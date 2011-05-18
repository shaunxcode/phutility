<?php

namespace Phutility\Test\Obj;

require_once '../src/Obj.php';
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
		'name', 'peter')->addParent($person)
);

Test::assert("Set array as property, check first element", $guy->friends[0]->name, 'ralph');
Test::assert("Test invokable with obj", array_map(I::willDieAt(), $guy->friends), array(130, 150));

$otherGuy = $guy(
	'age', 10, 
	'name', 'sammy'
);

Test::assert("Cloned object slot is correct", $otherGuy->name, 'sammy');
Test::assert("Cloned object inherits property", $otherGuy->region, 'USA');
Test::assert("Cloned object parents are the same", $otherGuy->willDieAt(), 110); 
Test::assert("Object Cloned from is still same", $guy->name, 'peter');

$number = Obj::create(
	'value', 0, 
	'add', function($self, $val) { 
		return $self->copy('value', $self->value + $val);
	},
	'sub', function($self, $val) {
		return $self->copy('value', $self->value - $val);	
	}
);

$five = $number('value', 5);

Test::assert("Number is set to five", $five->value, 5);
Test::assert("Adding number", $five->add(10)->value, 15);

$five->{"double" . ucfirst('value')} = function($self) { return $self->value * 2; };

Test::assert("double the value?", $five->doubleValue(), 10);

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

	return function($data) use($obj) { 
		return $obj('data', $data);
	};
}

$color = createTableObject('Color', array('name' => 'String', 'hex' => 'String'));
$red = $color(array('name' => 'Red', 'hex' => '#ff0000'));

Test::assert('table name is correct', $red->table, 'Color');
Test::assert('higher order object slot', $red->getName(), 'Red');
Test::assert('higher order setter', $red->setName('Crimson')->getName(), 'Crimson'); 

Test::assert('instantiated w/ array', Obj::create(array('a' => 'b', 'c' => 'd'))->a, 'b');
Test::assert('instantiated w/o array', Obj::create('a', 'b', 'c', 'd')->a, 'b');

$hello = Obj::create('sayHello', function() { return 'Hello '; }); 
$world = Obj::create('sayWorld', function() { return 'World'; });
$myHelloWorld = Obj::create('sayExclamationMark', function() { return '!'; })->addParents($hello, $world);
$o = $myHelloWorld();

Test::assert('Traits test', $o->sayHello() . $o->sayWorld() . $o->sayExclamationMark(), 'Hello World!');

$hello->monkeyTime = function() { return 'pound the keys!'; };

Test::assert("Parent mixin new func w/ child calling", $o->monkeyTime(), 'pound the keys!'); 


$Transaction = obj::create(
	'priorBalance', 0, 
	'change', 0,
	'newBalance', 0,
	'time', 0);

$BankAccount = obj::create(
	'balance', 0, 
	'transactions', array(),
	'withdraw', function($self, $amount) use($Transaction) { 
		if($self->balance >= $amount) {
			$t = $Transaction(
				'priorBalance', $self->balance, 
				'change', -$amount,
				'time', time());
				
			$self->balance -= $amount;
			$t->newBalance = $self->balance;
			
			$self->transactions[] = $t;
		} else {
			throw new \Exception('Balance not high enough');
		}
		
		return $self;
	},
	'deposit', function($self, $amount) use($Transaction) {
		$t = $Transaction(
			'priorBalance', $self->balance,
			'change', $amount,
			'time', time());
			
		$self->balance += $amount;
		$t->newBalance = $self->balance;
		
		$self->transactions[] = $t;
		
		return $self;
	});

$myAccount = $BankAccount(
	'balance', 500, 
	'transactions', array());

Test::assert('account balance is 500', $myAccount->balance, 500); 

$myAccount->deposit(500);

Test::assert('account balance is 1000 after deposit', $myAccount->balance, 1000);

$myAccount->withdraw(200);

Test::assert('account balance is 800 after withdraw', $myAccount->balance, 800);