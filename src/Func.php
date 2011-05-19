<?php

namespace Phutility;

class Func {
	public static function rest($num, $func) {
		if(!is_int($num)) {
			throw new \Exception('1st param must be an integer');
		}
		
		if(!$func instanceof \Closure) {
			throw new \Exception('2nd param must be a closure');
		}
		
		return function() use($num, $func) {
			$args = func_get_args();
			
			if(count($args) < $num) { 
				throw new \Exception("Not enough arguments. Expected $num got " . count($args));
			}
			
			return call_user_func_array(
				$func,
				array_merge(
					array_slice($args, 0, $num),
					array(array_slice($args, $num))));
		};
	}
}
