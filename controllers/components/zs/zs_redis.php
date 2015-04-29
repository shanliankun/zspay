<?php
/**
 * 公共组件，Redis
 * 主从连接判断，主从操作自动区分
 */


/**
 * Redis操作方法组件，仅限Redis操作
 *
 */
class ZsRedisComponent extends Object {
	
	/**
	 * Redis读操作方法
	 *
	 */
	private $redisReadFuns = array('get','keys', 'llen','lrange','lindex');
	
	/**
	 * Redis连接对象
	 *
	 */
	private static $redisObj;

	/*
	 * 初始实例化Redis连接组件
	 * */
	function __construct() {
        if (self::$redisObj == null){
            self::$redisObj = RedisFactory::getInstance();
        }
    }
    
    
    /**
     * 自动判断Redis读取，动态调用原生Redis方法
     *
     * @param string $method
     * @param array $args
     * @return value
     */
    public function __call($method, $args)
    {
    	//判断读与操作，TODO
    	//读
    	if(in_array(strtolower($method), $this->redisReadFuns)) {
    		$redis =  self::$redisObj->getInstanceSlave();
    	} else {
    		//写
    		$redis =  self::$redisObj->getInstanceMaster();
    	}
		
    
    	//动态调Redis方法
    	if($redis && $method && method_exists($redis, $method) ) {
    		return call_user_func_array(array($redis, $method), $args);
    	} else {
    		throw new Exception("redis $method error");
    	}
    }

}




class RedisFactory {

    //常量
    const SERVER_AREA_TELECOM = 'telecom'; //电信
    const SERVER_AREA_NETCOM  = 'netcom';  //网通
    const MAX_RELINK_NUM      = 3;//重连次数
    const SIGN_REDIS_BAD_EXCEPTION = 'signRedisBad';

    const SIGN_DOWN = 1;
    const SIGN_NO_DOWN = 0;

    const REDIS_STATUS_DOWN = 0;
    const REDIS_STATUS_UP   = 1;
    const REDIS_STATUS_TIMEOUT = 2;

    const SIGN_RELINK_TIME = 50;//Down机之后重连时间

    const DEBUG = false;

    //状态标示符
    private static $sign = 0;

    //静态对象
    private static $redisFactory = null;
    private static $redisMaster  = null;
	private static $redisSlave   = null;
	

    /**
     * 私有构造方法
     */
    public function __construct() {}

    /**
     * 获取工场代理对象
     */
    public static function getInstance(){

        if (self::$redisFactory == null){

            self::$redisFactory = new RedisFactory();
        }

        return self::$redisFactory;
    }
	
    /**
     * 获得redis连接
     * @param unknown_type $conf
     * @return Redis
     */
    private function redisLink($conf) {

        $redis = new Redis ();
    	$redis->connect ($conf['ip'], $conf['port']);
    	$redis->ping();
    	return $redis;
    }
    
    /**
     * 获取主机对象
     */
    public function getInstanceMaster(){

        if (self::$redisMaster == null){

            //初始化配置信息
            Configure::load ( 'envDefine' );
            $masterConf = Configure::read ( 'redis.master' );

            self::$redisMaster = $this->_tryLinkRedis($masterConf);
        }
        return self::$redisMaster;
    }

    /**
     * 获取从机对象
     */
    public function getInstanceSlave($area = null){

        if (self::$redisSlave == null){

            //初始化配置信息
            Configure::load ( 'envDefine' );
            $masterConf   = Configure::read ( 'redis.master' );
            $slaveAllConf = Configure::read ( 'redis.slave' );

            //检查本机所属地区
            $area        = $this->_getServerArea(Configure::read ('redis.area'));

            //后备地区redis
            $reserveArea = self::SERVER_AREA_TELECOM;
            if ($area == self::SERVER_AREA_TELECOM){

                $reserveArea = self::SERVER_AREA_NETCOM;
            }

            if(self::DEBUG){

                $this->_writeLineLog('开始尝试从机链接：');
                $this->_writeLineLog('主从机信息：'.json_encode($slaveAllConf[$area]));
                $this->_writeLineLog('从从机信息：'.json_encode($slaveAllConf[$reserveArea]));
            }

            //尝试链接主从机
            self::$redisSlave = $this->_tryLinkRedis($slaveAllConf[$area]);

            //尝试链接从从机
            self::$redisSlave == null && (self::$redisSlave = $this->_tryLinkRedis($slaveAllConf[$reserveArea]));

            //尝试链接主机
            self::$redisSlave == null && (self::$redisSlave = $this->getInstanceMaster());
        }
        return self::$redisSlave;
    }

    private function _tryLinkRedis($serverConf){

        $redis   = null;
        $linkNum = 1;

        while($linkNum <= self::MAX_RELINK_NUM){

            try{

                $status = $this->_getServerStatusSign($serverConf);

                //判断主从机状态
                if(self::REDIS_STATUS_DOWN == $status){

                    $linkNum = self::MAX_RELINK_NUM;
                    throw new Exception(self::SIGN_REDIS_BAD_EXCEPTION);
                }
                try{

                    if(self::DEBUG){

                        $this->_writeLineLog('第'.$linkNum.'次尝试中...');
                    }

                    $redis = $this->redisLink($serverConf);
                    if (self::REDIS_STATUS_UP != $status){

                        $this->_setServerUpSign($serverConf);
                    }
                    break;

                }catch (Exception $e){

                    if (self::REDIS_STATUS_TIMEOUT == $status){

                        $this->_refreshServerDownSign($serverConf);
                        $linkNum = self::MAX_RELINK_NUM;
                        throw new Exception(self::SIGN_REDIS_BAD_EXCEPTION);
                    } else {

                        throw $e;
                    }
                }
            }catch(Exception $e){

                $linkNum++;			//记录主从机出现异常的次数

                if ($linkNum > self::MAX_RELINK_NUM){//当异常次数大于最大值，尝试链接备份从机器

                    //发送警告邮件 + 设置Down机标示位
                    if($e->getMessage() != self::SIGN_REDIS_BAD_EXCEPTION){

                        $this->_setServerDownSign($serverConf);
                        $this->_sendWarnMail($serverConf);
                    }
                }
            }
        }
        return $redis;
    }

