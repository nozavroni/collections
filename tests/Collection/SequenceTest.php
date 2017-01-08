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

use Noz\Collection\Sequence;
use OutOfRangeException;

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
        $this->assertEquals([10,15], $seq['0:1'], "String works as slice arguments.");
        $this->assertEquals([10], $seq['0:0'], "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq['-5:-1'], "String works as slice arguments.");
        $this->assertEquals([
            20,
            100,
            false,
            5,
            '99'
        ], $seq['-6:-2'], "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq['-5:'], "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq['3:'], "String works as slice arguments.");
        $this->assertEquals(
            [            10,
                         15,
                         20,
                         100,
            ]
            , $seq[':3'], "String works as slice arguments.");
        $this->assertEquals(
            [            10,
                         15,
                         20,
                         100,
            ]
            , $seq[':-5'], "String works as slice arguments.");
        $this->assertEquals($exp, $seq[':']);

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

    public function testFoldSequence()
    {
        $seq = new Sequence(['a','b','c','d']);
        $this->assertEquals('a b c d', $seq->fold(function($carry, $val, $key) {
            return trim("$carry $val");
        }));
        $this->assertEquals('0 => a, 1 => b, 2 => c, 3 => d', $seq->fold(function($carry, $val, $key) {
            return trim("{$carry}, {$key} => {$val}", ' ,');
        }));
    }

    /**
     * @expectedException OutOfRangeException
     * @expectedExceptionMessage Index invalid or out of range
     */
    public function testSeqArrayAccessThrowsExceptionForOutOfRangeIndex()
    {
        $seq = new Sequence(['a','b','c','d','e','f']);
        $seq->offsetGet(6);
    }

    /**
     * @expectedException OutOfRangeException
     * @expectedExceptionMessage Index invalid or out of range
     */
    public function testSeqArrayAccessThrowsExceptionForInvalidIndex()
    {
        $seq = new Sequence(['a' => "a",'b','c','d','e','f']);
        $seq->offsetGet("a");
    }

    /**
     * @expectedException OutOfRangeException
     * @expectedExceptionMessage Index invalid or out of range
     */
    public function testSeqArrayAccessUsingSquareBracketsThrowsExceptionForInvalidIndex()
    {
        $seq = new Sequence(['a' => "a",'b','c','d','e','f']);
        $seq["a"];
    }

    public function testSeqArrayAccess()
    {
        $seq = new Sequence(['a','b','c','d','e','f']);
        $this->assertEquals('a', $seq[0]);
        $this->assertEquals('b', $seq[1]);
        $this->assertEquals('f', $seq[5]);
    }

    public function testSeqNegativeArrayAccess()
    {
        $seq = new Sequence(['a','b','c','d','e','f']);
        $this->assertEquals('a', $seq[-6]);
        $this->assertEquals('b', $seq[-5]);
        $this->assertEquals('f', $seq[-1]);
    }

    public function testSquareBracketsAllowsSlicingAndReturnsArray()
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
        $this->assertEquals([10,15], $seq['0:1'], "String works as slice arguments.");
        $this->assertEquals([10], $seq['0:0'], "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq['-5:-1'], "String works as slice arguments.");
        $this->assertEquals([
            20,
            100,
            false,
            5,
            '99'
        ], $seq['-6:-2'], "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq['-5:'], "String works as slice arguments.");
        $this->assertEquals([100, false, 5, '99', 'nine'], $seq['3:'], "String works as slice arguments.");
        $this->assertEquals(
            [            10,
                         15,
                         20,
                         100,
            ]
            , $seq[':3'], "String works as slice arguments.");
        $this->assertEquals(
            [            10,
                         15,
                         20,
                         100,
            ]
            , $seq[':-5'], "String works as slice arguments.");
        $this->assertEquals($exp, $seq[':']);

    }
}
