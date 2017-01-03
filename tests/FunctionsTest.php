<?php

/*
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/nozavroni/collections/blob/master/LICENSE The MIT License (MIT)
 */
namespace NozTest;

use ArrayIterator;
use Closure;
use Exception;
use function
    Noz\collect,
    Noz\invoke,
    Noz\is_traversable,
    Noz\is_arrayable,
    Noz\to_array,
    Noz\typeof,
    Noz\_;
use Noz\Collection\Collection;
use Noz\Contracts\CollectionInterface;
use SplObjectStorage;
use stdClass;

/**
 * Noz functions tests
 *
 * @package   Noz/Collections Unit Tests
 * @copyright (c) 2016, Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 */
class FunctionsTest extends UnitTestCase
{
    public function testCollectionFactoryFunctionUsingArray()
    {
        $coll = collect($arr = [0,1,2,3,4,5,6,7,8,9]);
        $this->assertEquals($arr, $coll->toArray());
    }

    public function testCollectFluidMethods()
    {
        $coll = collect($arr = [
            'f' => 'a',
            1 => '',
            'a' => '',
            2 => 'a',
            3 => 'foobar'
        ])->unique();
        $this->assertEquals(['f' => 'a', 1 => '', 3 => 'foobar'], $coll->toArray());
    }

    // // @todo Create a collection object that works on a string so that you
    // // can call a function for every character in a string and various other
    // // functionality
    // public function testCollectFunctionAcceptsString()
    // {
    //
    // }

    public function testGetValueAcceptsCallbackAndVariadicArguments()
    {
        $this->assertEquals('Hello, Luke Visinoni!', invoke(function($first, $last) {
            return "Hello, {$first} {$last}!";
        }, 'Luke', 'Visinoni'));
    }

    public function testCollectReturnsCollection()
    {
        $this->assertInstanceOf(Collection::class, collect(), 'Ensure Noz\\collect() returns CollectionInterface when passed no params.');
        $this->assertInstanceOf(Collection::class, collect([]), 'Ensure Noz\\collect() returns CollectionInterface when passed an empty array.');
        $this->assertInstanceOf(Collection::class, collect(['foo' => 'bar', 'baz' => 'bin']), 'Ensure Noz\\collect() returns CollectionInterface when passed an array.');
        $this->assertInstanceOf(Collection::class, collect(new ArrayIterator(['foo' => 'bar', 'baz' => 'bin'])), 'Ensure Noz\\collect() returns CollectionInterface when passed an array iterator with an empty array.');
    }

    public function testInvokeInvokesAnonymousFunction()
    {
        $this->assertEquals(6, invoke(function($one, $two, $three) { return $one + $two + $three; }, 1, 2, 3), 'Ensure that Noz\\invoke() uses first argument as callback and invokes it, passing remaining arguments as the function parameters.');
    }

    public function testInvokeCanInvokeStringIfItIsAValidFunctionName()
    {
        $this->assertEquals('string', invoke('gettype', 'str'));
        $this->assertEquals('string', invoke('\Noz\typeof', 'str'));
    }

    public function testInvokeCanInvokeObjectsThatImplementMagicInvokeMethod()
    {
        $coll = collect([3,2,1]);
        $this->assertEquals(3, invoke($coll, 0));
        $this->assertEquals(2, invoke($coll, 1));
    }

    public function testIsTraversableTestsThatValueIsArrayOrTraversable()
    {
        $this->assertFalse(is_traversable(null));
        $this->assertFalse(is_traversable(0));
        $this->assertFalse(is_traversable('not traversable'));
        $this->assertFalse(is_traversable(1.5));
        $this->assertFalse(is_traversable(true));

        $this->assertTrue(is_traversable([]));
        $this->assertTrue(is_traversable(new ArrayIterator([])));
        $this->assertTrue(is_traversable(new ArrayIterator([1,2,3])));
        $this->assertTrue(is_traversable(new SplObjectStorage()));
        $objs = new SplObjectStorage();
        $objs->attach(new stdClass, 'foo');
        $objs->attach(new stdClass, 'bar');
        $this->assertTrue(is_traversable($objs));
    }

