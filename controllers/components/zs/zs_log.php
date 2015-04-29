<?php
/**
 * Created by JetBrains PhpStorm.
 * User: masd
 * Date: 14-4-30
 * Time: 上午10:31
 * To change this template use File | Settings | File Templates.
 */
class ZsLogComponent extends Object
{
    /**
     * 通用文件接口
     *
     * @author
     * @param
     * @return
     **/
    protected  function writeFile($filedir, $filename, $content) {
        $fp = $this->getFile($filedir, $filename);
        $content = "\r\n------------------------------------\r\n".
            '['.date("Y-m-d H:i:s",time()).']'."\r\n".$content."\r\n";
        @fwrite($fp, $content);
        @fclose($fp);
    }

    public function write($filedir, $filename, $content) {
        $fp = $this->getFile($filedir, $filename);
        @fwrite($fp, $content);
        @fclose($fp);
    }

    /**
     * 积分变更请求日志
     *
     * @author
     * @param $rootpath 根目录
     * @param $msg 错误日志
     * @return
     **/
    public function writeAccessLog($rootpath,$msg) {
        $path = $this->gFilePath($rootpath);
        $fileName = date('H').'.txt';
        $this->writeFile($path, $fileName, $msg);
    }

    /**
     * 业务错误日志
     *
     * @author
     * @param $className 类名
     * @param $msg 错误日志
     * @return
     **/
    public function writeLogicErrorLog($className, $msg) {
        $path = $this->gFilePath('log_logic_error__');
        $fileName = $className.'-'.date('H').'.txt';
        $this->writeFile($path, $fileName, $msg);
    }

    /**
     * 其他通用错误日志
     *
     * @author
     * @param $msg 错误日志
     * @return
     **/
    public function writeErrorLog($msg) {
        Configure::load ( 'envDefine' );
        $path   = Configure::read ( 'log.path');

        $path = $path.'/'.'log_common_error/';
        $fileName = date("Y-m-d",time()).'.txt';
        $this->writeFile($path, $fileName, $msg);
    }

    private function gFilePath($root) {
        $firstDir = date('Y');
        $secDir = date('m');
        $thirdDir = date('d');

        Configure::load ( 'envDefine' );
        $path   = Configure::read ( 'log.path');

        $path =  $path.'/' .$root.'/'.$firstDir .'/' .$secDir .'/' .$thirdDir .'/';
        return $path;
    }



    private function getFile($path, $file) {
        $this->createDir($path);
        $fp= @fopen($path.$file, "a");
        return $fp;
    }


    private function createDir($path) {
        if  (!file_exists($path)){
            $this->createDir(dirname($path));
            @mkdir ($path, 0777);
            @chmod($path,0777);
        }
    }

    public function getLog() {
        static $log = null;
        if($log!=null) return $log;
        $log = new FileLog();
        return $log;
    }

}
