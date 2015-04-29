<?php
App::import ( 'Controller', 'Asbasic' );
App::import('Core', 'HttpSocket' ) ;

/**
 * 中搜币支付api
 *
 */
class ApiController extends AsbasicController {
	
	public $components = array ('Zs.ZsMail','Zs.ZsRedis');
	
	//public $layout='api';
	
	/**
	 * model
	 *
	 * @var array
	 */
	public $uses = array() ;
	
	const ZSPAY_NOTIFY_ERROR_PRE = 'ZSPAY_NOTIFY_ERROR_MESSAGE_';     //支付通知失败的日志队列

	
	/**
	 * 测试接口
	 *
	 */
	public function index()
	{ 
		    echo 'success';exit;
	}
	
	
	/*
	 * 生成支付token并注入
	 */
	public function sendmail(){

		$this->ZsMail->sendMessageInfo('shanliankun@zhongsou.com', 'developer', '中搜币支付异步通知异常数据','');
		exit;
	}
	
	/**
	 * 
	 */
	public function pushlog() {
		$message = '{"url":"http:\/\/m.zhongsou.com\/app\/webroot\/agentPage.php?server_url=http:\/\/m.zhongsou.com\/circlerewards\/dashangcallback&userAgent=souyue4.0&returnformat=1","signkey":"score#^&^&^**","params":{"mode":2,"ptoken":"382d62e147a0d76d34e8f856a70ad6e7","appid":"10010","business_type":"1","orderid":"DS2015021312031332668900966910","decorderid":54185664,"addorderid":54185667,"zsb_orderid":0,"result":200,"message":"success","zsb_cost":10,"order_flow":"2015021312032178410800"}}';
		
		$this->ZsRedis->rpush(self::ZSPAY_NOTIFY_ERROR_PRE.'10014',$message);
		exit;
	}
	
	
	
	
	
	
	
}

