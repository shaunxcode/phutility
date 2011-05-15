<?php
namespace Phutility;

class Obj { 
    private $slots; 
    private $parents = array();

	public static function create() {
	    return new Obj(func_get_args());
	}
	
    public function __construct($slots, $initSlots = array()) {
		$this->slots = $initSlots;
		while(!empty($slots)) {
	        $key = array_shift($slots);
	        $val = array_shift($slots);
	        $this->slots[$key] = $val;
	    }
    }

	public function __invoke() {
		$new = new Obj(func_get_args(), $this->slots);
		call_user_func_array(array($new, 'addParents'), $this->parents);
		return $new;
	}
	
    public function addParent($parent) {
        $this->parents[] = $parent;
		return $this;
    }

	public function addParents() {
		foreach(func_get_args() as $parent) {
			$this->parents[] = $parent;
		}
		return $this;
	}

    public function &getSlot($slot) {
        if(isset($this->slots[$slot])) {
            $ret = &$this->slots[$slot];
			return $ret;
        }

        foreach($this->parents as $parent) {
            if(!is_null($result = $parent->$slot)) {
                $ret = &$result;
				return $ret;
            }
        }

        return null;
    }

    public function setSlot($key, $val) {
        $this->slots[$key] = $val;
        return $this;
    }

    public function &__get($slot) {
        $ret = &$this->getSlot($slot);
		return $ret;
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