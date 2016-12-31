<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Contracts;
use OutOfBoundsException;

/**
 * Interface CollectionInterface.
 *
 * This interface is meant to be used in place of AbstractCollection for return types, type hints, etc.
 * It should only contain the bare minimum methods one would expect to be available on a collection class.
 *
 * @package Noz\Contracts
 */
interface CollectionInterface
{
    /**
     * Sort this collection (in place).
     *
     * @param string|null $alg Sorting algorithm
     *
     * @return $this
     */
    public function sort($alg = null);

    /**
     * Determine if there is an item at specified index.
     *
     * @param string|int $index The index to check
     * @return bool
     */
    public function has($index);

    /**
     * Get item at given index.
     *
     * Return the item at specified index or return a default if no item found.
     *
     * @param string|int $index   Get item at this index
     * @param mixed      $default Return this value if no item found
     *
     * @return mixed
     */
    public function get($index, $default = null);

    /**
     * Get item at index or throw exception.
     *
     * Return item at specified index. If it doesn't exist, throw an exception.
     *
     * @param string|int $index The index
     *
     * @return mixed
     *
     * @throws OutOfBoundsException
     */
    public function retrieve($index);

    /**
     * Set value at specified index.
     *
     * Replaces any item that currently exists at specified index.
     *
     * @param string|int $index Set value at this index
     * @param mixed      $value Set this value at index
     *
     * @return mixed
     */
    public function set($index, $value);

    /**
     * Add value at index.
     *
     * Set value at provided index if it doesn't already exist.
     *
     * @param string|int $index Add value at this index
     * @param mixed      $value Add this value at index
     *
     * @return mixed
     */
    public function add($index, $value);

    /**
     * Delete value from collection at given index.
     *
     * @param string|int $index Delete item at this index
     *
     * @return mixed
     */
    public function delete($index);

    /**
     * Return index of first occurrence of value.
     *
     * @param string|int $value Value to look up
     *
     * @return mixed
     */
    public function indexOf($value);

    /**
     * Return this collection's keys.
     *
     * @return CollectionInterface
     */
    public function keys();

    /**
     * Return this collection's values, re-indexed numerically.
     *
     * @return CollectionInterface
     */
    public function values();

    /**
     * Merge traversable data into collection.
     *
     * Returns a new collection with input merged into this collection's data.
     *
     * @param Traversable $data Traversable data to merge into collection
     *
     * @return CollectionInterface
     */
    public function merge($data);

    /**
     * Check that collection contains a value.
     *
     * You may optionally pass in a callable to provide your own equality test.
     *
     * @param mixed|callable  $value The value to search for
     * @param int|string|null $index The index to search for
     *
     * @return mixed
     */
    public function contains($value, $index = null);

    /**
     * Take item at index.
     *
     * Remove an item from this collection at given index and return it.
     *
     * @param int|string $index Take item at this index
     *
     * @return mixed
     */
    public function take($index);

    /**
     * Pop item.
     *
     * Pop an item off the end of the collection and return it.
     *
     * @return mixed
     */
    public function pop();

    /**
     * Shift item.
     *
     * Shift an item off the beginning of the collection and return it.
     *
     * @return mixed
     */
    public function shift();

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
     * Split collection into chunks.
     *
     * Returns a new collection with this collection's data split into chunks of a given size.
     *
     * @param int $size Number of items per chunk
     *
     * @return CollectionInterface
     */
    public function chunk($size);

    /**
     * Combine traversable with this collection.
     *
     * Combines supplied data with this collection using incoming array as values and this collection as keys.
     *
     * @param array|CollectionInterface $values Traversable data structure to combine with
     *
     * @return CollectionInterface
     */
    public function combine($values);

    /**
     * Get difference.
     *
     * Returns a new collection containing all items in this collection that are not in $data.
     *
     * @param array|CollectionInterface $data Data to diff
     *
     * @return CollectionInterface
     */
    public function diff($data);

    /**
     * Get difference by key.
     *
     * Returns a new collection containing all items in this collection whose indexes do not appear in $data.
     *
     * @param array|CollectionInterface $data Data to diff
     *
     * @return CollectionInterface
     */
    public function diffKeys($data);

    /**
     * Get every n-th item.
     *
     * Return collection containing every n-th item in this collection.
     *
     * @param int  $nth    The number of items between cycles
     * @param null $offset An optional offset to begin from
     *
     * @return mixed
     */
    public function every($nth, $offset = null);

