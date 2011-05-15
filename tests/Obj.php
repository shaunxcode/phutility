<?php

namespace Phutility\Test\Obj;

require_once '../src/Obj.php';
require_once '../src/Test.php';
require_once '../src/Invokable.php';

use Phutility\Invokable as I;
use Phutility\Obj;
use Phutility\Test;

$guy = Obj::create(
    'name', 'peter', 
    'age', 30,
	'region', 'USA',
    'getAgePlusYears', function($self, $years) {
        return $self->age + $years;
    }
);

Test::assert("Slot Value is correct", $guy->age, 30);
Test::assert("Calling a method", $guy->getAgePlusYears(300), 330);

$guy->age = 500; 

Test::assert("Setting slot value", $guy->age, 500);
Test::assert("Call method depending on changed slot", $guy->getAgePlusYears(300), 800);

$guy->weight = 30;

Test::assert("Setting unset slot", $guy->weight, 30); 

$guy->weight++; 
Test::assert("Increment slot by one", $guy->weight, 31); 

$guy->weight += 50;

Test::assert("Test increment by 50", $guy->weight, 81); 

$guy->getAgePlusYears = function($self, $years) {
    return $self->age + ($years * 2); 
};

Test::assert("Overload slot method", $guy->getAgePlusYears(300), 1100); 

$person = Obj::create(
    'willDieAt', function($self) {
        return $self->age + 100;
    }
);

$guy->addParent($person); 

Test::assert("Call parent slot", $guy->willDieAt(), 600); 

$guy->addParent(Obj::create('haha', function($self) { return "haha{$self->age}"; }));

Test::assert("Call slot added inline", $guy->haha(), "haha500");

$guy->friends = array(
	Obj::create(
		'age', 30, 
		'name', 'ralph')->addParent($person),
	Obj::create(
		'age', 50,
		'name', 'peter')->addParent($person));

Test::assert("Set array as property, check first element", $guy->friends[0]->name, 'ralph');
Test::assert("Test invokable with obj", array_map(I::willDieAt(), $guy->friends), array(130, 150));

$otherGuy = $guy(
	'age', 10, 
	'name', 'sammy');

Test::assert("Cloned object slot is correct", $otherGuy->name, 'sammy');
Test::assert("Cloned object inherits property", $otherGuy->region, 'USA');
Test::assert("Cloned object parents are the same", $otherGuy->willDieAt(), 110); 
Test::assert("Object Cloned from is still same", $guy->name, 'peter');

$number = Obj::create(
	'value', 0, 
	'add', function($self, $val) { 
		$self->value += $val;
		return $self;
	},
	'sub', function($self, $val) {
		$self->value -= $val;	
	});

$five = $number('value', 5);

Test::assert("Number is set to five", $five->value, 5);
Test::assert("Adding number", $five->add(10)->value, 15);

$five->{"double" . ucfirst('value')} = function($self) { return $self->value * 2; };

Test::assert("double the value?", $five->doubleValue(), 30);

function createTableObject($name, $fields) {
	$obj = Obj::create(
		'table', $name,
	    'data', array(),
		'types', array());
	
	$buildGetter = function($fname) { 
		return function($self) use($fname) { 
			return $self->data[$fname];
		};
	};
	
	$buildSetter = function($fname) {
		return function($self, $value) use($fname) {
			$self->data[$fname] = $value;
			return $self;
		};
	};
	
	foreach($fields as $field => $type) {
		$obj->types[$field] = getType($type);
		$obj->{'get' . ucfirst($field)} = $buildGetter($field);
		$obj->{'set' . ucfirst($field)} = $buildSetter($field);
	}
	
	return $obj;
}

$color = createTableObject('Color', array('name' => 'String', 'hex' => 'String'));
$red = $color('data', array('name' => 'Red', 'hex' => '#ff0000'));

Test::assert('Test table name is correct', $red->table, 'Color');
Test::assert('Test higher order object slot', $red->getName(), 'Red');
Test::assert('Test higher order setter', $red->setName('Crimson')->getName(), 'Crimson'); 

Test::totals();