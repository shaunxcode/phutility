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

$toAST = function($nodes, $level = 0) use(&$toAST) {
	$indent = str_repeat("\t", $level);
	$ast = array();
	$func = false;
	foreach($nodes as $node) {
		if(is_array($node)) {
			$func = array_pop($ast);
			if($func) {
				$node = $toAST($node, $level + 1);
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
