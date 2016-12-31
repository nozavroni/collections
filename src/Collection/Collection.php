<?php

/*
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/nozavroni/collections/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Collection;

use function Noz\is_traversable;

/**
 * Class Collection.
 *
 * A basic collection class, allowing only scalar/non-traversable items.
 *
 * @package Noz\Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Is correct input data type?
     *
     * @param mixed $data The data to assert correct type of
     *
     * @return bool
     */
    protected function isConsistentDataStructure($data)
    {
        return is_traversable($data);
    }
}
