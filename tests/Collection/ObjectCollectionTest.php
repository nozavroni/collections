<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace NozTest\Collection;

use ArrayIterator;
use BadMethodCallException;
use Exception;
use InvalidArgumentException;
use stdClass;
use Noz\Collection\ObjectCollection;

class ObjectCollectionTest extends AbstractCollectionTest
{
    public function testInstantiateObjectCollection()
    {
        $objects = new ObjectCollection([
            new stdClass("this is an object"),
            new ArrayIterator(['this','is','an','object']),
            new Exception("this is an exception, but it's still an object")
        ]);
        $this->assertInstanceOf(ObjectCollection::class, $objects);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInstantiateObjectCollectionWithNonObjectsThrowsException()
    {
        $objects = new ObjectCollection([
            new stdClass("this is an object"),
            new ArrayIterator(['this','is','an','object']),
            new Exception("this is an exception, but it's still an object"),
            "this is not an object, unfortunately"
        ]);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testObjectCollectionCannotBeCastToString()
    {
        $objects = new ObjectCollection([new stdClass]);
        $objects = $objects->join();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetThrowsExceptionIfPassedNonObject()
    {
        $objects = new ObjectCollection();
        $objects = $objects->set("index", "this is not an object");
    }

    public function testSetMethodSetsObjectAtGivenIndex()
    {
        $objects = new ObjectCollection();
        $objects = $objects->set("index", $expected = new stdClass);
        $this->assertSame($expected, $objects->get("index"));
    }

    public function testPushMethodPushesObjectOntoTheEnd()
    {
        $objects = new ObjectCollection([$first = new stdClass("first"), $second = new stdClass("second")]);
        $objects = $objects->push($expected = new stdClass);
        $this->assertSame($expected, $objects->pop());
        $this->assertSame($second, $objects->pop());
        $this->assertSame($first, $objects->pop());
        $this->assertNull($objects->pop());
    }

    public function testUnshiftMethodUnshiftsObjectOntoTheBeginning()
    {
        $objects = new ObjectCollection([$first = new stdClass("first"), $second = new stdClass("second")]);
        $objects = $objects->unshift($expected = new stdClass);
        $this->assertSame($expected, $objects->shift());
        $this->assertSame($first, $objects->shift());
        $this->assertSame($second, $objects->shift());
        $this->assertNull($objects->shift());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPadMethodCanOnlyPadWithObjects()
    {
        $objects = new ObjectCollection([$first = new stdClass("first"), $second = new stdClass("second")]);
        $padded = $objects->pad(5, $expected = "a string is not allowed");
    }

    public function testPadMethodPadsWithClonesOfGivenObject()
    {
        $objects = new ObjectCollection([$first = new stdClass("first"), $second = new stdClass("second")]);
        $origsize = $objects->count();
        $padded = $objects->pad($padsize = 5, $expected = new stdClass("this is a string"));
        $this->assertEquals($padsize, $padded->count());
        $this->assertEquals($origsize, $objects->count());
        $this->assertNotSame($expected, $padded->pop());
        $this->assertNotSame($expected, $padded->pop());
        $this->assertNotSame($expected, $padded->pop());
    }
}