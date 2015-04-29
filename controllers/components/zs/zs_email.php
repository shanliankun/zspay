<?php
/**
 * function  发送站内信
 * User: bfs
 * Date: 14-4-30
 * Time: 上午10:31
 * To change this template use File | Settings | File Templates.
 */
class ZsEmailComponent extends Object
{
    const  EMAIL_URL = "http://usc.zhongsou.com/?r=UserInterface/SendCommMsg";



    /**
     * sendEmail
     *
     * 发送站内信的方法 。
     *
     * @param array $infoArr     array(
     *                                  title,  发送标题
     *                                  content,   发送内容
     *                                  sender_name,   发送人的名字
     *                                  user_name    被发送站内信的人
     *                                 )
     *
     *@return json       {
     *                       "head": {
     *                       "status": 200 //成功为200，错误为非200
     *                       "msg":    错误信息
     *                       },
     *                       "body": {}   " body为空"
     *                     }
     */
    public    function sendEmail($infoArr) {

        $infoArr['sign'] = md5($infoArr['user_name']."uscfsznx");

        $infoArr['title'] = urlencode($infoArr['title']);
        $infoArr['content'] = urlencode($infoArr['content']);
        $infoArr['sender_name'] = urlencode($infoArr['sender_name']);
        $infoArr['user_name']  = $infoArr['user_name'];

        $res =$this->_request_by_curl(self::EMAIL_URL,$infoArr);
        return $res;
    }

    /**
     * Curl版本
     * 使用方法：
     * $post_string = "app=request&version=beta";
     * request_by_curl('http://www.qianyunlai.com/restServer.php', $post_string);
     */
   private  function _request_by_curl($remote_server, $post_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
