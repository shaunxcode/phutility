<?php
namespace Phutility;

class Tryable {	
	public static function attempt($obj, $method, $args) {		
		if($method == 'try') {
			$tryMethod = array_shift($args);
			$default = empty($args) ? false : array_shift($args);
			if(is_scalar($tryMethod)) {
				$i = new Invokable();
				$i->addToStack($method, array());
			} else if(is_callable($tryMethod)) {
				$i = $tryMethod;
			} else {
				throw new \Exception('Expecting either a method name, a closure or an Invokable object');
			}
		
			if(method_exists($obj, $i->getFirstMethodInStack())) {
				return $i($obj);
			} else {
				if($default instanceof \Exception) { 
					throw new $default;
				} else {
					return $default;
				}
			}
		}
		return false;
	}
}
