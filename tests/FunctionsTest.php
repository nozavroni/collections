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

use function
    Noz\collect,
    Noz\invoke;

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
}
