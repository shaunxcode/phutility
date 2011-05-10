<?php

class String {
    private $value; 

    public function __construct($value) {
        $this->value = (string)$value;
    }

	public function __get($method) {
		if(is_callable(array($this, $method))) {
			return $this->$method();
		} else {
			throw new Exception("No $method on String");
		}
	}
	
    public function toUpper() {
        return new String(strtoupper($this->value));
    }

    public function reverse() {
        return new String(strrev($this->value));
    }

    public function __toString() {
        return "{$this->value}";
    }
}

define('_', '_'); 

class _ {

    const BREAKER = '!~~_._BREAKER_._~~!';

    const Version = '0.1';

    private $value; 

    public function __construct($value) {
        $this->value = $value;
    }

    public static function __callStatic($method, $args) {
        return call_user_func_array(array(new _(array_shift($args)), $method), $args); 
    }

    public function __call($method, $args) {
        $realMethod = _ . $method;
        if(is_callable(array($this, $realMethod))) {
            return call_user_func_array(array($this, $realMethod), $args);
        } else {
            throw new Exception("No method $method on _");
        }
    }

    public function _map($iterator) {
        $result = array();
        foreach($this->value as $key => $val) {
            if(is_string($val)) { 
                $val = new String($val);
            }
            $result[$key] = $iterator($val); 
        }
        return _($result);
    }

    public function _each($iterator) {
        $this->_map($iterator); 
    }

	public function _reduce($iterator, $initval = 0) {
		return array_reduce($this->value, $iterator, $initval);
	}
	
	public function _reduceRight($iterator, $initval = 0) {
		return arrray_reduce(array_reverse($this->value), $iterator, $initval);
	}
	
	public function getIsTruthyIterator() {
		if(!_::$truthy) _::$truthy = function($val) { return (boolean)$val ? _::BREAKER : false; };
		return _::$truthy;
	}

	public function getIterator($iterator) {
		return is_callable($iterator) ?
		 	$iterator : 
			(is_scalar($iterator) || is_bool($iterator) ?
				function($x) use($iterator) { return $x == $iterator; } : 
				$this->getIsTruthyIterator());
	}
			
	public function _any($iterator = null) { 
		$callFunc = $this->getIterator($iterator);
				
		$result = false;
		foreach($this->value as $val) { 
			if($callFunc($val)) {
				$result = $val;
				break;
			}
		}

		return $result;
	}

	public function _every($iterator) {
		$callFunc = $this->getIterator($iterator);
				
		$result = true;
		foreach($this->value as $val) { 
			if(!$callFunc($val)) {
				$result = false;
				break;
			}
		}
		
		return $result;
	}
		
	public function _find($iterator) {
		return $this->any($iterator);
	}
	
	public function _filter($iterator) {
		return _(array_filter($this->value, $iterator));
	}
	
	public function _reject($iterator) {
		$result = array();
		foreach($this->value as $key => $val) { 
			if(!$iterator($val)) {
				$result[$key] = $val;
			}
		}
		return _($result);
	}
	
	public function _pluck($key) {
		return $this->map(function($i) use($key) { 
			return $i[$key];
		});
	}
	
	public function _invoke() {
		$args = func_get_args();
		$method = array_shift($args);
		return $this->map(function($i) use($method, $args) { return call_user_func_array(array($i, $method), $args); });
	}
	
    public function _print() {
        print_r($this->value);
    }
}

function _() {
    $args = func_get_args();
    if(count($args) > 1) {
        return new _($args);
    } else {
        return new _(array_shift($args));
    }
}

//I is for invokable, iterator, or just "I" as in "self" or "this"
class I { 
    private $stack;

    public function __construct() {
        $this->stack = array();
    }

    public function addToStack($method, $args) {
        $this->stack[$method] = $args;
        return $this;
    }

	public function __get($property) {
		return $this->addToStack($property, null);
	}
	
    public function __call($method, $args) {
        return $this->addToStack($method, $args); 
    }

    public static function __callStatic($method, $args) {
        $new = new I;
        $new->addToStack($method, $args); 
        return $new;
    }

    public function __invoke($result) { 
        foreach($this->stack as $method => $args) {
            $result = is_null($args) ? $result->$method : call_user_func_array(array($result, $method), $args);
        }
        return $result;
    }
}