<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Christian Dickmann <dickmann@php.net>                       |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "Cache.php";

error_reporting(0);

class XMLRPC_Cache
{
    var $cache;
        
    function &singleton()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new XMLRPC_Cache();
        }

        return $instance;
    }

    function XMLRPC_Cache()
    {
        $this->cache = new Cache('file', 
                                 array(
                                       'cache_dir'       => PEAR_TMPDIR.'/cache/',
                                       'filename_prefix' => 'cache_xmlrpc_',
                                       )
                                 );
    }
        
    function get($method, $args, $maxAge = null)
    {
        $id = $this->cache->generateID(array($method, $args));

        if ($maxAge != null) {
            $filename = $this->cache->container->getFilename($id, 'default');
            if (!file_exists($filename)) {
                return null;
            }
            $time = filemtime($filename);
            if ($maxAge > $time) {
                return "";
            }
        }

        return $this->cache->get($id);
    }

    function save($method, $args, $value)
    {
        $id = $this->cache->generateID(array($method, $args));

        return $this->cache->save($id, $value);
    }
        
    function remove($method, $args)
    {
        $id = $this->cache->generateID(array($method, $args));

        return $this->cache->remove($id);
    }
}
?>
