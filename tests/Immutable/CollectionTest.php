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
namespace NozTest\Immutable;

use ArrayAccess;
use Closure;
use Countable;
use Illuminate\Support\Str;
use \Iterator;
use \ArrayIterator;
use Noz\Immutable\Collection;
use Noz\Contracts\CollectionInterface;

use function Noz\invoke;
use function
    Noz\is_traversable,
    Noz\collect,
    Noz\dd;

class CollectionTest extends AbstractCollectionTest
{
    // BEGIN NEW TESTS....

    public function testSortReturnsNewCollectionSortedUsingDefaultAlgorithm()
    {
        $coll = collect($this->testdata['multi']['names']);
        $this->watchImmutable($coll);
        $this->assertEquals([
            0 => 'Alivia Kemmer',
            1 => 'Gillian Wisozk',
            2 => 'Kelley Zemlak',
            3 => 'Lily Heaney',
            4 => 'Mr. Blaze Daugherty MD',
            5 => 'Mr. Jaquan Swift',
            6 => 'Mrs. Aaliyah Paucek Jr.',
            7 => 'Mrs. Meredith Wyman',
            8 => 'Mrs. Raegan Shields PhD',
            9 => 'Natalia Keebler'
        ], $coll->sort()->values()->toArray());
        $this->assertImmutable($coll);
    }

    public function testSortReturnsNewCollectionSortedUsingCustomAlgorithm()
    {
        $coll = collect($this->testdata['multi']['names']);
        $this->watchImmutable($coll);
        $sorted = $coll->sort(function($str1, $str2) {
            $str3 = collect(str_split($str1))->unique()->count();
            $str4 = collect(str_split($str2))->unique()->count();
            return $str3 - $str4;
        });
        $this->assertEquals([
            0 => 'Kelley Zemlak',
            1 => 'Lily Heaney',
            2 => 'Alivia Kemmer',
            3 => 'Natalia Keebler',
            4 => 'Gillian Wisozk',
            5 => 'Mr. Jaquan Swift',
            6 => 'Mrs. Meredith Wyman',
            7 => 'Mr. Blaze Daugherty MD',
            8 => 'Mrs. Raegan Shields PhD',
            9 => 'Mrs. Aaliyah Paucek Jr.'
        ], $sorted->values()->toArray());
        $this->assertImmutable($coll);
    }

    public function testSortKeysReturnsNewCollectionSortedByKeyUsingDefaultAlgorithm()
    {
        $coll = collect([
            0 => 'Mrs. Raegan Shields PhD',
            1 => 'Natalia Keebler',
            2 => 'Mr. Blaze Daugherty MD',
            3 => 'Lily Heaney',
            4 => 'Mr. Jaquan Swift',
            5 => 'Kelley Zemlak',
            6 => 'Mrs. Aaliyah Paucek Jr.',
            7 => 'Mrs. Meredith Wyman',
            8 => 'Gillian Wisozk',
            9 => 'Alivia Kemmer'
        ])->flip();
        $this->watchImmutable($coll);
        $sorted = $coll->sortKeys();
        $this->assertEquals([
            'Alivia Kemmer' => 9,
            'Gillian Wisozk' => 8,
            'Kelley Zemlak' => 5,
            'Lily Heaney' => 3,
            'Mr. Blaze Daugherty MD' => 2,
            'Mr. Jaquan Swift' => 4,
            'Mrs. Aaliyah Paucek Jr.' => 6,
            'Mrs. Meredith Wyman' => 7,
            'Mrs. Raegan Shields PhD' => 0,
            'Natalia Keebler' => 1
        ], $sorted->toArray());
        $this->assertImmutable($coll);
    }

    // @todo Need MANY more sorting tests...
    // @todo Need tests for Collection::has()
    // @todo Need tests for Collection::get()
    // @todo Need tests for Collection::retrieve()
    // @todo Need tests for Collection::set()
    // @todo Need tests for Collection::add()

