<?php

    class Memcached {
       const OPT_LIBKETAMA_COMPATIBLE = true;
       const OPT_COMPRESSION = false;// true;
       const OPT_NO_BLOCK = true;
       //if you code relies on any other constants define them to avoid
       //undefined constant notice

       //http://www.php.net/manual/en/memcached.constants.php

       public $_instance;
       public function __construct() {
            $this->_instance = new Memcache;
       }

       public function __call($name, $args) {
            return call_user_func_array(array($this->_instance, $name), $args);
       }

       public function setOption() {}
       
       public function set($key , $value , $expiration = null ) {
           return $this->_instance->set($key,$value,false,$expiration);
       }
    }
    
?>