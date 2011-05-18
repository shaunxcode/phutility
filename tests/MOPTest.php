<?php
namespace Phutility\MOP;

require_once '../src/MOP.php';

use Phutility\Test;

defclass('ColorMixin',
	slot('color') 
		->initForm('red')
		->initArg('color')
		->accessor('color'));

defclass('Rectangle', 
	slot('width'), 
	slot('height'));
					
defclass('ColorRectangle', 
	extend(ColorMixin, Rectangle),
	slot('clearp')
		->initForm(nil)
		->initArg(_clearp)
		->accessor('clearp'));
				
defmethod('paint', 
	arity(Rectangle, BitmapDisplay),
	function($shape, $medium) {
		echo "Specialized by arity for Rectangle\n";
});

defmethod('paint',
	arity(Circle, BitmapDisplay),
	function($shape, $medium) {
		echo "Specialized by arity for Circle\n";
});

defmethod('paint', 
	before(),
	arity(Shape, ColorMixin), 
	function($shape, $medium) {
		echo "Specialized before in case of having color mixin";
});

Test::assert('test class name exists', getclass('ColorMixin')->get('name'), 'ColorMixin');