<?php
namespace Phutility;

class Appos {
	public function create($args) {
		$array = array();
		while(!empty($args)) {
			$array[array_shift($args)] = array_shift($args);
		}
		return $array;
	}
}