<?php
/**
 * Created by JetBrains PhpStorm.
 * User: masd
 * Date: 14-4-25
 * Time: 下午2:07
 * To change this template use File | Settings | File Templates.
 */
class ZsMemcacheComponent extends Object
{
    public static  function getMemcache() {
        Configure::load ( 'envDefine' );
        $ip   = Configure::read ( 'memcache.ip' );
        $port = Configure::read ( 'memcache.port' );

        try {
            $memcache =  new Memcache;
            $memcache->addServer($ip, $port);
        } catch(Exception $e) {
            $memcache = null;
        }

        return $memcache;
    }
}
