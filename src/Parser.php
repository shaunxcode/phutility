<?php

namespace Phutility;

class Parser {
	public static function subExpressions($chars, $callback = false, $brackets = array('(' => ')')) {
		$tree = array();

		if(is_string($chars)) {
			$flatBrackets = array_merge(
				array_keys($brackets), 
				array_values($brackets));
				
			$chars = explode(
				' ', 
				preg_replace('/\s+/', ' ',
					str_replace(
						$flatBrackets, 
						array_map(
							function($i) { return " $i "; }, 
							$flatBrackets), 
						$chars)));
		}

		foreach($chars as $char) {
			if($char == ' ' || $char == '') {
				continue;
			}
			 
			if(isset($brackets[$char])) {
				if(isset($count)) { 
					$count++;
					$branch[] = $char;
				} else {
					$count = 1;
					$bracketType = $char;
					$branch = array();
				} 
				continue; 
			}
			
			if(isset($count)) {
				if($char == $brackets[$bracketType]) {
					$count--;
				}
				
				if($count) {
					$branch[] = $char;
				} else {
					$tree[] = Parser::subExpressions($branch, $callback, $brackets);
					unset($branch);
					unset($count);
				} 
				continue;
			}
					
			$tree[] = $char;
		}

		return $callback ? $callback($tree) : $tree;
	}
}