    public function testIndexOfReturnsIndexOfFirstValueOccurrence()
    {
        $coll = collect([1,2,3,'a','b','c', 'p' => 'gee']);
        $this->assertEquals(2, $coll->indexOf(3));
        $this->assertEquals(5, $coll->indexOf('c'));
        $this->assertEquals('p', $coll->indexOf('gee'));
    }

    // @todo Need tests for Collection::keys()
    // @todo Need tests for Collection::values()
    // @todo Need tests for Collection::contains()
    // @todo Need tests for Collection::prepend()
    // @todo Need tests for Collection::append()

    public function testChunkSplitsCollectionIntoChunks()
    {
        $coll = collect([0,1,2,3,4,5,6,7,8,9]);
        $this->watchImmutable($coll);
        $this->assertEquals([[0,1,2,3,4,5,6,7,8,9]], $coll->chunk(11)->toArray());
        $this->assertEquals([[0,1,2,3,4,5,6,7,8,9]], $coll->chunk(10)->toArray());
        $this->assertEquals([[0,1,2,3,4,5,6,7,8],[9]], $coll->chunk(9)->toArray());
        $this->assertEquals([[0,1,2,3,4,5,6,7],[8,9]], $coll->chunk(8)->toArray());
        $this->assertEquals([[0,1,2,3,4,5,6],[7,8,9]], $coll->chunk(7)->toArray());
        $this->assertEquals([[0,1,2,3,4,5],[6,7,8,9]], $coll->chunk(6)->toArray());
        $this->assertEquals([[0,1,2,3,4],[5,6,7,8,9]], $coll->chunk(5)->toArray());
        $this->assertEquals([[0,1,2,3],[4,5,6,7],[8,9]], $coll->chunk(4)->toArray());
        $this->assertEquals([[0,1,2],[3,4,5],[6,7,8],[9]], $coll->chunk(3)->toArray());
        $this->assertEquals([[0,1],[2,3],[4,5],[6,7],[8,9]], $coll->chunk(2)->toArray());
        $this->assertEquals([[0],[1],[2],[3],[4],[5],[6],[7],[8],[9]], $coll->chunk(1)->toArray());
        $this->assertEquals([[0],[1],[2],[3],[4],[5],[6],[7],[8],[9]], $coll->chunk(-1)->toArray());
        $this->assertImmutable($coll);
    }

    public function testCombineReturnsNewCollectionWithValuesFromInputArray()
    {
        $coll = collect([
            'luke' => 30,
            'margaret' => 24,
            'zach' => 22,
            'kevanna' => 31,
            'lorrie' => 56
        ]);
        $this->watchImmutable($coll);
        $combined = $coll->combine([1986,1992,1994,1985,1961]);
        $this->assertEquals([
            'luke' => 1986,
            'margaret' => 1992,
            'zach' => 1994,
            'kevanna' => 1985,
            'lorrie' => 1961
        ], $combined->toArray());
        $this->assertImmutable($coll);
    }

    public function testCombineReturnsNewCollectionWithValuesFromInputCollection()
    {
        $coll = collect([
            'luke' => 30,
            'margaret' => 24,
            'zach' => 22,
            'kevanna' => 31,
            'lorrie' => 56
        ]);
        $this->watchImmutable($coll);
        $combined = $coll->combine($years = collect([1986,1992,1994,1985,1961]));
        $this->watchImmutable($years);
        $this->assertEquals([
            'luke' => 1986,
            'margaret' => 1992,
            'zach' => 1994,
            'kevanna' => 1985,
            'lorrie' => 1961
        ], $combined->toArray());
        $this->assertImmutable($years);
        $this->assertImmutable($coll);
    }

    public function testDiffReturnsNewCollectionWithOnlyValuesNotContainedInInputArray()
    {
        $ages = collect([
            'luke' => 30,
            'margaret' => 24,
            'zach' => 22,
            'kevanna' => 31,
            'lorrie' => 56
        ]);
        $this->watchImmutable($ages);
        $diff = $ages->diff([1,2,3,31,22,5,56,10]);
        $this->assertEquals([
            'luke' => 30,
            'margaret' => 24
        ], $diff->toArray());
        $this->assertImmutable($ages);
    }

