<?php

namespace Phutility;

class Func {
	public static function rest($num, $func, $transform = false) {
		if(!is_int($num)) {
			throw new \Exception('1st param must be an integer');
		}
		
		if(!$func instanceof \Closure) {
			throw new \Exception('2nd param must be a closure');
		}
		
		if(!$transform) { 
			$transform = function($i) { return $i; };
		}
		
		return function() use($num, $func, $transform) {
			$args = func_get_args();
			
			if(count($args) < $num) { 
				throw new \Exception("Not enough arguments. Expected $num got " . count($args));
			}
						
			return call_user_func_array(
				$func,
				array_merge(
					array_slice($args, 0, $num),
					array($transform(array_slice($args, $num)))));
		};
	}
	
	public static function options() {
		$args = func_get_args();

		if(count($args) == 1 && current($args) instanceof \Closure) {
			$num = 0;
			$func = current($args);
		} else if(count($args) == 2) {
			list($num, $func) = $args;
		}
		
		return Func::rest($num, $func, function($i) { return Appos::create($i); });
	}
}
