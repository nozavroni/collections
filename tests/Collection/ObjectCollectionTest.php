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
}