    public function testDiffReturnsNewCollectionWithOnlyValuesNotContainedInInputCollection()
    {
        $ages = collect([
            'luke' => 30,
            'margaret' => 24,
            'zach' => 22,
            'kevanna' => 31,
            'lorrie' => 56
        ]);
        $this->watchImmutable($ages);
        $diff = $ages->diff(collect([1,2,3,31,22,5,56,10]));
        $this->assertEquals([
            'luke' => 30,
            'margaret' => 24
        ], $diff->toArray());
        $this->assertImmutable($ages);
    }

    public function testDiffKeysReturnsNewCollectionWithOnlyKeysNotContainedInInputArray()
    {
        $ages = collect([
            'luke' => 30,
            'margaret' => 24,
            'zach' => 22,
            'kevanna' => 31,
            'lorrie' => 56
        ]);
        $this->watchImmutable($ages);
        $diff = $ages->diffKeys([
            'dave' => 1954,
            'lyle' => 1981,
            'jayson' => 1980,
            'luke' => 1986,
            'margaret' => 1992
        ]);
        $this->assertEquals([
            'zach' => 22,
            'kevanna' => 31,
            'lorrie' => 56
        ], $diff->toArray());
        $this->assertImmutable($ages);
    }

    public function testDiffKeysReturnsNewCollectionWithOnlyKeysNotContainedInInputCollection()
    {
        $ages = collect([
            'luke' => 30,
            'margaret' => 24,
            'zach' => 22,
            'kevanna' => 31,
            'lorrie' => 56
        ]);
        $this->watchImmutable($ages);
        $diff = $ages->diffKeys(collect([
            'jayson' => 1980,
            'kevanna' => 1985,
            'luke' => 1986,
            'zach' => 1994,
        ]));
        $this->assertEquals([
            'lorrie' => 56,
            'margaret' => 24
        ], $diff->toArray());
        $this->assertImmutable($ages);
    }

    public function testEveryReturnsANewCollectionWithEveryNthItem()
    {
        $coll = collect([0,1,2,3,4,5,6,7,8,9]);
        $this->watchImmutable($coll);
        $every2nd = $coll->nth(2);
        $this->assertEquals([
            0 => 0,
            2 => 2,
            4 => 4,
            6 => 6,
            8 => 8
        ], $every2nd->toArray());
        $every3rd = $coll->nth(3);
        $this->assertEquals([
            0 => 0,
            3 => 3,
            6 => 6,
            9 => 9,
        ], $every3rd->toArray());
        $every4th = $coll->nth(4);
        $this->assertEquals([
            0 => 0,
            4 => 4,
            8 => 8
        ], $every4th->toArray());
        $every5th = $coll->nth(5);
        $this->assertEquals([
            0 => 0,
            5 => 5
        ], $every5th->toArray());
        $every6th = $coll->nth(6);
        $this->assertEquals([
            0 => 0,
            6 => 6,
        ], $every6th->toArray());
        $every7th = $coll->nth(7);
        $this->assertEquals([
            0 => 0,
            7 => 7
        ], $every7th->toArray());
        $every8th = $coll->nth(8);
        $this->assertEquals([
            0 => 0,
            8 => 8
        ], $every8th->toArray());
        $every9th = $coll->nth(9);
        $this->assertEquals([
            0 => 0,
            9 => 9
        ], $every9th->toArray());
        $this->assertImmutable($coll);
    }

    public function testEveryReturnsANewCollectionWithEveryNthItemStartingAtOffset()
    {
        $coll = collect([0,1,2,3,4,5,6,7,8,9]);
        $this->watchImmutable($coll);
        $every2nd = $coll->nth(2, 1);
        $this->assertEquals([
            1 => 1,
            3 => 3,
            5 => 5,
            7 => 7,
            9 => 9
        ], $every2nd->toArray());
        $every3rd = $coll->nth(3, 2);
        $this->assertEquals([
            2 => 2,
            5 => 5,
            8 => 8
        ], $every3rd->toArray());
        $every4th = $coll->nth(4, 5);
        $this->assertEquals([
            5 => 5,
            9 => 9
        ], $every4th->toArray());
        $every5th = $coll->nth(5, 5);
        $this->assertEquals([
            5 => 5
        ], $every5th->toArray());
        $this->assertImmutable($coll);
    }

