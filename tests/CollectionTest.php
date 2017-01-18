<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 *
 * @version   {version}
 * @copyright Copyright (c) 2017 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */

namespace NozTest;

use InvalidArgumentException;
use Noz\Collection;

class CollectionTest extends UnitTestCase
{
    public function testCollectionIsMutable()
    {
        $coll = new Collection([
            'foo' => 'bin'
        ]);
        $this->assertInstanceOf(Collection::class, $coll);
        $this->assertEquals("bin", $coll->get('foo'));
        $coll2 = $coll->set('foo','bar');
        $this->assertSame($coll, $coll2);
        $this->assertEquals("bar", $coll->get('foo'));
    }

    public function testNullInputReturnsEmptyCollection()
    {
        $coll = new Collection();
        $this->assertInstanceOf(Collection::class, $coll);
        $this->assertEquals([], $coll->toArray());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid input for Noz\Collection::__construct. Expecting traversable data, got "string".
     */
    public function testNonTraversableInputCausesException()
    {
        $coll = new Collection('foo');
    }

    public function testInvokeWithNoParamsIsAliasForToArray()
    {
        $coll = new Collection($exp = [
            'foo' => 'bar',
            4 => 6,
            '-29' => 100000,
            'datetime' => new \DateTime()
        ]);
        $invoked = $coll();
        $this->assertInternalType('array', $invoked);
        $this->assertEquals($exp, $invoked);
    }

    public function testCountReturnsCollectionElementCount()
    {
        $coll = new Collection();
        $this->assertEquals(0, $coll->count());
        $coll->set('foo','bar');
        $this->assertEquals(1, $coll->count());
        $coll->set('too','bar');
        $this->assertEquals(2, count($coll));
    }

    /** @TODO: Test iterator functions */

    public function testSortElementsInPlaceUsingDefaultAlgorithm()
    {
        $coll = new Collection($arr = [
            'bac',
            'cab',
            'aba',
            'aba',
            'aab',
            'aaa',
            'bab',
        ]);
        $this->assertSame($arr, $coll->toArray());
        $this->assertSame([
            'aaa',
            'aab',
            'aba',
            'aba',
            'bab',
            'bac',
            'cab',
        ], array_values($coll->sort()->toArray()), 'Default sort algorithm is case insensitive natural sort comparison (strnatcasecmp).');
    }

    public function testSortUsingCallback()
    {
        $coll = new Collection($arr = [
            'axa',
            'yui',
            'boo',
            'fow',
            'pik',
            'zaz',
            'lub',
        ]);
        $this->assertSame([
            5 => 'zaz',
            4 => 'pik',
            3 => 'fow',
            2 => 'boo',
            6 => 'lub',
            1 => 'yui',
            0 => 'axa',
        ], $coll->sort(function($a, $b) {
            return ord($a[1]) - ord($b[1]);
        })->toArray());
    }

    public function testSortKeyElementsInPlaceUsingDefaultAlgorithm()
    {
        $coll = new Collection($arr = [
            'bac' => 'blood alcohol content',
            'cab' => 'taxi',
            'aba' => 'zabba',
            'abb' => 'silly band',
            'aab' => 'all about bob',
            'aaa' => 'vroom',
            'bab' => 'boogars and butts',
        ]);
        $this->assertSame($arr, $coll->toArray());
        $this->assertSame([
            'aaa' => 'vroom',
            'aab' => 'all about bob',
            'aba' => 'zabba',
            'abb' => 'silly band',
            'bab' => 'boogars and butts',
            'bac' => 'blood alcohol content',
            'cab' => 'taxi',
        ], $coll->sortkeys()->toArray());
    }

    public function testSortKeyUsingCallback()
    {
        $c = new Collection([1,2,3]);
        $this->assertSame($c, $c->sortKeys());
        $coll = new Collection($arr = array_flip([
            'axa',
            'yui',
            'boo',
            'fow',
            'pik',
            'zaz',
            'lub',
        ]));
        $this->assertSame([
            'zaz' => 5,
            'pik' => 4,
            'fow' => 3,
            'boo' => 2,
            'lub' => 6,
            'yui' => 1,
            'axa' => 0,
        ], $coll->sortkeys(function($a, $b) {
            return ord($a[1]) - ord($b[1]);
        })->toArray());
    }

    public function testHasTestsForPresenceOfGivenIndex()
    {
        $coll = new Collection(['mama' => 'lovely', 'daddy' => 'doofus', 'baby' => 'cry eye', 'numeric']);
        $this->assertTrue($coll->has('mama'));
        $this->assertTrue($coll->has('daddy'));
        $this->assertTrue($coll->has('baby'));
        $this->assertFalse($coll->has('numeric'));
        $this->assertTrue($coll->has('0'));
        $this->assertTrue($coll->has(0));
        $this->assertFalse($coll->has(1));
    }

    public function testSetChangesElementsInPlace()
    {
        $coll = new Collection($arr = ['a' => 'arr','b' => 'bar','c' => 'char']);
        $this->assertEquals($arr, $coll->toArray());
        $ret = $coll->set('a','hay');
        $this->assertSame($coll, $ret);
        $this->assertEquals('hay', $coll->get('a'));
    }

    public function testDeleteDestroysElementAtGivenIndex()
    {
        $coll = new Collection([0,1,2,3,4,5]);
        $this->assertSame($coll, $coll->delete(3));
        $this->assertNull($coll->get(3));
        $this->assertEquals([0=>0,1=>1,2=>2,4=>4,5=>5], $coll->toArray());
    }

    public function testIndexOfReturnsFirstIndexForGivenValue()
    {
        $coll = new Collection(['foo','bar','bin','baz','bin','boz']);
        $this->assertEquals(0, $coll->indexOf('foo'));
        $this->assertEquals(1, $coll->indexOf('bar'));
        $this->assertEquals(2, $coll->indexOf('bin'));
        $this->assertEquals(3, $coll->indexOf('baz'));
        $this->assertEquals(2, $coll->indexOf('bin'));
        $this->assertEquals(5, $coll->indexOf('boz'));
    }

    public function testKeysReturnsCollectionOfKeys()
    {
        $coll = new Collection(['a' => 'foo','b' => 'bar','c' => 'bin']);
        $this->assertInstanceOf(Collection::class, $coll->keys());
        $this->assertEquals(['a','b','c'], $coll->keys()->toArray());
    }

    public function testValuesReturnsCollectionOfValues()
    {
        $coll = new Collection(['a' => 'foo','b' => 'bar','c' => 'bin']);
        $this->assertInstanceOf(Collection::class, $coll->values());
        $this->assertEquals(['foo','bar','bin'], $coll->values()->toArray());
    }

    public function testPadPadsInPlace()
    {
        $coll = new Collection(['a','b','c']);
        $this->assertSame($coll, $coll->pad(10));
        $this->assertCount(10, $coll);
        $this->assertEquals(['a','b','c',null,null,null,null,null,null,null], $coll->toArray());
        $coll2 = new Collection();
        $this->assertEquals(['a','a','a','a'], $coll2->pad(4,'a')->toArray());
    }

    // @todo Add transform() that does map() in place
    public function testMapReturnsNewCollectionWithChangedValues()
    {
        $coll = new Collection(['a' => 'ark','b' => 'bark','c' => 'car']);
        $this->assertEquals(['a' => 'aark0','b' => 'bbark1','c' => 'ccar2'], $coll->map(function($val, $key, $iter) {
            return "{$key}{$val}{$iter}";
        })->toArray());
    }
}
