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
     * {@inheritdoc}
     */
    protected function prepareData($data)
    {
        return $data;
    }

    /**
     * Is correct input data type?
     *
     * @param mixed $data The data to assert correct type of
     *
     * @return bool
     */
    protected function isConsistentDataStructure($data)
    {
        // this collection may only contain scalar or null values
        if (!is_traversable($data)) {
            return false;
        }
        foreach ($data as $key => $val) {
            if (is_traversable($val)) {
                return false;
            }
        }

        return true;
    }
}
