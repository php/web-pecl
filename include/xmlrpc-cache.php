<?php
    require_once "Cache.php";

    class XMLRPC_Cache
    {
        var $cache;
        
        function &singleton()
        {
            static $instance;
            
            if (!isset($instance)) {
                $instance = new XMLRPC_Cache();
            };
            
            return $instance;
        }
        
        function XMLRPC_Cache()
        {
            $this->cache = new Cache('file', 
                array(
                    'cache_dir'       => PEAR_TMPDIR.'/cache/',
                    'filename_prefix' => 'cache_xmlrpc_',
                ));
        }
        
        function get($method, $args, $maxAge = null)
        {
            if (!isset($this) || get_class($this) != 'xmlrpc_cache') {
                $this =& XMLRPC_Cache::singleton();
            };
            
            $id = $this->cache->generateID(array($method, $args));
            
            if ($maxAge != null) {
                $time = filemtime($this->cache->container->getFilename($id, 'default'));
                if ($maxAge > $time) {
                    return "";
                };
            };
            
            return $this->cache->get($id);
        }
        
        function save($method, $args, $value)
        {
            if (!isset($this) || get_class($this) != 'xmlrpc_cache') {
                $this =& XMLRPC_Cache::singleton();
            };
            
            $id = $this->cache->generateID(array($method, $args));

            return $this->cache->save($id, $value);
        }
        
        function remove($method, $args)
        {
            if (!isset($this) || get_class($this) != 'xmlrpc_cache') {
                $this =& XMLRPC_Cache::singleton();
            };
            
            $id = $this->cache->generateID(array($method, $args));

            return $this->cache->remove($id);
        }
    };