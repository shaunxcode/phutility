<?php
namespace Phutility;

class Obj { 
    private $slots; 
    private $parents = array();

	public static function create() {
	    $slots = array();
	    $args = func_get_args();

	    while(!empty($args)) {
	        $key = array_shift($args);
	        $val = array_shift($args);
	        if(!defined($key)) { 
	            define($key, $key);
	        }
	        $slots[$key] = $val;
	    }

	    return new Obj($slots);
	}
	
    public function __construct($slots) {
        $this->slots = $slots;
    }

    public function addParent($parent) {
        $this->parents[] = $parent;
		return $this;
    }

    public function getSlot($slot) {
        if(isset($this->slots[$slot])) {
            return $this->slots[$slot];
        }

        foreach($this->parents as $parent) {
            if(!is_null($result = $parent->$slot)) {
                return $result;
            }
        }

        return null;
    }

    public function setSlot($key, $val) {
        $this->slots[$key] = $val;
        return $this;
    }

    public function __get($slot) {
        return $this->getSlot($slot);
    }

    public function __set($slot, $val) {
        $this->slots[$slot] = $val;
    }

    public function __call($slot, $args) {
        if($method = $this->getSlot($slot)) { 
            if(is_callable($method)) {
                array_unshift($args, $this);
                return call_user_func_array($method, $args);
            }
            if(empty($args)) {
                return $method;
            }
        }
        throw new \Exception("Not a method at $slot");
    }
}