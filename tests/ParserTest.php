<?php

namespace Phutility\Test\Parser;

require_once '../src/Parser.php';

use Phutility\Test;
use Phutility\Parser; 

Test::assert(
	"test sub expression parsing", 
	Parser::subExpressions("a b(c) dude e (f g (h i (k l m())))"), 
	array(a, b, array(c), dude, e, array(f, g, array(h, i, array(k, l, m, array())))));
	
$lexer = function($string) {
	static $special = array('[' => 'lambda(', ']' => ')', '{' => 'dict(', '}' => ')', ':' => ': ');
	
	return str_replace(array_keys($special), array_values($special), $string);
};

$toAST = function($nodes) use(&$toAST) {
	$ast = array();
	$func = false;
	foreach($nodes as $node) {
		if(is_array($node)) {
			$func = array_pop($ast);
			if($func) {
				$node = $toAST($node);
				array_unshift($node, $func);
				$ast[] = $node;
				continue;
			} else {
				throw new \Exception("Expected to find a func before an array");
			}
		} else {
			$ast[] = $node;
		}
	}
	
	return $ast;
};

Test::assert(
	"test soy",
	$toAST(Parser::subExpressions($lexer("
	obj(message: 3 restOfMessage: 6 subcall: func(arg1 arg2))
	   (rest: of selector: var withDict: {a: b c:d})
	   (nextSelector: of someSort: withArgs andAClosure: [_(msg: to anon:var_)])"))),
	array(
		array(
			array(
				array(
					"obj",
					"message:",
					"3",
					"restOfMessage:",
					"6",
					"subcall:", 
					array(
						"func",
						"arg1",
						"arg2")),
				"rest:",
				"of",
				"selector:",
				"var",
				"withDict:",
				array(
					"dict",
					"a:",
					"b",
					"c:",
					"d")),
			"nextSelector:",
			"of",
			"someSort:",
			"withArgs",
			"andAClosure:",
			array(
				"lambda",
				array(
					"_",
					"msg:",
					"to",
					"anon:",
					"var_")))));

$toPHP = function($ast, $level = 0) use(&$toPHP) {
	$indent = str_repeat("\t", $level);
	echo $indent . json_encode($ast) . "\n";
	
	if (is_scalar($ast)) {
		if(is_numeric($ast)) {
			return $ast;
		}
		if(substr($ast, -1) == ':') {
			return "'" . substr($ast, 0, -1) . "'";
		} else {
			return '$' . $ast;
		}
	} else if (is_array($ast)) {
		$func = $toPHP(array_shift($ast), $level + 1);
		$args = array();
		foreach($ast as $node) {
			$args[] = $toPHP($node, $level + 1);
		}
		
		return "call_user_func_array({$func}, array(" . implode(', ', $args) . '))';
	}
};
/*
test::assert("php translation", 
	$toPHP($toAST(Parser::subExpressions($lexer("
		obj(message: 3 restOfMessage: 6 subcall: func(arg1 arg2))
		   (rest: of selector: var withDict: {a: b c:d})
		   (nextSelector: of someSort: withArgs andAClosure: [_(msg: to anon:var_)])")))), 
	array('apple'));
	*.