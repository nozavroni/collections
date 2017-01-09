<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2017 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Contracts\Structure;

interface Collectable
{
    /**
     * Prepend item to collection.
     *
     * Prepend an item to this collection (in place).
     *
     * @param mixed $item Item to prepend to collection
     *
     * @return $this
     */
    public function prepend($item);

    /**
     * Append item to collection.
     *
     * Append an item to this collection (in place).
     *
     * @param mixed $item Item to append to collection
     *
     * @return $this
     */
    public function append($item);

    /**
     * Check that collection contains a value.
     *
     * You may optionally pass in a callable to provide your own equality test.
     *
     * @param mixed|callable  $value The value to search for
     *
     * @return mixed
     */
    public function contains($value);

    /**
     * Is collection empty?
     * You may optionally pass in a callback which will determine if each of the items within the collection are empty.
     * If all items in the collection are empty according to this callback, this method will return true.
     *
     * @param callable $callback The callback
     *
     * @return bool
     */
    public function isEmpty(callable $callback = null);

    /**
     * Pipe collection through callback.
     *
     * Passes entire collection to provided callback and returns the result.
     *
     * @param callable $callback
     *
     * @return mixed
     */
    public function pipe(callable $callback);

    /**
     * Does every item return true?
     *
     * If callback is provided, this method will return true if all items in collection cause callback to return true.
     * Otherwise, it will return true if all items in the collection have a truthy value.
     *
     * @param callable|null $callback The callback
     *
     * @return bool
     */
    public function every(callable $callback = null);

    /**
     * Does every item return false?
     *
     * This method is the exact opposite of "all".
     *
     * @param callable|null $callback The callback
     *
     * @return bool
     */
    public function none(callable $callback = null);

    /**
     * Get total count.
     *
     * Returns number of items in the collection.
     *
     * @return int
     */
    public function count();

    /**
     * Get collection as array.
     *
     * @return array
     */
    public function toArray();

    public function serialize();
    public function unserialize($serialized);
}
