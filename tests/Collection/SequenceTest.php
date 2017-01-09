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
use Illuminate\Support\Str;
use Noz\Collection\Sequence;
use RuntimeException;

use function Noz\is_traversable;
use stdClass;

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

    public function testSetValueReturnsNewSequence()
    {
        $seq = new Sequence($exp = ['a','b','c','d','e','f']);
        $this->watchImmutable($seq);
        $seq2 = $seq->set(0, 'g');
        $this->assertEquals('g', $seq2->offsetGet(0));
        $this->assertEquals(['g','b','c','d','e','f'], $seq2->toArray());
        $seq2 = $seq->set(25, 'twentyfive');
        $this->assertEquals(['a','b','c','d','e','f','twentyfive'], $seq2->toArray());
        $this->assertImmutable($seq);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testOffsetSetThrowsException()
    {
        $seq = new Sequence([1,2,3]);
        $seq[1] = 10;
    }

    /**
     * @expectedException RuntimeException
     */
    public function testOffsetUnsetThrowsException()
    {
        $seq = new Sequence([1,2,3]);
        unset($seq[1]);
    }

    public function testExceptReturnsNewSequenceExceptIndexes()
    {
        $seq = new Sequence(['a','b','c','d','e']);
        $this->assertEquals(['a','c','d','e'], $seq->except(1)->toArray());
        $this->assertEquals(['a','c','e'], $seq->except([1,3])->toArray());
        $this->assertEquals(['a','e'], $seq->except('1:3')->toArray());
        $this->assertEquals(['e'], $seq->except(':3')->toArray());
        $this->assertEquals(['a','b','c'], $seq->except('3:')->toArray());
        $this->assertEquals([], $seq->except(':')->toArray());
    }

    public function testOffsetExists()
    {
        $seq = new Sequence(['ab','cd','ef','gh','ij']);
        $this->assertTrue($seq->offsetExists(0));
        $this->assertTrue($seq->offsetExists(1));
        $this->assertTrue($seq->offsetExists(4));
        $this->assertFalse($seq->offsetExists(5));
    }

    public function testOffsetExistsWithBrackets()
    {
        $seq = new Sequence(['ab','cd','ef','gh','ij']);
        $this->assertTrue(isset($seq[0]));
        $this->assertTrue(isset($seq[2]));
        $this->assertTrue(isset($seq[3]));
        $this->assertFalse(isset($seq[5]));
    }

    public function testOffsetExistsWithNegativeOffset()
    {
        $seq = new Sequence(['ab','cd','ef','gh','ij']);
        $this->assertTrue(isset($seq[-5]));
        $this->assertTrue(isset($seq[-3]));
        $this->assertTrue(isset($seq[-1]));
        $this->assertFalse(isset($seq[-6]));
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
     * @expectedException RuntimeException
     * @expectedExceptionMessage Index invalid or out of range
     */
    public function testSeqArrayAccessThrowsExceptionForOutOfRangeIndex()
    {
        $seq = new Sequence(['a','b','c','d','e','f']);
        $seq->offsetGet(6);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Index invalid or out of range
     */
    public function testSeqArrayAccessThrowsExceptionForInvalidIndex()
    {
        $seq = new Sequence(['a' => "a",'b','c','d','e','f']);
        $seq->offsetGet("a");
    }

    /**
     * @expectedException RuntimeException
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

    public function testIsEmptyReturnsTrueForEmptySequence()
    {
        $seq = new Sequence();
        $this->assertTrue($seq->isEmpty());
    }

    public function testIsEmptyReturnsTrueForCallbackThatReturnsTrueForAllItemsInSequence()
    {
        $seq = new Sequence([
            [],
            0,
            ['', 0, null],
            null,
            new Sequence(),
            new ArrayIterator([0,0,0,0,null])
        ]);
        $this->assertTrue($seq->isEmpty(function($val) {
            if (is_traversable($val)) {
                $val = array_filter(\Noz\to_array($val));
            }

            return empty($val);
        }));
    }

    public function testPipeAcceptsFunctionThatAcceptsSequence()
    {
        $seq = new Sequence(['a','b','c','d','e','f']);
        $this->assertEquals('a,b,c,d,e,f', $seq->pipe(function(Sequence $sequence) {
            return implode(',', $sequence->toArray());
        }));
    }

    public function testEveryReturnsTrueIfEveryItemInCollectionHasTruthyValue()
    {
        $seq = new Sequence([1,true,'false',new stdClass,[1]]);
        $this->assertTrue($seq->every());
        $seq = new Sequence([1,true,'false',new stdClass,[]]);
        $this->assertFalse($seq->every());
        $seq = new Sequence([1,true,'false',null,[1]]);
        $this->assertFalse($seq->every());
        $seq = new Sequence([1,true,'',new stdClass,[1]]);
        $this->assertFalse($seq->every());
        $seq = new Sequence([1,false,'false',new stdClass,[1]]);
        $this->assertFalse($seq->every());
        $seq = new Sequence([0,true,'false',new stdClass,[1]]);
        $this->assertFalse($seq->every());
    }

    public function testEveryReturnsTrueIfEveryItemInCollectionReturnsTrueForCallback()
    {
        $seq = new Sequence(['abc','def','ghi','jkl','mno']);
        $this->assertTrue($seq->every(function($val, $key) {
            return strlen($val) == 3;
        }));
        $seq = new Sequence(['abc','def','ghi','jkl','mno','pqr','stu','vxy','z']);
        $this->assertFalse($seq->every(function($val, $key) {
            return strlen($val) == 3;
        }));
        $seq = new Sequence(['abc','d3f','ghi','jkl','mno', 'pqr', '345']);
        $this->assertFalse($seq->every(function($val, $key) {
            return !is_numeric($val);
        }));
        $seq = new Sequence(['abc','123','def','456','ghi','789']);
        $this->assertTrue($seq->every(function($val, $key) {
            return $key % 2 == 0 ?
                !is_numeric($val):
                is_numeric($val);
        }));
    }

    public function testNoneReturnsTrueIfNoItemsCauseCallbackToReturnTrue()
    {
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr']);
        $this->assertTrue($seq->none(function($val, $key) {
            return Str::contains($val, str_split('01234'));
        }));
        $this->assertFalse($seq->none(function($val, $key) {
            return Str::contains($val, str_split('01234a'));
        }));
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr']);
        $this->assertTrue($seq->none(function($val, $key) {
            return Str::startsWith($val, 'abd');
        }));
        $this->assertFalse($seq->none(function($val, $key) {
            return Str::startsWith($val, 'ab');
        }));
        $this->assertFalse($seq->none(function($val, $key) {
            return Str::length($val) == 3;
        }));
        $this->assertTrue($seq->none(function($val, $key) {
            return Str::length($val) != 3;
        }));
    }

    public function testFirstReturnsFirstItemInSequenceOrDefault()
    {
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr']);
        $this->assertEquals('abc', $seq->first(), 'Ensure first item is returned if no callback provided.');
        $seq = new Sequence();
        $this->assertEquals('default', $seq->first(null, 'default'), 'Ensure default is returned if sequence is empty.');
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr']);
        $this->assertEquals('ghi', $seq->first(function($val, $key) {
            return Str::contains($val, 'h');
        }), 'Ensure first matching item is returned if callback provided.');
        $this->assertEquals('default', $seq->first(function($val, $key) {
            return Str::contains($val, 'z');
        }, 'default'), 'Ensure default is returned if callback provided and nothing is found.');
    }

    public function testLastReturnsLastItemInSequenceOrDefault()
    {
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr']);
        $this->assertEquals('pqr', $seq->last(), 'Ensure last item is returned if no callback provided.');
        $seq = new Sequence();
        $this->assertEquals('default', $seq->last(null, 'default'), 'Ensure default is returned if sequence is empty.');
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr', 'hello world', 'bbb']);
        $this->assertEquals('hello world', $seq->last(function($val, $key) {
            return Str::contains($val, 'h');
        }), 'Ensure last matching item is returned if callback provided.');
        $this->assertEquals('default', $seq->last(function($val, $key) {
            return Str::contains($val, 'z');
        }, 'default'), 'Ensure default is returned if callback provided and nothing is found.');
    }

    public function testReverseReturnsSequenceInReverseOrder()
    {
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr']);
        $this->assertEquals(
            ['pqr','mno','jkl','ghi','def','abc'],
            $seq->reverse()->toArray()
        );
    }

    public function testBumpReturnsSequenceWithFirstItemRemoved()
    {
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr']);
        $this->assertEquals(
            ['def','ghi','jkl','mno', 'pqr'],
            $seq->bump()->toArray()
        );
    }

    public function testDropReturnsSequenceWithLastItemRemoved()
    {
        $seq = new Sequence(['abc','def','ghi','jkl','mno', 'pqr']);
        $this->assertEquals(
            ['abc','def','ghi','jkl','mno'],
            $seq->drop()->toArray()
        );
    }
}
