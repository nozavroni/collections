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
}
