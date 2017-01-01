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
use Countable;
use \Iterator;
use \ArrayIterator;
use Noz\Collection\Collection;
use Noz\Contracts\CollectionInterface;

use function
    Noz\is_traversable,
    Noz\collect,
    Noz\dd;

class CollectionTest extends AbstractCollectionTest
{
    public function testCollectFactoryReturnsBasicCollectionByDefault()
    {
        $coll = Collection::factory();
        $this->assertInstanceOf(Collection::class, $coll);
    }

    public function testCollectionFactoryPassesInputToCollection()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals($in, $coll->toArray());
    }

    public function testCollectionFactoryReturnsCollectionForEverythingElse()
    {
        $chars = ['a set of characters'];
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(Collection::class, $charColl);
        $chars = ['000', 0, 'zero'];
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(Collection::class, $charColl);
        $chars = [0,1,2,3,4,5,'six'];
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(Collection::class, $charColl);
        $chars = [12345, null, true, false];
        $charColl = Collection::factory($chars);
        $this->assertInstanceOf(Collection::class, $charColl);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCollectionThrowsExceptionIfPassedInvalidData()
    {
        $in = false;
        Collection::factory($in);
    }

    public function testCollectionAcceptsArrayOrIterator()
    {
        $arr = ['foo' => 'bar', 'baz' => 'bin'];
        $arrColl = Collection::factory($arr);
        $this->assertEquals($arr, $arrColl->toArray());

        $iter = new ArrayIterator($arr);
        $iterColl = Collection::factory($iter);
        $this->assertEquals(iterator_to_array($iter), $iterColl->toArray());
    }

    public function testCollectionHas()
    {
        $arr = ['foo' => 'bar', 'baz' => 'bin'];
        $arrColl = Collection::factory($arr);
        $this->assertTrue($arrColl->has('foo'));
        $this->assertFalse($arrColl->has('poo'));
    }

    public function testCollectionHasWorksOnNumericKeys()
    {
        $arr = ['foo', 'bar', 'baz', 'bin'];
        $arrColl = Collection::factory($arr);
        $this->assertTrue($arrColl->has(0));
        $this->assertFalse($arrColl->has(5));
    }

    public function testCollectionGetReturnsValueAtIndex()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals('bar', $coll->get('foo'));
    }

    public function testCollectionGetReturnsDefaultIfIndexNotFound()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals('woo!', $coll->get('poo', 'woo!'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testCollectionRetrieveThrowsExceptionIfIndexNotFound()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $coll->retrieve('poo');
    }

    public function testCollectionSetValueSetsValueInCopyButDoesntChangeOriginal()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->watchImmutable($coll);
        $this->assertNull($coll->get('poo'));
        $this->assertInstanceOf(CollectionInterface::class, $copy = $coll->set('poo', 'woo!'));
        $this->assertNull($coll->get('poo'), 'Ensure original collection is not changed by Collection::set().');
        $this->assertEquals('woo!', $copy->get('poo'), 'Ensure returned collection from Collection::set() has index set to specified value');
        $this->assertNotSame($coll, $copy, 'Ensure return collection from Collection::set() is a copy.');
        $this->assertImmutable($coll);
    }

    public function testCollectionDeleteValueDeletesValueInCopyButNotOriginal()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->watchImmutable($coll);
        $this->assertNotNull($coll->get('foo'));
        $this->assertInstanceOf(Collection::class, $copy = $coll->delete('foo'));
        $this->assertNull($copy->get('foo'));
    }

    public function testCollectionToArrayCallsToArrayRecursively()
{
    $in1 = ['foo' => 'bar', 'baz' => 'bin'];
    $in2 = ['boo' => 'far', 'biz' => 'ban'];
    $in3 = ['doo' => 'dar', 'diz' => 'din'];
    $coll1 = Collection::factory($in1);
    $this->watchImmutable($coll1);
    $coll2 = Collection::factory($in2);
    $this->watchImmutable($coll2);
    $copy2 = $coll2->set('coll1', $coll1);
    $coll3 = Collection::factory($in3);
    $this->watchImmutable($coll3);
    $copy3 = $coll3->set('coll2', $copy2);
    $this->assertEquals([
        'doo' => 'dar', 'diz' => 'din',
        'coll2' => [
            'boo' => 'far', 'biz' => 'ban',
            'coll1' => [
                'foo' => 'bar', 'baz' => 'bin'
            ]
        ]
    ], $copy3->toArray());
    $this->assertImmutable($coll1);
    $this->assertImmutable($coll2);
    $this->assertImmutable($coll3);
}

    public function testCollectionKeysReturnsCollectionOfKeys()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals(['foo','baz'], $coll->keys()->toArray());
    }

    public function testCollectionValuesReturnsCollectionOfValues()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $this->assertEquals(['bar','bin'], $coll->values()->toArray());
    }

    public function testCollectionUnionMergesDataWithCollection()
    {
        $in = ['foo' => 'bar', 'baz' => 'bin'];
        $coll = Collection::factory($in);
        $mergeIn = ['baz' => 'bone', 'boo' => 'hoo'];
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'bone',
            'boo' => 'hoo'
        ], $coll->union($mergeIn)->toArray());
    }

    public function testCollectionContainsReturnsTrueIfRequestedValueInCollection()
    {
        $coll = Collection::factory([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);
        $this->assertTrue($coll->contains('bar'));
        $this->assertFalse($coll->contains('tar'));

        // can also check key
        $this->assertTrue($coll->contains('bar', 'foo'), "Ensure Container::contains() can pass a second param for key. ");
        $this->assertFalse($coll->contains('far', 'poo'));

        // can also accept a callable to determine if collection contains user-specified criteria
        $this->assertTrue($coll->contains(function($val) {
            return strlen($val) > 3;
        }));
        $this->assertFalse($coll->contains(function($val) {
            return strlen($val) < 3;
        }));
        $this->assertFalse($coll->contains(function($val) {
            return $val instanceof Iterator;
        }));

        // can also return true only for given index(es)
        $this->assertTrue($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }, 'goo'));
        $this->assertFalse($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }, 'boo'));

        // check that $key can be used for truthiness checking...
        $this->assertTrue($coll->contains(function($val, $key) {
            if (is_string($key)) {
                return strlen($val) > 3;
            }
            return false;
        }, 'goo'));
        $this->assertFalse($coll->contains(function($val, $key) {
            if (is_numeric($key)) {
                return strlen($val) > 3;
            }
            return false;
        }, 'boo'));
    }

    public function testCollectionContainsAcceptsArrayForIndexParam()
    {
        $coll = Collection::factory([
            'foo' => 'bar',
            'boo' => 'far',
            'goo' => 'czar'
        ]);

        // pass an array of possible indexes
        $this->assertTrue($coll->contains('bar', ['foo','boo']));
        $this->assertFalse($coll->contains('bar', ['goo','too']));

        // we also need to make sure this works with callables
        $this->assertTrue($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }, ['goo','boo']));
        $this->assertFalse($coll->contains(function($val, $key) {
            return strlen($val) > 3;
        }, ['foo','boo']));

        // check that $key can be used for truthiness checking...
        $this->assertFalse($coll->contains(function($val, $key) {
            if (is_string($key)) {
                return strlen($val) > 3;
            }
            return false;
        }, ['foo','boo']));
        $this->assertFalse($coll->contains(function($val, $key) {
            if (is_numeric($key)) {
                return strlen($val) > 3;
            }
            return false;
        }, ['goo','boo']));
    }

    public function testAppentItemsOntoCollectionAddsToEnd()
    {
        $coll = Collection::factory(['a','b','c','d']);
        $this->watchImmutable($coll);
        $copy1 = $coll->append('e');
        $this->assertEquals(['a','b','c','d','e'], $copy1->toArray());
        $copy2 = $copy1->append('f')
             ->append('g')
             ->append(['h', 'i', 'j'])
             ->append('k');
        $this->assertEquals(['a','b','c','d','e','f','g',['h','i','j'], 'k'], $copy2->toArray());
        $this->assertImmutable($coll);
    }

    public function testPrependAddsToBeginningOfCollection()
    {
        $coll = Collection::factory(['a','b','c','d']);
        $this->watchImmutable($coll);
        $copy = $coll->prepend('e');
        $this->assertEquals(['e','a','b','c','d'], $copy->toArray());
        $copy2 = $copy->prepend('k')
             ->prepend(['h', 'i', 'j'])
             ->prepend('g')
             ->prepend('f');
        $this->assertEquals(['f','g',['h','i','j'],'k','e','a','b','c','d'], $copy2->toArray());
        $this->assertImmutable($coll);
    }

    public function testMapReturnsANewCollectionContainingValuesAfterCallback()
    {
        $coll = Collection::factory([0,1,2,3,4,5,6,7,8,9]);
        $coll2 = $coll->map(function($val){
            return $val + 1;
        });
        $this->assertInstanceOf(CollectionInterface::class, $coll2);
        $this->assertEquals([1,2,3,4,5,6,7,8,9,10], $coll2->toArray());
    }

    public function testCollectionReduceReturnsSingleValueUsingCallback()
    {
        $coll = Collection::factory([
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool'
        ]);
        $this->assertEquals('really cool guy', $coll->foldRight(function($item, $carry, $key, $iter) {
            if (strlen($item) >= strlen($carry)) {
                return $item;
            }
            return $carry;
        }, null));

    }

    public function testCollectionFilterReturnsCollectionFilteredUsingCallback()
    {
        $coll = Collection::factory([
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool'
        ]);
        $this->assertEquals([
            'mk'     => 'lady',
            'terry'  => 'what a fool'
        ], $coll->filter(function($v, $k) {
            return strpos($v, 'e') === false;
        })->toArray());
    }

    public function testCollectionIsIterable()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $this->assertInstanceOf(Iterator::class, $coll);
        $this->assertEquals('mk', $coll->key());
        $this->assertEquals('lady', $coll->current());
        $this->assertTrue($coll->valid());
        $this->assertEquals('sweet', $coll->next());
        $this->assertEquals('lorrie', $coll->key());
        $this->assertEquals('sweet', $coll->current());
        $this->assertTrue($coll->valid());
        $this->assertEquals('really cool guy', $coll->next());
        $this->assertEquals('luke', $coll->key());
        $this->assertEquals('really cool guy', $coll->current());
        $this->assertTrue($coll->valid());
        $this->assertEquals('what a fool', $coll->next());
        $this->assertEquals('terry', $coll->key());
        $this->assertEquals('what a fool', $coll->current());
        $this->assertTrue($coll->valid());
        $this->assertNull($coll->next());
        $this->assertFalse($coll->valid());
        $this->assertEquals('lady', $coll->rewind());

        foreach ($coll as $key => $val) {
            $this->assertEquals($exp[$key], $val);
        }
    }

    public function testSPLIteratorFunctionsWorkOnCollection()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $arr = iterator_to_array($coll);
        $this->assertEquals($exp, $arr);
        $this->assertEquals($arr, $coll->toArray());
    }

    //public function testToArrayUsesIteratorMethods()
    //{
        // @todo Need to stub the collection and change the "current" method to return something different
        // so I can test that foreach always returns the value that current returns
    //}

    public function testCollectionReturnsTrueForIsTraversable()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $this->assertTrue(is_traversable($coll));
    }

    public function testCollectionIsCountable()
    {
        $coll = Collection::factory($exp = [
            'mk'     => 'lady',
            'lorrie' => 'sweet',
            'luke'   => 'really cool guy',
            'terry'  => 'what a fool',
        ]);
        $this->assertInstanceOf(Countable::class, $coll);
        $this->assertEquals(4, $coll->count());
    }

    public function testPairsReturnsKeyValPairs()
    {
        $coll = Collection::factory([
            'foo' => 'bar',
            'bin' => 'baz',
            'boo' => 'far',
        ]);
        $this->assertEquals([
            ['foo', 'bar'],
            ['bin', 'baz'],
            ['boo', 'far'],
        ], $coll->pairs()->toArray());
    }

    public function testHasPositionReturnsNumericPositionRegardlessOfKeyType()
    {
        $coll = Collection::factory([
            'foo' => 'bar',
            0 => 'baz',
            'test' => 'best',
            10 => 'ten',
            'fifth' => 'this is the fifth'
        ]);
        $this->assertTrue($coll->hasOffset(0));
        $this->assertTrue($coll->hasOffset(1));
        $this->assertTrue($coll->hasOffset(2));
        $this->assertTrue($coll->hasOffset(3));
        $this->assertTrue($coll->hasOffset(4));
        $this->assertFalse($coll->hasOffset(5));
    }

    public function testIndexOfReturnsIndexForGivenValue()
    {
        $coll = new Collection(['foo','bar','baz', 'boo' => 'woo']);
        $this->assertEquals(1, $coll->indexOf('bar'));
        $this->assertEquals('boo', $coll->indexOf('woo'));
        $this->assertNull($coll->indexOf('notinarray', false));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testIndexOfThrowsExceptionIfValueNotFoundAndThrowParamIsTrue()
    {
        $coll = new Collection(['foo','bar','baz', 'boo' => 'woo']);
        $this->assertEquals(1, $coll->indexOf('bar'));
        $this->assertEquals('boo', $coll->indexOf('woo'));
        $this->assertNull($coll->indexOf('notinarray', true));
    }

    // BEGIN Numeric data method tests

    public function testIncrementDecrementAddsSubtractsOneFromGivenKey()
    {
        $coll = collect([10,15,20,25,50,100]);
        $this->watchImmutable($coll);
        $zero = 0;
        $copy = $coll->increment($zero);
        $this->watchImmutable($copy);
        $this->assertEquals(11, $copy->get($zero));
        $copy2 = $copy->increment($zero)
            ->increment($zero)
            ->increment($zero)
            ->increment($zero);
        $this->watchImmutable($copy2);
        $this->assertEquals(15, $copy2->get($zero));
        $copy3 = $copy2->decrement($zero);
        $this->watchImmutable($copy3);
        $this->assertEquals(14, $copy3->get($zero));
        $copy4 = $copy3->decrement($zero)
            ->decrement($zero);
        $this->watchImmutable($copy4);
        $this->assertEquals(12, $copy4->get($zero));
        $this->assertImmutable($coll);
        $this->assertImmutable($copy);
        $this->assertImmutable($copy2);
        $this->assertImmutable($copy3);
        $this->assertImmutable($copy4);
    }

    public function testIncrementDecrementWithIntervalAddsSubtractsIntervalFromGivenKey()
    {
        $coll = collect([10,15,20,25,50,100]);
        $this->watchImmutable($coll);
        $zero = 0;
        $copy = $coll->increment($zero, 5);
        $this->watchImmutable($copy);
        $this->assertEquals(15, $copy->get($zero));
        $copy2 = $copy->increment($zero, 100);
        $this->watchImmutable($copy2);
        $this->assertEquals(115, $copy2->get($zero));
        $copy3 = $copy2->decrement($zero, 2);
        $this->watchImmutable($copy3);
        $this->assertEquals(113, $copy3->get($zero));
        $copy4 = $copy3->decrement($zero, 1000);
        $this->watchImmutable($copy4);
        $copy5 = $copy4->decrement($zero);
        $this->watchImmutable($copy5);
        $this->assertEquals(-888, $copy5->get($zero));
        $this->assertImmutable($coll);
        $this->assertImmutable($copy);
        $this->assertImmutable($copy2);
        $this->assertImmutable($copy3);
        $this->assertImmutable($copy4);
        $this->assertImmutable($copy5);
    }


    public function testSumMethodSumsCollection()
    {
        $coll = collect([10,20,30,100,60,80]);
        $this->assertEquals(300, $coll->sum());
    }

    public function testAverageMethodAveragesCollection()
    {
        $coll = collect([10,20,30,100,60,80]);
        $this->assertEquals(50, $coll->average());
    }

    public function testModeMethodReturnsCollectionMode()
    {
        $coll = collect([10,20,30,100,60,80,10,20,100,10,50,40,10,20,50,60,80]);
        $this->assertEquals(10, $coll->mode());
    }

    public function testMedianMethodReturnsCollectionMedian()
    {
        $coll = collect([1,10,20,30,100,60,80,10,20,100,10,50,40,10,20,50,60,80]);
        $this->assertEquals(35, $coll->median());

        $coll = collect([1,20,300,4000]);
        $this->assertEquals(160, $coll->median());

        // $coll = collect(['one','two','three','four','five']);
        // $this->assertEquals('four', $coll->median());

        // @todo Maybe for strings median should work with string length?
        // $coll = collect(['hello','world','this','will','do','weird','stuff','yes','it','will']);
        // $this->assertEquals(0, $coll->median());

        $coll = collect([1]);
        $this->assertEquals(1, $coll->median());

        $coll = collect([1,2]);
        $this->assertEquals(1.5, $coll->median());
    }

    public function testCountsReturnsCollectionOfCounts()
    {
        $data = [1,1,1,2,0,2,2,3,3,3,3,3,3,3,4,5,6,6,7,8,9,0];
        $coll = collect($data);
        $this->assertInstanceOf(Collection::class, $coll);
        $counts = $coll->counts();
        $this->assertInstanceOf(Collection::class, $counts);
        $this->assertEquals([
            1 => 3,
            2 => 3,
            3 => 7,
            4 => 1,
            5 => 1,
            6 => 2,
            7 => 1,
            8 => 1,
            9 => 1,
            0 => 2
        ], $counts->toArray());
    }
}