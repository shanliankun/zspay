<?php
/**
 * 公共组件，Redis
 * 主从连接判断，主从操作自动区分
 */


/**
 * Redis操作方法组件，仅限Redis操作
 *
 */
class ZsMailComponent extends Object {
	
	public function __construct(){

        /*实例化对象*/
		require_once dirname( WWW_ROOT ).'/libs/class.phpmailer.php';
        $this->phpMailer = new PHPMailer(true);
        $this->phpMailer->IsHTML(true);
        $this->phpMailer->IsSMTP();
        $this->phpMailer->SMTPAuth = true;
        $this->phpMailer->CharSet  = "utf-8";
        $this->phpMailer->Host = 'smtp.zhongsou.com';
        $this->phpMailer->Username = 'developer@zhongsou.com';
        $this->phpMailer->Password = 'zhs123';
    }
	
	/**
     * 发送账号信息
	 * @param string $mailAddress 收件人邮箱
	 * @param string $accountName 收件人的姓名
	 * @param string  Description
	 * 
     */
    public function sendMessageInfo($mailAddress, $accountName, $subject, $message){

        try {

            $this->phpMailer->From = 'zspaymail@zhongsou.com';
            $this->phpMailer->FromName = 'Zspay - Admin';
            $this->phpMailer->AddAddress($mailAddress, $accountName);
            $this->phpMailer->Subject = $subject;
            $this->phpMailer->Body = '
					<div style="border:1px solid #CCC;background:#F4F4F4;width:100%;text-align:left">
                        <div style="height:31px;padding:14px;">Runing - Rabbit （中搜币支付异步通知系统）</div>
                        <div style="border:none;background:#FCFCFC;padding:20px;padding-top:1px;color:#333;font-size:14px;">
                            '.$message.'
                      
                            <p style="height:20px; border-top:1px solid #CCC"></p><p>系统发信，请勿回复</p>
                        </div>
                    </div>';

            $this->phpMailer->Send();
            return true;
        } catch (Exception $e) {

            return false;
        }
    }
	
}