    public function testExceptReturnsCollectionWithAllButValuesAtSpecifiedIndices()
    {
        $coll = collect([
            'foo' => 'FOO',
            'bar' => 'BAR',
            'baz' => 'BAZ',
            'bin' => 'BIN'
        ]);
        $this->watchImmutable($coll);
        $exceptBazBin = $coll->except(['baz','bin']);
        $this->assertEquals(['foo' => 'FOO','bar' => 'BAR'], $exceptBazBin->toArray());
        $exceptFooBinColl = $coll->except(collect(['foo','bin']));
        $this->assertEquals(['bar' => 'BAR','baz' => 'BAZ'], $exceptFooBinColl->toArray());
        $this->assertImmutable($coll);
    }

    public function testFlipReturnsCollectionWithKeysValuesFlipped()
    {
        $coll = collect([
            'foo' => 'FOO',
            'bar' => 'BAR',
            'baz' => 'BAZ',
            'bin' => 'BIN'
        ]);
        $this->watchImmutable($coll);
        $this->assertEquals([
            'FOO' => 'foo',
            'BAR' => 'bar',
            'BAZ' => 'baz',
            'BIN' => 'bin'
        ], $coll->flip()->toArray());
        $this->assertImmutable($coll);
    }

    // @TODO I skipped a BUNCH of methods here...

    public function testSplitReturnsCollectionWithItemsSplitIntoNumGroups()
    {
        $coll = collect(range(0,20));
        $this->watchImmutable($coll);
        $this->assertEquals([
            [0,1,2,3,4],
            [5,6,7,8],
            [9,10,11,12],
            [13,14,15,16],
            [17,18,19,20]
        ], $coll->split(5)->toArray());
        $this->assertEquals([
            [0,1,2,3,4,5,6],
            [7,8,9,10,11,12,13],
            [14,15,16,17,18,19,20]
        ], $coll->split(3)->toArray());
        $this->assertEquals([
            [ 0, 1, 2, 3, 4, 5],
            [ 6, 7, 8, 9,10],
            [11,12,13,14,15],
            [16,17,18,19,20]
        ], $coll->split(4)->toArray());
        $this->assertEquals([
            [ 0, 1, 2],
            [ 3, 4, 5],
            [ 6, 7, 8],
            [ 9,10,11],
            [12,13,14],
            [15,16],
            [17,18],
            [19,20],
        ], $coll->split(8)->toArray());
        $this->assertImmutable($coll);
    }

    public function testZip()
    {
        $coll1 = collect(['broom','chair','table']);
        $coll2 = collect(['shoe','shirt','hat']);
        $coll3 = collect(['toothbrush','floss','flouride']);
        $this->assertEquals([
            ['broom','shoe','toothbrush'],
            ['chair','shirt','floss'],
            ['table','hat','flouride'],
        ], $coll1->zip($coll2->toArray(), $coll3->toArray())->toArray());

    }

    // END NEW TESTS

    // BEGIN OLD TESTS...

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
        $this->assertEquals('really cool guy', $coll->fold(function($item, $carry, $key, $iter) {
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

//    /**
//     * @expectedException \OutOfBoundsException
//     */
//    public function testIndexOfThrowsExceptionIfValueNotFoundAndThrowParamIsTrue()
//    {
//        $coll = new Collection(['foo','bar','baz', 'boo' => 'woo']);
//        $this->assertEquals(1, $coll->indexOf('bar'));
//        $this->assertEquals('boo', $coll->indexOf('woo'));
//        $this->assertNull($coll->indexOf('notinarray', true));
//    }

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
