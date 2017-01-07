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

use ArrayAccess;
use Closure;
use Countable;
use Illuminate\Support\Str;
use \Iterator;
use \ArrayIterator;
use Noz\Collection\Collection;
use Noz\Collection\Sequence;
use Noz\Contracts\CollectionInterface;

use function Noz\invoke;
use function
    Noz\is_traversable,
    Noz\collect,
    Noz\dd;

class SequenceTest extends AbstractCollectionTest
{
    public function testCreateSequence()
    {
        $seq = new Sequence($exp = [
            10,
            15,
            20,
            100,
            false,
            5,
            '99',
            'nine'
        ]);
        $this->assertEquals(array_values($exp), $seq->toArray());
    }

    public function testCountReturnsSequenceItemCount()
    {
        $seq = new Sequence($exp = [
            10,
            15,
            20,
            100,
            false,
            5,
            '99',
            'nine'
        ]);
        $this->assertEquals(8, $seq->count());
    }

    public function testInvokableAllowsStandardIndexing()
    {
        $seq = new Sequence($exp = [
            10,
            15,
            20,
            100,
            false,
            5,
            '99',
            'nine'
        ]);
        $this->assertTrue(is_callable($seq));
        $this->assertEquals(10, $seq(0), "Invoke returns offset if passed int.");
        $this->assertEquals(15, $seq(1), "Invoke returns item at offset if passed int.");
        $this->assertSame(false, $seq(4), "Invoke returns offset if passed int.");
        $this->assertNotSame(99, $seq(6), "Invoke returns offset if passed int.");
        $this->assertSame('99', $seq(6), "Invoke returns offset if passed int.");
        $this->assertEquals('nine', $seq(7), "Invoke returns offset if passed int.");
    }

    public function testInvokableAllowsNegativeIndexing()
    {
        $seq = new Sequence($exp = [
            10,
            15,
            20,
            100,
            false,
            5,
            '99',
            'nine'
        ]);
        $this->assertEquals(10, $seq(-8), "Negative index works as negative integer");
        $this->assertEquals(15, $seq(-7));
        $this->assertEquals(20, $seq(-6));
        $this->assertEquals(100, $seq(-5));
        $this->assertEquals(false, $seq('-4'), "Negative index works as string");
        $this->assertEquals('nine', $seq('-1'));
    }

    public function testInvokableAllowsSlicing()
    {
        $seq = new Sequence($exp = [
            10,
            15,
            20,
            100,
            false,
            5,
            '99',
            'nine'
        ]);
        $this->assertEquals([10,15], $seq("0:1")->toArray(), "String works as slice arguments.");
        $this->assertEquals([10], $seq("0:0")->toArray(), "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq("-5:-1")->toArray(), "String works as slice arguments.");
        $this->assertEquals([
            20,
            100,
            false,
            5,
            '99'
        ], $seq("-6:-2")->toArray(), "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq("-5:")->toArray(), "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq("3:")->toArray(), "String works as slice arguments.");
        $this->assertEquals(
            [            10,
                         15,
                         20,
                         100,
            ]
            , $seq(":3")->toArray(), "String works as slice arguments.");
        $this->assertEquals(
            [            10,
                         15,
                         20,
                         100,
            ]
            , $seq(":-5")->toArray(), "String works as slice arguments.");
        $this->assertEquals($exp, $seq(':')->toArray());

    }

    public function testPrependSequence()
    {
        $seq = new Sequence(['b','c','d','e']);
        $this->watchImmutable($seq);
        $this->assertEquals(['a','b','c','d','e'], $seq->prepend('a')->toArray());
        $this->assertImmutable($seq);
    }

    public function testAppendSequence()
    {
        $seq = new Sequence(['b','c','d','e']);
        $this->watchImmutable($seq);
        $this->assertEquals(['b','c','d','e','a'], $seq->append('a')->toArray());
        $this->assertImmutable($seq);
    }
}
