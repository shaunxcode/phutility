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
    'getAgePlusYears', function($self, $years) {
        return $self->age + $years;
    }
);

Test::assert("Slot Value is correct", $guy->age, 30);
Test::assert("Calling a method", $guy->getAgePlusYears(300), 330);

$guy->age = 500; 

Test::assert("Setting slot value", $guy->age, 500);
Test::assert("Call method depending on changed slot", $guy->getAgePlusYears(300), 800);

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
Test::totals();