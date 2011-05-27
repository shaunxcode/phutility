<?php
namespace Phutility\Test\Func;

require_once '../src/Func.php';

use Phutility\Test; 
use Phutility\Func as F;
use Phutility\Appos; 

$func = F::rest(function($a, $b, $c, $rest) {
	return $rest;
});

Test::assert("rest args is working", $func(1, 2, 3, 4, 5, 6), array(4, 5, 6));

$func2 = F::rest(function($a, $b, $c, $rest) {
	return $a;
});

Test::assert("other args are correct", $func2(1, 2, 3), 1);

$func3 = F::rest(function($a, $b, $c){});

Test::throwsException("incorrect arg num in func", function() use($func3) { $func3(1); });

Test::throwsException("1nd param must be closure", function() { F::rest('apple'); });

$func4 = F::options(function($options) {
	return $options;
});

test::assert("options passes through", $func4('name', 'peter', 'age', 13), array('name' => 'peter', 'age' => 13));

$func5 = F::options(function($age, $weight, $options) {
	return array('age' => $age, 'weight' => $weight, 'options' => $options);
});

test::assert("extra params", $func5(5, 500, 'name', 'peter', 'hair', 'red'), array('age' => 5, 'weight' => 500, 'options' => array('name' => 'peter', 'hair' => 'red')));

$keyword = F::keyword(function($age, $eyeColor) {
	return array('age' => $age, 'eyeColor' => $eyeColor);
});

test::assert("keyword args normal", $keyword(age, 25, eyeColor, 'blue'), array('age' => 25, 'eyeColor' => 'blue'));
test::assert("keyword args reversed", $keyword(eyeColor, 'blue', age, 25), array('age' => 25, 'eyeColor' => 'blue'));
test::throwsException("wrong arg number", function() use($keyword) { $keyword(eyeColor, 'blue');});
test::throwsException("misnamed keyword", function() use($keyword) { $keyword(eyeBolor, 'blue', age, 35);});

$argsFunc = F::args(function($args) { 
	return $args;
});

test::assert("args works", $argsFunc(1, 2, 3), array(1, 2, 3));

class Circle {}
class Square {}
const Circle = 'Phutility\Test\Func\Circle';
const Square = 'Phutility\Test\Func\Square';
 
$multi = F::matchType(
	Circle, Square, 
	function($c, $s) {
		return "MATCHED CIRCLE SQUARE";
	},
	Square, 
	function($s) {
		return "MATCHED SQUARE";
	});

test::assert("Match Square", $multi(new Square), "MATCHED SQUARE");
test::assert("Match Circle Square", $multi(new Circle, new Square), "MATCHED CIRCLE SQUARE");

class KeywordArgs {
	private $funcs = array();
	
	public function addMethod($selector, $func) {
		$this->funcs[$selector] = $func;
		return $this;
	}
	
	public function __invoke() {
		$args = func_get_args();
		if(count($args) == 1 && isset($this->funcs[current($args)])) {
			return call_user_func_array($this->funcs[current($args)], array($this));
		}
		
		$arglist = \Phutility\Appos::create($args);

		$selector = implode(':', array_keys($arglist));
		if(isset($this->funcs[$selector])) {
			array_unshift($arglist, $this);
			return call_user_func_array($this->funcs[$selector], array_values($arglist));
		} else {
			throw new \Exception("No selector $selector. " . 'Has ' . implode("\n", array_keys($this->funcs)));
		}
	}
	
	public function _() {
		return call_user_func_array(array($this, '__invoke'), func_get_args());
	}
}

$person = new KeywordArgs;
$person
	->addMethod('ifEyeColorIs:setHairToColor', function($self, $eyeColor, $hairColor) {
		return $self;
	})
	->addMethod('setAddressTo:city:state', function($self, $line1, $city, $state) {
		$self->address = "$line1\n$city,$state";
		return $self;
	})
	->addMethod('getAddress', function($self) {
		return $self->address;
	});
	
$person(ifEyeColorIs, 'blue', setHairToColor, 'blonde')
	->_(setAddressTo, '364 North 800 East', city, 'Orem', state, 'Utah')
	->_(getAddress);

test::assert("keyword magic obj arg is set", $person(getAddress), "364 North 800 East\nOrem,Utah");

function TestFunc($name, $eyes, $hair = 'blonde', $age = 0, $weight = 200, $goatFace = 'definitely') {
	return array(
		'name' => $name, 
		'eyes' => $eyes, 
		'hair' => $hair, 
		'age' => $age, 
		'weight' => $weight,
		'goatFace' => $goatFace);
}

function TestFuncWrapper() {
	static $wrapper; 
	if(!$wrapper) {
		$wrapper = F::keyword('\Phutility\Test\Func\TestFunc');
	}

	return call_user_func_array($wrapper, func_get_args());
}

test::assert(
	"real function wrapper instead of var", 
	TestFuncWrapper(
		eyes, 'blue',
		weight, 1200,
		name, 'peter'),
	array(
		'name' => 'peter',
		'eyes' => 'blue',
		'hair' => 'blonde',
		'age' => 0,
		'weight' => 1200,
		'goatFace' => 'definitely')
);