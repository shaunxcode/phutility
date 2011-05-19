<?php
namespace Phutility;

class Appos {
	public function create($args) {
		if(count($args) % 2) {
			throw new \Exception('Must have even number arguments. Got ' . json_encode($args));
		}
		
		$array = array();
		while(!empty($args)) {
			$array[array_shift($args)] = array_shift($args);
		}
		return $array;
	}
}