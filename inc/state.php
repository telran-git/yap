<?php
define('ST_KEY', '"state"');
if(!defined('EXPT')) define('EXPT', 7200);

class State {
//    const ST_KEY='state';
// Memcached
    private $m;
// state
    private $st = array();
    
    public function __construct($data=null) {
        $this->m = new Memcached();

	$this->m->addServer('127.0.0.1', 11211);// or die(«Could not connect»);

	if (isset($data))
	    $this->start($data);
	else {
	    $this->st = $this->m->get(ST_KEY);
            if (method_exists($this->m,'getResultCode')) {
                if ($this->m->getResultCode() !== Memcached::RES_SUCCESS) // item does not exist ($item is probably false)
                    $this->stop();
            } else {
               if (!$this->st) $this->stop();
            }
	}
    }

    public function __destruct()
    {
//	$this->m->delete(ST_KEY);
	unset($this->st);
	unset($this->m);
    }

    private function settime() {
	if (isset($this->st['statrtime']))
	    $this->st['elapsedtime'] = round(microtime(true) - $this->st['statrtime'], 1);
    }

    public function get($key = null) {
	$this->st = $this->m->get(ST_KEY);
	if (isset($key)) {
	    if (array_key_exists($key, $this->st))
		return $this->st[$key];
	    else
		return null;
	} else
	    return $this->st;
    }

    public function set($key,$val) {
	$this->st = $this->m->get(ST_KEY);
	if (!isset($this->st['preparing']))
	    return false;
	elseif (!array_key_exists($key, $this->st))
	    return false;
	else {
	    $this->st[$key] = $val;
	    $this->settime();
//error_log('* state_set['.print_r($this->st,true).']');

	    return $this->m->set(ST_KEY, $this->st);
	}
    }

    public function set_action($val) {
	return $this->set("action", $val);
    }

    public function set_aprocessed($val) {
	return $this->set("aprocessed", $val);
    }

    public function set_bprocessed($val) {
	return $this->set("bprocessed", $val);
    }

    public function set_state($val) {
	$this->st = $this->m->get(ST_KEY);

	$this->st = array_replace($this->st,$val);

	$this->settime();

	return $this->m->set(ST_KEY, $this->st, EXPT);
    }

    public function inc($key) {
	$this->st = $this->m->get(ST_KEY);
	if (!$this->st)
	    return false;
	elseif (!array_key_exists($key, $this->st))
	    return false;
	else {
	    $this->st[$key]++;
	    $this->settime();
	    return $this->m->set(ST_KEY, $this->st, EXPT);
	}

    }

    public function set_time() {
	$this->st = $this->m->get(ST_KEY);
	$this->settime();
	return $this->m->set(ST_KEY, $this->st, EXPT);
    }

    public function error($errstr) {
	$this->st = $this->m->get(ST_KEY);
	$this->st['preparing'] = false;
	$this->st['error'] = $errstr;
	return $this->m->set(ST_KEY, $this->st, EXPT);
    }


    public function start($val) {
	$this->st = array(
	    "preparing"	=> true,
	    "action"	=> $val,
	    "statrtime"	=> microtime(true),
	    "elapsedtime"   => null,
	    "aprocessed"    => 0,
	    "bprocessed"    => 0,
	);
//error_log('* state_start['.print_r($this->st,true).']');
	return $this->m->set(ST_KEY, $this->st, EXPT);
    }

    public function stop() {
	$this->st = array(
	    "preparing"	=> false,
	    "action"	=> "",
	    "statrtime"	=> null,
	    "elapsedtime"   => null,
	    "aprocessed"    => null,
	    "bprocessed"    => null,
	);
//error_log('* state_stop ['.print_r($this->st,true).']');

	return $this->m->set(ST_KEY, $this->st, EXPT);
    }

}

?>