    /**
     * Except keys.
     *
     * Returns a collection containing every item in this collection except items at specified indexes.
     *
     * @param array|CollectionInterface $indexes Indexes to except
     *
     * @return CollectionInterface
     */
    public function except($indexes);

    /**
     * Flip keys with values.
     *
     * Returns new collection with keys as values and values as keys.
     *
     * @return CollectionInterface
     */
    public function flip();

    /**
     * Intersect collection.
     *
     * Returns a new collection containing only items that occur in both this collection and input data.
     *
     * @param array|CollectionInterface $data The intersect data
     *
     * @return CollectionInterface
     */
    public function intersect($data);

    /**
     * Intersect collection via keys.
     *
     * Exactly the same as intersect, only using keys rather than values.
     *
     * @param array|CollectionInterface $data The intersect data
     *
     * @return CollectionInterface
     */
    public function intersectKeys($data);

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
     * Get values at specified indices.
     *
     * Returns a new collection containing only elements found at specified indices.
     *
     * @param array<int|string>|CollectionInterface $indices An array or collection of indices
     *
     * @return CollectionInterface
     */
    public function only($indices);

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
     * Get random item(s).
     *
     * Returns random item(s) from the collection.
     *
     * @param int $num Number of items to return
     *
     * @return mixed|CollectionInterface
     */
    public function random($num);

    /**
     * Like indexOf, but it will return an array of keys if value appears in collection more than once. Always returns a collection.
     *
     * Also accepts a callback. Returns every index whose value passes truth test.
     *
     * @param mixed|callable $value Either the value to look up or a callback
     *
     * @return CollectionInterface
     */
    public function indicesOf($value);

    /**
     * Shuffle this collection.
     *
     * Reorders underlying data array at random.
     *
     * @return $this
     */
    public function shuffle();

    /**
     * Get a slice of this collection.
     *
     * Returns a new collection containing item(s) in this collection starting from $offset. A length may optionally
     * be provided to limit the size of the slice.
     *
     * @param int      $offset The offset to start slicing from
     * @param int|null $length The number of items to include
     *
     * @return CollectionInterface
     */
    public function slice($offset, $length = null);

    /**
     * Remove a slice from this collection and return it.
     *
     * Behaves exactly the same way as slice(), only this method removes the slice from this collection before returning it.
     *
     * @param int      $offset The offset to start slicing from
     * @param int|null $length The number of items to include
     *
     * @return CollectionInterface
     */
    public function splice($offset, $length = null);

    /**
     * Split collection into pieces.
     *
     * Splits this collection into specified number of groups.
     *
     * @param int $num The number of groups
     *
     * @return CollectionInterface
     */
    public function split($num);

    /**
     * Transform collection using callback.
     * This method behaves much like map(), only it modifies this collection in place rather than returning a new one.
     *
     * @param callable $callback The transform callback
     *
     * @return $this
     */
    public function transform(callable $callback);

    /**
     * Union of collections/array.
     *
     * Return union of this collection and provided data structure, preferring values in this collection over provided
     *
     * @param array|CollectionInterface $data The data to add
     *
     * @return CollectionInterface
     */
    public function union($data);

    /**
     * Zip data into collection.
     *
     * Returns a new collection, each item a combination of this collection's values with the provided data structure(s).
     * So, if this collection contains "foo","bar","baz" and it is passed an array containing 1,2,3, another array
     * containing "a","b","c", and a collection containing simply, 'w00t!', you will get a collection back
     * containing [["foo",1,"a","w00t!"],["bar",2,"b"],["baz",3,"c"]].
     *
     * @param array|CollectionInterface ...$data The data to zip into this collection
     *
     * @return CollectionInterface
     */
    public function zip(...$data);

    /**
     * Pad collection.
     *
     * Return new collection, padded to specified length with specified value.
     *
     * @param int        $size The size collection should be padded to
     * @param mixed|null $with The value to pad collection with
     *
     * @return CollectionInterface
     */
    public function pad($size, $with = null);

    /**
     * Map collection.
     *
     * Each item in the collection is passed through a callable and then this mutated collection copy is returned.
     *
     * @param callable $callback The callback to mutate with
     *
     * @return CollectionInterface
     */
    public function map(callable $callback);

    /**
     * Walk through this collection's items.
     *
     * Iterates over each item in the collection, passing value, key to a callback function at each iteration.
     * It is ill-advised to attempt to change items within the collection using this method.
     *
     * @param callable $callback The callback function
     *
     * @return $this
     */
    public function walk(callable $callback);

