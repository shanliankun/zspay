<?php
App::import('Core', 'HttpSocket' ) ;
/**
 * 工具类
 * User: masd
 * Date: 14-4-14
 * Time: 下午5:57
 * To change this template use File | Settings | File Templates.
 */
class ZsUtilComponent  extends Object {
	
	public $components = array ('Zs.ZsLogger');
	
    public static function formatSuccRes($data='') {
        $res = array(
            'head'=>array('stat'=>200, 'msg'=>'success'),
            'body'=>$data
        );
        echo json_encode($res);
    }

    public static function formatFailRes($error=600, $msg='') {
        $res = array(
            'head'=>array('stat'=>500, 'msg'=>'error'),
            'body'=>array('error'=>$error, 'msg'=>$msg)
        );
        echo json_encode($res);
    }

    public static function formatSuccJson($data='') {
        return array('data'=>$data, 'status'=>1);
    }

    public static function formatFailJson($code=-1, $desc='') {
        return array('data'=>array('code'=>$code, 'desc'=>$desc), 'status'=>0);
    }

    public static function isEmpty($value, $trim=false) {
        return $value===null || $value===array() || $value==='' || $trim && is_scalar($value) && trim($value)==='';
    }

    public static function isInteger($value)
    {
        $integerPattern='/^\s*[+-]?\d+\s*$/';
        if(!preg_match($integerPattern, $value)) return false;
        return true;
    }
	
	/**
	 * 搜悦用加密方法
	 * @param string $source_data 源数据
	 * @return string 加密后数据
	 */
	public function encryption($source_data) {
		$encode_data = '';
		$ObjHttp = new HttpSocket();
		$json_data = '{"data":"'.$source_data.'"}';
		$post_params = array('result' => $json_data);
		$result_data = $ObjHttp->post(code::DECRYPTSERVER_ENCRYPTION, $post_params);
		$array_data = json_decode($result_data,TRUE);
		if($array_data['head']['status'] == 200){
			$encode_data = $array_data['body'];
		}
		return $encode_data;
	}
	
	/**
	 * 搜悦用解密方法
	 * @param string $source_data 加密后数据
	 * @return array 源数据
	 */
	public function decrypt($encode_data) {
		$source_data = '';		
		$ObjHttp = new HttpSocket();
		$encode_data = urldecode($encode_data);
		$encode_data = str_replace(' ', '+', $encode_data);
		$json_data = '{"data":"'.$encode_data.'"}';
		$post_params = array('result' => $json_data);
		$this->ZsLogger->start();
		$result_data = $ObjHttp->post(code::DECRYPTSERVER_DECRYP, $post_params);
		$array_data = json_decode($result_data,TRUE);
		if($array_data['head']['status'] == 200){
			$source_data = $array_data['body'];
		}else{
			$this->ZsLogger->addLogger('tool/decrypt', code::DECRYPTSERVER_DECRYP, $post_params, $result_data, $this->ZsLogger->ERROR_LEVEL);
		}
		return $source_data;
	}
	
	/**
	 * 处理搜悦解密数据
	 */
	public function editDecryptDate() {
		$params_array = array();
		$sy_code = isset($_REQUEST['sy_c']) ? trim($_REQUEST['sy_c']) : '';
		if($sy_code){
			$sy_decode = $this->decrypt($sy_code);
			parse_str($sy_decode, $old_params_array);
			foreach ($old_params_array as $key => $value) {
				$params_array[$key] = urldecode($value);
			}
		}
		return 	$params_array;
	}
	
	/**
	 * 获得接入方的ip
	 */
	public function getIp() {
		$defalut_ip = '127.0.0.1';
		$array = array('HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'HTTP_CLIENT_IP');
		foreach ($array as $method) {
			$true_ip = getenv($method);
			if ($true_ip) {
				$defalut_ip = $true_ip;
				break;
			}
		}
		return $defalut_ip;
	}
	
	/**
	 * 获得ip所在地域
	 */
	public function getIpRegin($ipcode = NULL) {
		$regin = array(
			'province' => '',
			'city' => '',
		);
		if (!$ipcode) {
			$ipcode = $this->getIp();
		}
		if (substr($ipcode, 0, 7) != '192.168') {
			$ObjHttp = new HttpSocket();
			$get_params = array('format' => 'js', 'ip' => $ipcode);
			$result_data = $ObjHttp->get('http://int.dpool.sina.com.cn/iplookup/iplookup.php', $get_params);
			if($result_data){
				$result_array = explode(' = ', $result_data);
				$new_array = json_decode(substr($result_array[1], 0, -1), true);
				$regin['province'] = $new_array['province'];
				$regin['city'] = $new_array['city'];
			}
		}
		return $regin;
	}
	
	public function returnSafeCenter($token){
		$safe_center = 'https://security.zhongsou.com/SecurityCenter/index?arg=4&Security=';
		$security_orign = 'token='.$token.'&anonymous=1&encry_support=1';
		$security = $this->encryption($security_orign);
		$safe_center = $safe_center.$security;
		return $safe_center;
	}
	
}
