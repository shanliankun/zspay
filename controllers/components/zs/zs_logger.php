<?php
/**
 * Logger
 * 
 * @author shanliankun
 * 统一日志记录器
 * 日志字段统一使用一个tab符分割
 */
class ZsLoggerComponent extends Object {

	private static $_log_file = "[file_name].[split].log"; #文件位置
	private static $_log_split = "YmdH"; #文件分隔符
	private static $_start_time = 0; #计时开始
	private static $_format_string = "[now]\t[time]\t[level]\t[uri]\tinput:[input]\toutput:[output]\n"; #日志格式
	//日志等级
	public $INFO_LEVEL = 1; //正常日志输出
	public $ERROR_LEVEL = 2; //请求接口失败

	/**
	 * 开始时间 可手动设置
	 * @param mixed $start_time 开始时间
	 * @return void
	 */

	public function start($start_time = NULL) {

		self::$_start_time = $start_time === NULL ? microtime(true) : $start_time;
	}

	/**
	 * 适合对外提供接口写日志 
	 * @param mixed $file_name 日志名称，如file_name='umc', 则输出到umc.2013080915.log文件里面 
	 * @param mixed $input 输入参数 
	 * @param mixed $output 输出参数
	 * @param mixed $level 日志等级
	 * @return void
	 */
	public function addLoggerForApi($file_name, $input, $output, $level = 1) {

		if (!is_string($file_name)) {
			return false;
		}
		$now = self::_getNow();
		$time = self::_getTime();
		$uri = self::_getUri();
		$ip = self::_getIp();
		$level = self::_getLevel($level);
		$input = self::_getInput($input);
		$output = self::_getOutput($output);
		$file_string = "$now\t$time\t$level\t$ip\t$uri\tinput:$input\toutput\t:$output\n";
		self::_writeFile($file_name, $file_string);
	}
	
	/**
	 * 抓取页面访问ip 
	 * @param mixed $file_name 日志名称，如file_name='umc', 则输出到umc.2013080915.log文件里面 
	 * @param mixed $uri 地址
	 * @param mixed $input 输入参数 
	 * @param mixed $level 日志等级
	 * @return void
	 */
	public function addLoggerForWeb($file_name, $uri, $input, $level = 1) {

		if (!is_string($file_name)) {
			return false;
		}
		$now = self::_getNow();
		$time = self::_getTime();
		$ip = self::_getIp();
		$level = self::_getLevel($level);
		$input = self::_getInput($input);
		$file_string = "$now\t$time\t$level\t$ip\t$uri\tinput:$input\n";
		self::_writeFile($file_name, $file_string);
	}

	/**
	 * 适合请求数据库或别人接口写日志 
	 * @param mixed $file_name 日志名称，如file_name='umc', 则输出到umc.2013080915.log文件里面 
	 * @param mixed $content 如果是请求接口输入接口url，如果请求db则输入mysql/sybase 
	 * @param mixed $input 输入参数 
	 * @param mixed $output 输出参数
	 * @param mixed $level 日志等级
	 * @return void
	 */
	public function addLogger($file_name, $content, $input, $output, $level = 1) {

		if (!is_string($file_name)) {
			return false;
		}
		$now = self::_getNow();
		$time = self::_getTime();
		$url = $content;
		$level = self::_getLevel($level);
		$input = self::_getInput($input);
		$output = self::_getOutput($output);
		$file_string = "$now\t$time\t$level\t$url\tinput:$input\toutput\t:$output\n";
		self::_writeFile($file_name, $file_string);
	}

	/**
	 * 写日志到文件
	 * @param mixed $file_name 日志文件名
	 * @param mixed $file_string 日志内容
	 * @return void
	 */
	private static function _writeFile($file_name, $file_string) {
		self::_clearTime();
		$base_path = Configure::read ( 'log.path' );
		$file_name = str_replace('[file_name]', $file_name, self::$_log_file);
		$file_name = str_replace('[split]', date(self::$_log_split), $file_name);
		$file_name = $base_path . '/'.$file_name;
		self::createDir(dirname($file_name));
		file_put_contents($file_name, $file_string, FILE_APPEND);
	}

	//-----以下是用于合成日志所需要的函数-----//
	/**
	 * 格式化输出数据位字符串
	 * @param mixed $message 写入文本数据日志
	 * @return string  
	 */
	private static function _formatString($message) {
		if (!$message) {
			return '';
		}
		if (is_string($message) || is_numeric($message)) {
			return $message;
		} elseif (is_array($message)) {
			return json_encode($message);
		} else {
			return serialize($message);
		}
	}

	/**
	 * 获取输入字符串
	 * @param mixed $input 输入数据
	 * @return string
	 */
	private static function _getInput($input) {

		return self::_formatString($input);
	}

	/**
	 * 获取出数据
	 * @param mixed $output 输出/返回数据
	 * @return string
	 */
	private static function _getOutput($output) {

		return self::_formatString($output);
	}

	/**
	 * 获取当前时间
	 * @return string
	 */
	private static function _getNow() {

		return date("H:i:s");
	}

	/**
	 * 获取日志级别
	 * @param int $level 日志级别
	 * @return void
	 */
	private static function _getLevel($level) {

		$array = array(
			0 => 'debug',
			1 => 'info',
			2 => 'error',
		);
		return $array[$level];
	}

	/**
	 * 计算运行时间差(运行结束时间-运行开始标记时间),$digit:保留小数点后的位数
	 * @param int $digit 点位数 
	 * @return 开始时间和结束时间的时差
	 */
	private static function _getTime($digit = 4) {

		if(self::$_start_time <=0){
            return 0;    
        }
		return number_format(microtime(true) - self::$_start_time, $digit, '.', '');
	}
	
	/**
	 * 迭代创建目录
	 * 
	 */
	private static function createDir($path) {
        if  (!file_exists($path)){
            self::createDir(dirname($path));
            @mkdir ($path, 0777);
            @chmod($path,0777);
        }
    }
	
	/**
     * 清空开始时间
     * @return void
     */
    private static function _clearTime(){
        
        self::$_start_time = 0;
    }
    /**
     * 获取请求方的ip
     * @param string $defalut_ip 默认ip
     * @return void 获取到的ip
     */
	private static function _getIp($defalut_ip = '127.0.0.1')
	{
		$array = array('HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'HTTP_CLIENT_IP');
		foreach($array as $method){
            $true_ip = getenv($method);
			if($true_ip){
				$defalut_ip = $true_ip;
				break;
			}
		}
		return $defalut_ip;
	}

    /**
     * 获取请求接口地址
     * @return void
     */
	private static function _getUri(){
	
		$uri = $_SERVER["REQUEST_URI"];
		return $uri;
	}

}
