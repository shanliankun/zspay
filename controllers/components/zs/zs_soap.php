<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fonder
 * Date: 14-4-21
 * Time: 下午4:15
 * To change this template use File | Settings | File Templates.
 */
class ZsSoapComponent extends Object{

    /*
     * 请求webservice
     */
    public function webService($wsUrl,$param)
    {
        $soap = new SoapClient ($wsUrl) ;
        $arr = array ('content' =>$param);
        $res = (array)$soap->addLogs ( $arr );
        return $res;
    }
}
