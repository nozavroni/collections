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

/**
 * Class CharCollection
 *
 * Char Collection is like any other collection, only it accepts a string of characters and treats each character as an
 * item in the collection. It is in many ways like a string object, allowing you to extract substrings, add characters,
 * replace characters, etc.
 *
 * @package Noz\Collection
 */
class CharCollection extends AbstractCollection
{
    /**
     * Apply a callback to each item in collection.
     *
     * Applies a callback to each item in collection and returns a new collection
     * containing each iteration's return value.
     *
     * @param callable $callback The callback to apply
     *
     * @return AbstractCollection A new collection with callback return values
     */
    public function map(callable $callback)
    {
        return new self(implode('', array_map($callback, $this->data)));
    }

    /**
     * {@inheritdoc}
     */
    public function push(...$items)
    {
        $result = parent::push(...$items);

        return new self(implode('', $result->toArray()));
    }

    /**
     * {@inheritdoc}
     */
    public function unshift(...$items)
    {
        $result = parent::unshift(...$items);

        return new self(implode('', $result->toArray()));
    }

    /**
     * Convert input data to an array.
     *
     * Convert the input data to an array that can be worked with by a collection.
     *
     * @param mixed $data The input data
     *
     * @return array
     */
    protected function prepareData($data)
    {
        if (!is_string($data)) {
            $data = (string) $data;
        }

        return str_split($data);
    }

    /**
     * Is data consistent with this collection type?
     *
     * @param mixed $data The data to check
     *
     * @return bool
     */
    protected function isConsistentDataStructure($data)
    {
        return static::isCharacterSet($data);
    }
}
