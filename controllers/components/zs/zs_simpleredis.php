<?php
/**
 * Created by JetBrains PhpStorm.
 * User: masd
 * Date: 14-6-3
 * Time: 上午10:04
 * To change this template use File | Settings | File Templates.
 */
class ZsSimpleredisComponent    extends Object {

    public function getSimpleRedis($host,$port) {
        if(empty($host) || empty($port))  return null;
        try {
            $simpleredis = new Redis();
            $simpleredis->connect($host, $port);
            $simpleredis->ping();

        } catch(Exception $e) {
            $simpleredis = null;
        }
        return $simpleredis;
    }

}
