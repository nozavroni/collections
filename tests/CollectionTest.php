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

use DateInterval;
use DateTime;
use InvalidArgumentException;
use Noz\Collection;
use stdClass;

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

    public function testMapReturnsNewCollectionWithChangedValues()
    {
        $coll = new Collection(['a' => 'ark','b' => 'bark','c' => 'car']);
        $this->assertEquals(['a' => 'aark0','b' => 'bbark1','c' => 'ccar2'], $coll->map(function($val, $key, $iter) {
            return "{$key}{$val}{$iter}";
        })->toArray());
    }

    public function testTransformPerformsMapInPlace()
    {
        $coll = new Collection(['a' => 'ark','b' => 'bark','c' => 'car']);
        $transform = $coll->transform(function($val, $key, $iter) {
            return "{$key}{$val}{$iter}";
        });
        $this->assertEquals(['a' => 'aark0','b' => 'bbark1','c' => 'ccar2'], $transform->toArray());
    }

    public function testEachIteratesAndBreaksOnFalse()
    {
        $coll = new Collection(['je' => 'john', 'kb' => 'kevin', 'lv' => 'luke', 'rw' => 'ryan', 'lh' => 'luke']);
        $test = [];
        $coll->each(function($val, $key, $iter) use (&$test) {
            $test["{$key}-{$iter}"] = $val;
            return $iter < 2;
        });
        $this->assertEquals(['je-0' => 'john', 'kb-1' => 'kevin', 'lv-2' => 'luke'], $test);
    }

    public function testFilterReturnsNewCollectionLessPredicate()
    {
        $coll = new Collection(['asd','fpp',0,12,'416',new DateTime, -96, false, null, 'ppj', new stdClass, [1,3,4], 'bar']);
        $this->assertCount(10, $truthy = $coll->filter());
        $this->assertEquals(['asd','fpp',12,'416',new DateTime, -96, 'ppj', new stdClass, [1,3,4], 'bar'], $truthy->values()->toArray());
        $this->assertCount(4, $filtered = $coll->filter(function($val, $key, $iter) {
            return is_numeric($val);
        }));
        $this->assertEquals([0,12,'416', -96], $filtered->values()->toArray());
    }

    public function testExcludeReturnsNewCollectionLessOppositePredicate()
    {
        $coll = new Collection(['asd','fpp',0,12,'416',new DateTime, -96, false, null, 'ppj', new stdClass, [1,3,4], 'bar']);
        $falsey = $coll->exclude();
        $this->assertCount(3, $falsey, "Exclude without a predicate will exclude all \"truthy\" values, so count should equal 3 (for 0, false, and null)");
        $this->assertEquals([0, false, null], $falsey->values()->toArray(), "Exclude without a predicate should return a collection of all the original collection's \"falsey\" values.");
        $this->assertCount(4, $excluded = $coll->exclude(function($val, $key, $iter) {
            if (is_scalar($val)) {
                return !is_numeric($val);
            }
            return true;
        }));
        $this->assertEquals([0,12,'416', -96], $excluded->values()->toArray(), "The predicate returns true only for non-scalar, non-numeric values. This means that all such values should be excluded from the return collection.");
    }

    public function testFirstReturnsFirstItemInCollectionIfPassedNoParams()
    {
        $coll = new Collection(['zzz','yyz','margaret is lovely']);
        $this->assertEquals('zzz', $coll->first());
    }

    public function testFirstReturnsFirstItemToPassTruthTest()
    {
        $coll = new Collection(['zzz','yyz','margaret is lovely', 123, $dt = new DateTime, null, true, false, 'true', 'NULL', -489]);
        // decided not to implement this... yet... see #
//        $this->assertEquals(123, $coll->first('is_numeric'), "First should be able to work with standard PHP functions.");
        $this->assertEquals(123, $coll->first(function($val) { return is_numeric($val); }));
        $this->assertEquals('margaret is lovely', $coll->first(function($val) { return is_string($val) && strpos($val, 'margaret') !== false; }));
        $this->assertSame($dt, $coll->first(function($val) { return is_object($val); }));
        $this->assertEquals(null, $coll->first(function($val) { return is_null($val); }));
        $this->assertEquals('NULL', $coll->first(function($val) { return is_string($val) && strlen($val) == 4 && strtoupper($val) === $val; }));
        $this->assertEquals(-489, $coll->first(function($val) { return is_numeric($val) && $val < 0; }));
    }

    public function testFirstStillWorksEvenIfCollectionIsIndexedAssociatively()
    {
        $coll = new Collection([
            'margaret' => 24,
            'luke' => 30,
            'shane' => 21,
            'jayson' => 36,
            'kevanna' => 32,
            'tony' => 56
        ]);
        $this->assertEquals(24, $coll->first(), "First with no predicate works even if collection is indexed associatively.");
        $this->assertEquals(36, $coll->first(function($val) {
            return is_numeric($val) && $val > 30;
        }), "First with predicate works even if collection is indexed associatively.");
    }

    public function testFirstWillReturnDefaultIfPredicateFailsAndDefaultIsProvided()
    {
        $empty = new Collection();
        $coll = new Collection([
            'margaret' => 24,
            'luke' => 30,
            'shane' => 21,
            'jayson' => 36,
            'kevanna' => 32,
            'tony' => 56
        ]);
        $this->assertEquals(null, $empty->first(), "First should return null if collection is empty and it is passed no args. It should return the default param, which is null.");
        $this->assertEquals('default!', $coll->first(function($val) { return is_object($val); }, 'default!'), "First should return default value if nothing in the collection passes truth test and a default is provided.");
    }

    public function testLastReturnsLastItemInCollectionIfPassedNoParams()
    {
        $coll = new Collection(['zzz','yyz','margaret is lovely']);
        $this->assertEquals('margaret is lovely', $coll->last());
    }

    public function testLastReturnsLastItemToPassTruthTest()
    {
        $coll = new Collection(['zzz','yyz','margaret is lovely', 123, -3, $dt = new DateTime, null, true, false, 'true', $di = new DateInterval('P2W'), 'NULL', 'BULL', -489]);
        // decided not to implement this... yet... see #
//        $this->assertEquals(123, $coll->Last('is_numeric'), "Last should be able to work with standard PHP functions.");
        $this->assertEquals(-489, $coll->last(function($val) { return is_numeric($val); }));
        $this->assertEquals('margaret is lovely', $coll->last(function($val) { return is_string($val) && strpos($val, 'margaret') !== false; }));
        $this->assertSame($di, $coll->last(function($val) { return is_object($val); }));
        $this->assertEquals(null, $coll->last(function($val) { return is_null($val); }));
        $this->assertEquals('BULL', $coll->last(function($val) { return is_string($val) && strlen($val) == 4 && strtoupper($val) === $val; }));
        $this->assertEquals(-489, $coll->last(function($val) { return is_numeric($val) && $val < 0; }));
    }

    public function testLastStillWorksEvenIfCollectionIsIndexedAssociatively()
    {
        $coll = new Collection([
            'margaret' => 24,
            'luke' => 30,
            'shane' => 21,
            'jayson' => 36,
            'kevanna' => 32,
            'isabel' => 7,
            'charlotte' => 5,
            'tony' => 57,
            'lorrie' => 56,
            'jacko' => 3
        ]);
        $this->assertEquals(3, $coll->last(), "last with no predicate works even if collection is indexed associatively.");
        $this->assertEquals(56, $coll->last(function($val) {
            return is_numeric($val) && $val > 30;
        }), "last with predicate works even if collection is indexed associatively.");
    }

    public function testLastWillReturnDefaultIfPredicateFailsAndDefaultIsProvided()
    {
        $empty = new Collection();
        $coll = new Collection([
            'margaret' => 24,
            'luke' => 30,
            'shane' => 21,
            'jayson' => 36,
            'kevanna' => 32,
            'tony' => 56
        ]);
        $this->assertEquals(null, $empty->last(), "last should return null if collection is empty and it is passed no args. It should return the default param, which is null.");
        $this->assertEquals('default!', $coll->last(function($val) { return is_object($val); }, 'default!'), "last should return default value if nothing in the collection passes truth test and a default is provided.");
    }

    public function testReverseReturnsCollectionInReverseOrder()
    {
        $coll = new Collection([1,2,3,4,5,6,7]);
        $this->assertEquals([7,6,5,4,3,2,1], $coll->reverse()->values()->toArray());
    }

    public function testReverseMaintainsIndexesAfterReversing()
    {
        $coll = new Collection([
            'foo' => 'bar',
            'w00t!',
            'boo' => 'whoa',
            'margaret',
            'luke',
            10 => 100
        ]);
        $this->assertSame([
            10 => 100,
            2 => 'luke',
            1 => 'margaret',
            'boo' => 'whoa',
            0 => 'w00t!',
            'foo' => 'bar'
        ], $coll->reverse()->toArray());
    }

    public function testUniqueReturnsCollectionOfOnlyUniqueValues()
    {
        $coll = new Collection(['foo','bar','foo','bar','bar',1,4,2,3,2,1,4,5,6,1,3]);
        $this->assertSame([0 => 'foo',1 => 'bar',5 => 1,6 => 4,7 => 2,8 => 3,12 => 5,13 => 6], $coll->unique()->toArray());
    }

    public function testFactoryReturnsNewCollection()
    {
        $exp = [1,2,3];
        $coll = new Collection($exp);
        $this->assertEquals($coll, Collection::factory($exp));
    }

    public function testIsNumericReturnsTrueIfEveryElementInCollectionIsNumeric()
    {
        $numeric = new Collection([1,2,3,5,3,5,6,3,2.4,-408,"234",0,-90,100000,"0", 100]);
        $hasObj = new Collection([1,2,3,4,5,new DateTime,79]);
        $containsNumArrs = new Collection([[1,2,3],[3,2,1],[10,100,1000]]);
        $this->assertTrue($numeric->isNumeric());
        $this->assertFalse($hasObj->isNumeric());
        $this->assertFalse($containsNumArrs->isNumeric());
    }

    public function testHasOffsetReturnsTrueForNumericOffsetEvenForAssociativelyIndexedCollections()
    {
        $num = new Collection([4,5,6,7,8,9]);
        $this->assertTrue($num->hasOffset(0));
        $this->assertTrue($num->hasOffset(3));
        $this->assertTrue($num->hasOffset(5));
        $this->assertFalse($num->hasOffset(6));
        $inconsecutive = new Collection([3=>4,10=>"foo",5=>'five',25=>25]);
        $this->assertTrue($inconsecutive->hasOffset(0));
        $this->assertTrue($inconsecutive->hasOffset(1));
        $this->assertTrue($inconsecutive->hasOffset(3));
        $this->assertFalse($inconsecutive->hasOffset(5));
        $assoc = new Collection([
            'text' => 123,
            'foo' => 456,
            'bar' => 'seven',
            'eight' => 'nein'
        ]);
        $this->assertTrue($assoc->hasOffset(0));
        $this->assertTrue($assoc->hasOffset(1));
        $this->assertTrue($assoc->hasOffset(3));
        $this->assertFalse($assoc->hasOffset(5));
    }

    public function testHasOffsetWorksWithNegativeOffset()
    {
        $num = new Collection([4,5,6,7,8,9]);
        $this->assertTrue($num->hasOffset(-6));
        $this->assertTrue($num->hasOffset(-4));
        $this->assertTrue($num->hasOffset(-1));
        $this->assertFalse($num->hasOffset(-7));
        $inconsecutive = new Collection([3=>4,10=>"foo",5=>'five',25=>25]);
        $this->assertTrue($inconsecutive->hasOffset(-4));
        $this->assertTrue($inconsecutive->hasOffset(-2));
        $this->assertTrue($inconsecutive->hasOffset(-1));
        $this->assertFalse($inconsecutive->hasOffset(-5));
        $assoc = new Collection([
            'text' => 123,
            'foo' => 456,
            'bar' => 'seven',
            'eight' => 'nein'
        ]);
        $this->assertTrue($assoc->hasOffset(-4));
        $this->assertTrue($assoc->hasOffset(-3));
        $this->assertTrue($assoc->hasOffset(-1));
        $this->assertFalse($assoc->hasOffset(-5));
    }

    public function testGetOffsetKeyWorksWithPositiveAndNegativeOffset()
    {
        $num = new Collection([4,5,6,7,8,9]);
        $this->assertEquals(0, $num->getOffsetKey(0));
        $this->assertEquals(0, $num->getOffsetKey(-6));
        $this->assertEquals(2, $num->getOffsetKey(2));
        $this->assertEquals(2, $num->getOffsetKey(-4));
        $this->assertEquals(5, $num->getOffsetKey(5));
        $this->assertEquals(5, $num->getOffsetKey(-1));
        $inconsecutive = new Collection([3=>4,10=>"foo",5=>'five',25=>25]);
        $this->assertEquals(3, $inconsecutive->getOffsetKey(0));
        $this->assertEquals(3, $inconsecutive->getOffsetKey(-4));
        $this->assertEquals(5, $inconsecutive->getOffsetKey(2));
        $this->assertEquals(5, $inconsecutive->getOffsetKey(-2));
        $this->assertEquals(25, $inconsecutive->getOffsetKey(3));
        $this->assertEquals(25, $inconsecutive->getOffsetKey(-1));
        $assoc = new Collection([
            'text' => 123,
            'foo' => 456,
            'bar' => 'seven',
            'eight' => 'nein'
        ]);
        $this->assertEquals('text', $assoc->getOffsetKey(0));
        $this->assertEquals('text', $assoc->getOffsetKey(-4));
        $this->assertEquals('foo', $assoc->getOffsetKey(1));
        $this->assertEquals('foo', $assoc->getOffsetKey(-3));
        $this->assertEquals('eight', $assoc->getOffsetKey(3));
        $this->assertEquals('eight', $assoc->getOffsetKey(-1));
    }

    public function testGetOffsetWorksWithPositiveAndNegativeOffset()
    {
        $num = new Collection([4,5,6,7,8,9]);
        $this->assertEquals(4, $num->getOffset(0));
        $this->assertEquals(4, $num->getOffset(-6));
        $this->assertEquals(6, $num->getOffset(2));
        $this->assertEquals(6, $num->getOffset(-4));
        $this->assertEquals(9, $num->getOffset(5));
        $this->assertEquals(9, $num->getOffset(-1));
        $inconsecutive = new Collection([3=>4,10=>"foo",5=>'five',25=>25]);
        $this->assertEquals(4, $inconsecutive->getOffset(0));
        $this->assertEquals(4, $inconsecutive->getOffset(-4));
        $this->assertEquals('five', $inconsecutive->getOffset(2));
        $this->assertEquals('five', $inconsecutive->getOffset(-2));
        $this->assertEquals(25, $inconsecutive->getOffset(3));
        $this->assertEquals(25, $inconsecutive->getOffset(-1));
        $assoc = new Collection([
            'text' => 123,
            'foo' => 456,
            'bar' => 'seven',
            'eight' => 'nein'
        ]);
        $this->assertEquals(123, $assoc->getOffset(0));
        $this->assertEquals(123, $assoc->getOffset(-4));
        $this->assertEquals(456, $assoc->getOffset(1));
        $this->assertEquals(456, $assoc->getOffset(-3));
        $this->assertEquals('nein', $assoc->getOffset(3));
        $this->assertEquals('nein', $assoc->getOffset(-1));
    }

    public function testPairsReturnsCollectionAsArrayPairs()
    {
        $coll1 = new Collection([9,8,7,6,5,10]);
        $coll2 = new Collection(['foo' => 'f00','bar' => 'BAR','bin' => 'BIN','baz' => 'BaZ!']);
        $coll3 = new Collection(['wowzers' => 10,'dt' => new DateTime,'no' => 9,'arr' => [1,3,4]]);
        $this->assertEquals([
            [0,9],
            [1,8],
            [2,7],
            [3,6],
            [4,5],
            [5,10],
        ], $coll1->pairs()->toArray());
        $this->assertEquals([
            ['foo','f00'],
            ['bar','BAR'],
            ['bin','BIN'],
            ['baz','BaZ!'],
        ], $coll2->pairs()->toArray());
        $this->assertEquals([
            ['wowzers',10],
            ['dt',new DateTime],
            ['no',9],
            ['arr',[1,3,4]],
        ], $coll3->pairs()->toArray());
    }

    public function testFrequencyReturnsCollectionWhereKeysAreValuesAndValuesAreFrequencyCount()
    {
        $coll = new Collection([1,2,3,4,5,6,7,8,9,0,8,5,6,7,8,5,6,8,5,3,4,5,7,9,7,6,6,6,6,6,6,3,3,2,4,6,7,8,9,0]);
        $this->assertEquals([
            1 => 1,
            2 => 2,
            3 => 4,
            4 => 3,
            5 => 5,
            6 => 10,
            7 => 5,
            8 => 5,
            9 => 3,
            0 => 2
        ], $coll->frequency()->toArray());
    }
}