    /**
     * Iterate over each item in collection.
     *
     * Behaves much like walk, except that returning false from the callback function will stop iteration.
     *
     * @param callable $callback The callback function
     *
     * @return $this
     */
    public function each(callable $callback);

    /**
     * Fold collection right.
     *
     * Reduces this collection to one value by passing value, key, and the return value from the previous iteration
     * until only one value remains. Iteration begins from the first item in the collection and moves down.
     *
     * @todo Change order of callback args to $carry, $val, $key, $iter
     *
     * @param callable $callback The callback function
     * @param          $initial  The initial "carry" value
     *
     * @return mixed
     */
    public function foldRight(callable $callback, $initial = null);

    /**
     * Fold collection left.
     *
     * Reduces this collection to one value by passing value, key, and the return value from the previous iteration
     * until only one value remains. Iteration begins from the last item in the collection and moves up.

     * @param callable $callback The callback function
     * @param          $initial  The initial "carry" value
     *
     * @return mixed
     */
    public function foldLeft(callable $callback, $initial = null);

    /**
     * Filter collection.
     *
     * Filters this collection using a callback function. The callback is passed the item value, followed by its index,
     * followed by a numeric iteration value. The resulting collection will contain
     *
     * @todo Would it be useful to pass an instance of the collection as the third parameter to the callback? Or maybe
     *       I should just typehint Closure instead of callable and then I can simply call $closure->bindTo($this) so
     *       that $this is available within the callback. This is something to think about for ALL callback arguments.
     *
     * @param callable $callback The callback function
     *
     * @return CollectionInterface
     */
    public function filter(callable $callback);

    /**
     * Filter collection.
     *
     * Filters this collection using a callback function. The callback is passed the item value, followed by its index,
     * followed by a numeric iteration value.
     *
     * @param callable $callback The callback function
     *
     * @return CollectionInterface
     */
    public function exclude(callable $callback);

    /**
     * Get first item.
     *
     * If a callback argument is supplied, the first item in the collection that causes the callback to return true
     * will be returned. If no item in the collection returns true from the callback, a default may be returned.
     * If no callback is supplied at all, the first item in the collection will be returned.
     *
     * @param callable $callback The callback function
     * @param mixed    $default  The default value
     *
     * @return mixed
     */
    public function first(callable $callback = null, $default = null);

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
    public function all(callable $callback = null);

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
     * Get last item.
     *
     * If a callback argument is supplied, the last item in the collection that causes the callback to return true
     * will be returned. If no item in the collection returns true from the callback, a default may be returned.
     * If no callback is supplied at all, the last item in the collection will be returned.
     *
     * @param callable $callback The callback function
     * @param mixed    $default  The default value
     *
     * @return mixed
     */
    public function last(callable $callback = null, $default = null);

    /**
     * Reverse the collection order.
     *
     * Returns a copy of this collection, only its items are in reverse order.
     *
     * @return CollectionInterface
     */
    public function reverse();

    /**
     * Get unique items.
     *
     * Returns a new collection, having removed all duplicate values from the original collection.
     *
     * @return CollectionInterface
     */
    public function unique();

    /**
     * Convert to array.
     *
     * Returns collection as an array.
     *
     * @return array
     */
    public function toArray();

    /**
     * Does collection have value at offset?
     *
     * Regardless of this collection's keys, this method will return true if an element exists at specified offset.
     *
     * @param int $offset The numerical offset
     *
     * @return bool
     */
    public function hasOffset($offset);

    /**
     * Return value at numerical offset.
     *
     * Regardless of this collection's keys, this method will return the item found at the specified numerical offset.
     *
     * @param int $offset The numerical offset
     *
     * @return mixed
     */
    public function getOffset($offset);

    /**
     * Return key at numerical offset.
     *
     * Regardless of this collection's keys, this method will return the key found at the specified numerical offset.
     *
     * @param int $offset The numerical offset
     *
     * @return int|string
     */
    public function getOffsetKey($offset);

    /**
     * Get count.
     *
     * If a callback is supplied, this method will return the number of items that cause the callback to return true.
     * Otherwise, all items in the collection will be counted.
     *
     * @todo If having the callback param causes collection not to be able to implement Countable,
     *       just get rid of it. You can do the same thing with filter($callback)->Count()
     *
     * @param callable $callback The (optional) callback function
     * @return int
     */
    public function count(callable $callback = null);
}