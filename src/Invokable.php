<?php
namespace Phunderscore;

class Invokable { 
    private $stack;

    public function __construct() {
        $this->stack = array();
    }

	public function getStack() {
		return $this->stack;
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