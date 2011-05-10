<?php
namespace Phunderscore;

class Tryable {
	private $obj;
	
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