    public function testIsArrayableTestsValueCanBeConvertedToArray()
    {
        $this->assertTrue(is_arrayable([]));
        $this->assertTrue(is_arrayable([1,2,3]));
        $this->assertTrue(is_arrayable(new ArrayIterator([])));
        $this->assertTrue(is_arrayable(new ArrayIterator([1,2,3])));
        $this->assertTrue(is_arrayable(collect()));
        $this->assertTrue(is_arrayable(collect([])));
        $this->assertTrue(is_arrayable(collect([1,2,3])));

        $this->assertFalse(is_arrayable(null));
        $this->assertFalse(is_arrayable(0));
        $this->assertFalse(is_arrayable('foo'));
        $this->assertFalse(is_arrayable(true));
        $this->assertFalse(is_arrayable(1.5));
        $this->assertFalse(is_arrayable(new stdClass));
        $this->assertFalse(is_arrayable(new Exception()));
    }

    public function testToArrayReturnsArray()
    {
        $this->assertInternalType('array', to_array('foo', false));
        $this->assertInternalType('array', to_array(1, false));
        $this->assertInternalType('array', to_array(null, false));
        $this->assertInternalType('array', to_array(1.5, false));

        $this->assertInternalType('array', to_array([]));
        $this->assertInternalType('array', to_array(['foo',1,2]));
        $this->assertInternalType('array', to_array(new ArrayIterator([1,2,3])));
        $this->assertInternalType('array', to_array(collect([1,2,3])));

        $this->assertEquals([], to_array(null, false));
        $this->assertEquals([1], to_array(1, false));
        $obj = new stdClass();
        $obj->foo = "bar";
        $obj->bar = "baz";
        $this->assertEquals(['foo' => 'bar','bar' => 'baz'], to_array($obj, false));
    }

    public function testTypeOfReturnsDataType()
    {
        $this->assertEquals('NULL', typeof(null));
        $this->assertEquals('integer', typeof(0));
        $this->assertEquals('double', typeof(1.5));
        $this->assertEquals('string', typeof('foo'));
        $this->assertEquals('boolean', typeof(true));
        $this->assertEquals('array', typeof([]));
        $this->assertEquals('object <stdClass>', typeof(new stdClass));
        $this->assertEquals('resource <stream>', typeof(STDIN));
        $this->assertEquals('stdClass', typeof(new stdClass, false));
        $this->assertEquals('stream', typeof(STDIN, false));
    }

    public function testUnderscoreIsAliasForInvokeIfPassedACallable()
    {
        $c = [
            'anonymous_no_args' => function() { return 'foo'; },
            'anonymous_one_arg' => function($foo) { return $foo; },
            'anonymous_two_args' => function($foo, $bar) { return $foo . $bar; }
        ];
        $this->assertEquals('foo', _($c['anonymous_no_args']));
        $this->assertEquals('BAR', _($c['anonymous_one_arg'], 'BAR'));
        $this->assertEquals('fooBAR', _($c['anonymous_two_args'], 'foo', 'BAR'));

        $closure_no_args = $c['anonymous_no_args']->bindTo($this);
        $this->assertEquals('foo', _($closure_no_args));
        $closure_one_arg = $c['anonymous_one_arg']->bindTo($this);
        $this->assertEquals('boo', _($closure_one_arg, 'boo'));
        $closure_two_args = $c['anonymous_two_args']->bindTo($this);
        $this->assertEquals('booFAR', _($closure_two_args, 'boo', 'FAR'));
    }

    public function testUnderscoreCanInvokeCollectionMethods()
    {
        $coll = collect($expected = ['a' => 'foo','b' => 'bar','c' => 'baz']);
        $this->assertEquals('bar', _([$coll, 'get'], 'b'));
        $this->assertInstanceOf(Collection::class, _([$coll, 'set'], 'b', 'BAR!'));
    }

    public function testCurryWithUnderscore()
    {
        $arrows = function($a) {
            return function($b) use ($a) {
                return function ($c) use ($a, $b) {
                    return "{$a} -> {$b} -> {$c}";
                };
            };
        };
        // kinda verbose, but this is the best it's going to get for currying in PHP...
        $this->assertEquals('foo -> bar -> baz', _(_(_($arrows, 'foo'), 'bar'), 'baz'));
    }
}
