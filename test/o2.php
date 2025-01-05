<?php
include __DIR__ . '/../vendor/autoload.php';
class test implements \ArrayAccess, \Countable{
	use \nx\parts\o2;
}
$t = new test();
$t([
	'abc' => 123,
]);
test('load default', function() use ($t){
	expect($t['abc'])->toBe(123);
});
test('get NULL', function() use ($t){
	expect($t['ghi'])->toBeNull();
});
test('set & get', function() use ($t){
	$t['def'] = 456;
	expect($t['def'])->toBe(456);
});
test('callable', function() use ($t){
	$t([
		'fn' => function(){
			return 123;
		},
	]);
	expect($t['fn'])->toBe(123);
});
test('callable object', function() use ($t){
	class tt{
		public function __invoke(): int{
			return 456;
		}
	}
	$t([
		'cls' => new tt(),
	]);
	expect($t['cls'])->toBe(456);
});

