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

use BadMethodCallException;
use Noz\Collection\AbstractCollection;
use Noz\Collection\Collection;
use Noz\Collection\MultiCollection;

use Noz\Collection\TabularCollection;
use function Noz\is_traversable;
use OutOfBoundsException;

class TabularCollectionTest extends AbstractCollectionTest
{
    protected $moretestdata = [
        [
            'numbers' => 10,
            'words' => 'some words'
        ],
        [
            'numbers' => 17,
            'words' => 'some words'
        ],
        [
            'numbers' => 18,
            'words' => 'some words'
        ],
        [
            'numbers' => 11,
            'words' => 'no words'
        ],
        [
            'numbers' => 20,
            'words' => 'all words'
        ],
        [
            'numbers' => 15,
            'words' => 'word up'
        ],
        [
            'numbers' => 15,
            'words' => 'word up'
        ],
        [
            'numbers' => 15,
            'words' => 'word down'
        ],
        [
            'numbers' => 20,
            'words' => 'all words'
        ],
        [
            'numbers' => 5,
            'words' => 'all words'
        ],
    ];

    public function testFactoryReturnsTabularCollection()
    {
        $coll = Collection::factory($this->testdata[TabularCollection::class]['user']);
        $this->assertInstanceOf(TabularCollection::class, $coll);
        $coll2 = Collection::factory($this->testdata[TabularCollection::class]['profile']);
        $this->assertInstanceOf(TabularCollection::class, $coll2);
    }

    public function testMapTabularCollection()
    {
        $coll = Collection::factory($this->testdata[TabularCollection::class]['user']);
        $func = function($row) {
            $this->assertInstanceOf(AbstractCollection::class, $row);
            return $row->get('email');
        };
        $newcoll = $coll->map($func->bindTo($this));
        $this->assertEquals([
            'ohauck@bahringer.info',
            'larry.emard@pacocha.com',
            'jaylin.mueller@yahoo.com',
            'gfriesen@hotmail.com',
            'verla.ohara@dibbert.com'
        ], $newcoll->toArray());
    }

    public function testContainsWorksWithTabular()
    {
        $coll = Collection::factory($this->testdata[TabularCollection::class]['user']);
        $this->assertTrue($coll->contains('gfriesen@hotmail.com'));
        $this->assertTrue($coll->contains('gfriesen@hotmail.com', 'email'));
        $this->assertFalse($coll->contains('gfriesen@hotmail.com', 'role'));
        $this->assertTrue($coll->contains('gfriesen@hotmail.com', ['id','email','created']));
    }

    public function testContainsWithCallbackWorksWithTabular()
    {
        $coll = Collection::factory($this->testdata[TabularCollection::class]['user']);
        $this->assertTrue($coll->contains(function($val) {
            return $val['is_active'];
        }));
        $this->assertTrue($coll->contains(function($val) {
            return !$val['is_active'];
        }));
        $this->assertTrue($coll->contains(function($val) {
            return is_array($val) && array_key_exists('email', $val);
        }));
        $this->assertFalse($coll->contains(function($val) {
            return is_array($val) && array_key_exists('username', $val);
        }));
    }

    public function testCollectionHasRow()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $this->assertTrue($coll->hasRow(0));
        $this->assertTrue($coll->hasRow(1));
        $this->assertTrue($coll->hasRow(2));
        $this->assertTrue($coll->hasRow(3));
        $this->assertTrue($coll->hasRow(4));
        $this->assertFalse($coll->hasRow(5));
    }

    public function testCollectionGetRow()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $firstrow = $coll->getRow(0);
        unset($firstrow['created']);
        unset($firstrow['modified']);
        $this->assertEquals([
            'id' => 1,
            'profile_id' => 126,
            'email' => 'ohauck@bahringer.info',
            'password' => '60a2409dea624661573516a31e3a1ea412076237',
            'role' => 'moderator',
            'is_active' => false
        ], $firstrow->toArray());
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testCollectionGetRowThrowsExceptionForMissingRow()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $firstrow = $coll->getRow(10);
    }

    public function testHasColumn()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $this->assertTrue($coll->hasColumn('email'));
        $this->assertFalse($coll->hasColumn('foobar'));
    }

    public function testGetColumn()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $this->assertInstanceOf(AbstractCollection::class, $coll->getColumn('email'));
        $this->assertEquals([1,2,3,4,5], $coll->getColumn('id')->toArray());
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetColumnThrowsExceptionForMissingColumn()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $coll->getColumn(100);
    }

    public function testGetColumnReturnsFalseForMissingColumnIfThrowParamIsFalse()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $this->assertFalse($coll->getColumn(100, false));
    }

    public function testAverageColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(14.6, $coll->average('numbers'));
    }

    public function testSumColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(146, $coll->sum('numbers'));
    }

    public function testModeColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(15, $coll->mode('numbers'));
    }

    public function testMedianColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(15, $coll->median('numbers'));
    }

    public function testMaxColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(20, $coll->max('numbers'));
    }

    public function testMinColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals(5, $coll->min('numbers'));
    }

    public function testCountsColumn()
    {
        $coll = new TabularCollection($this->moretestdata);
        $this->assertEquals([
            10 => 1,
            17 => 1,
            18 => 1,
            11 => 1,
            20 => 2,
            15 => 3,
            5 => 1
        ], $coll->counts('numbers')->toArray());
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Method does not exist: Noz\Collection\TabularCollection::nonExistantMethod()
     */
    public function testTabularCollectionThrowsBadMethodCallExceptionOnBadMethodCall()
    {
        $coll = new TabularCollection([
            ['id' => 2, 'name' => 'Luke', 'email' => 'luke.visinoni@gmail.com'],
            ['id' => 3, 'name' => 'Dave', 'email' => 'dave.mason@gmail.com'],
            ['id' => 5, 'name' => 'Joe', 'email' => 'joe.rogan@gmail.com'],
        ]);
        $coll->nonExistantMethod('foo','bar');
    }

    public function testWalkForTabularCollection()
    {
        $coll = new TabularCollection($this->testdata[TabularCollection::class]['user']);
        $ret = $coll->walk(function($row, $index) {
            $this->assertInstanceOf(AbstractCollection::class, $row);
            $this->assertInternalType('int', $index);
        });
        $this->assertInstanceOf(TabularCollection::class, $ret);
    }
}