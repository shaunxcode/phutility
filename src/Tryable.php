<?php
namespace Phunderscore;

class Tryable {
	private $obj;
	
	public static function attemptTryable($obj, $method, $args) {
		if(strpos($method, 'try_') === 0) {
			array_unshift($args, substr($method, 4));
			$method = 'try';
		}
		
		if($method == 'try') {
			$tryMethod = array_shift($args);
			if(method_exists($obj, $tryMethod)) {
				return call_user_func_array(array($obj, $tryMethod), $args);
			} else {
				return new Tryable($obj);
			}
		}
		
		return false;
	}
	
	public function __construct($obj = null) {
		$this->obj = $obj;
	}
	
	public function __call($method, $args) {
		if($method == 'else') {
			if(is_callable(current($args))) {
				return call_user_func_array(current($args), array($this->obj));
			}
			return current($args);
		}
		
		if($method == 'elseThrow') {
			$exception = current($args);
			if(!($exception instanceof \Exception)) {
				$exception = new \Exception($exception);
			}
			throw $exception;
		}
		
		return $this;
	}
	
	public function __get($what) {
		return $this;
	}
	
	public function __toString() {
		return '';
	}
}