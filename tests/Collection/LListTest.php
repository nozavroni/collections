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

use Noz\Collection\LList;

class LListTest extends AbstractCollectionTest
{
    public function testCreateLListReindexesNumerically()
    {
        $list = new LList($exp = [
            10 => 'goo',
            15,
            20 => 'foo',
            100,
            'yes' => false,
            '$' => 'money',
            '99',
            'nine'
        ]);
        $this->assertEquals(array_values($exp), $list->toArray());
    }
}
