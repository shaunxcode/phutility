<?php
namespace Phutility;

class Invokable { 
    private $stack;

    public function __construct() {
        $this->stack = array();
    }

	public function getStack() {
		return $this->stack;
	}
	
	public function getFirstMethodInStack() {
		reset($this->stack);
		return key($this->stack);
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
        $new = new Invokable;
		if(strpos($method, 'try_') === 0) {
			array_unshift($args, substr($method, 4));
			$method = 'try';
		}
		
        $new->addToStack($method, $args); 
        return $new;
    }

    public function __invoke() { 
    	$iargs = func_get_args();
    	if(is_object(current($iargs))) {
            $result = current($iargs);
    	}
    	else if(is_object(end($iargs))) {
            $result = end($iargs);
    	}
    	else {
            throw new \Exception('Expects that either arg1 or arg2 is the object to be invoked upon.');
    	}
        foreach($this->stack as $method => $args) {
            $result = is_null($args) ? $result->$method : call_user_func_array(array($result, $method), $args);
        }
        return $result;
    }
}