    /**
     * 获取服务器地区
     */
    private function _getServerArea($areaConf){

        $serverIp = $_SERVER['SERVER_ADDR'];
        $area     = self::SERVER_AREA_NETCOM;

        if (strpos($areaConf[self::SERVER_AREA_TELECOM], $serverIp) !== false){

            $area = self::SERVER_AREA_TELECOM;
        }
        return $area;
    }

    /**
     * 发送警告邮件
     * @param $serverConf 服务器配置
     */
    private function _sendWarnMail($serverConf){

        //TODO 发送效率太低响应时间过长 太慢了。。。。

        //加载
        App::import('Email');
        $email = new EmailComponent();

        //组织预警格式
        $sendInfo = 'Redis已经Down机，请及时处理！<br />信息如下：<br />主机：'.$serverConf['ip'];

        //发送邮件
        $email->smtpOptions = array (
            'host' => 'smtp.zhongsou.com',
            'port' => 25, //邮箱服务器端口号，可以换成中搜自己的
            'username' => 'sendmail@zhongsou.com', //发信邮箱的用户名
            'password' => 'zhs123', //发信邮箱的密码
            'timeout' => 30
        );
        $email->delivery = 'smtp'; //使用smtp发送
        $email->sendAs   = 'html'; //邮件内容为html
        $email->from = 'sendmail@zhongsou.com'; //发送人邮箱，此处需要注意，必须是和邮件服务器设置一致
        $email->to   = 'xpwang@ec01.cn'; //收件人Email地址
        $email->subject  = '广告系统Redis预警'; //邮件标题
        $email->send ( $sendInfo ); //邮件内容

        $err = $email->smtpError; //邮件发送错误提示
        return $err;
    }

    /**
     * 获取服务器标示位
     * @param  $serverConf 服务器配置
     * @return boolean true 已经down机 false 没有down机
     */
    private function _getServerStatusSign($serverConf){

        $sign = $serverConf['key'];
        $shm_id = shmop_open($sign, "c", 0644, 100);
        if (!$shm_id) {

            shmop_close($shm_id);
            return false;
        }
        $shm_size = shmop_size($shm_id);
        $shm_info = shmop_read($shm_id, 0, $shm_size);
        if (!$shm_info) {

            shmop_close($shm_id);
            return false;
        }
        shmop_close($shm_id);

        $signInfo = $this->_resolutionSignInfoStr($shm_info);

        if (self::DEBUG){

            $this->_writeLineLog($serverConf['ip'].'服务器状态：'.json_encode($signInfo));
        }

        if ($signInfo['sign'] == self::SIGN_DOWN){

            //机制 超过X秒则对失败的主机进行重连
            $downDt = $signInfo['dt'];
            $nowDt  = time();
            if ($nowDt - $downDt > self::SIGN_RELINK_TIME){

                return self::REDIS_STATUS_TIMEOUT;
            } else {

                return self::REDIS_STATUS_DOWN;
            }
        } else {

            return self::REDIS_STATUS_UP;
        }
    }

    /**
     * 设置服务器Down机标示位
     * @param $serverConf 服务器配置
     */
    private function _refreshServerDownSign($serverConf){

        $this->_setServerDownSign($serverConf);
    }
    private function _setServerDownSign($serverConf){

        $this->_setServerSign($serverConf, self::SIGN_DOWN);
    }
    /**
     * 设置服务器恢复标示位
     * @param $serverConf 服务器配置
     */
    private function _setServerUpSign($serverConf){

        $this->_setServerSign($serverConf, self::SIGN_NO_DOWN);
    }
    /**
     * 设置服务器标示位
     * @param $serverConf
     * @param $sign
     */
    private function _setServerSign($serverConf, $sign){

        $key    = $serverConf['key'];
        $shm_id = shmop_open($key, "c", 0644, 100);
        if (!$shm_id) {

            shmop_close($shm_id);
            return false;
        }
        $infoStr = $this->_makeUpSignInfoStr($sign);
        $shm_bytes_written = shmop_write($shm_id, $infoStr, 0);
        if ($shm_bytes_written != strlen($infoStr)) {

            shmop_close($shm_id);
            return false;
        }
        shmop_close($shm_id);
        return true;
    }

    /**
     * 从内存信息串中获取sign
     * @param $infoStr
     * @return string
     */
    private function _resolutionSignInfoStr($infoStr){

        return array(
            'sign'=>substr($infoStr,0,1),
            'dt'=>substr($infoStr,1,10)
        );
    }

    private function _makeUpSignInfoStr($sign){

        $dt = time();
        return $sign.''.$dt;
    }

    private function _writeLineLog($logInfo){
        echo $logInfo.'<br /><br />';
    }

 
}


