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

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use Iterator;
use Noz\Contracts\ArrayableInterface;
use Noz\Contracts\CollectionInterface;
use OutOfBoundsException;

use Noz\Traits\IsArrayable;

use function
    Noz\is_traversable,
    Noz\typeof,
    Noz\collect;
use Traversable;

/**
 * Class Collection.
 *
 * This is the abstract class that all other collection classes are based on.
 * Although it's possible to use a completely custom Collection class by simply
 * implementing the "Collectable" interface, extending this class gives you a
 * whole slew of convenient methods for free.
 *
 * @package Noz\Collection
 *
 * @author Luke Visinoni <luke.visinoni@gmail.com>
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 *
 * @todo Implement Serializable, other Interfaces
 */
class Collection implements
    CollectionInterface,
    ArrayableInterface,
    ArrayAccess,
    Countable,
    Iterator
{
    use IsArrayable;

    /**
     * @var array The collection of data this object represents
     */
    protected $data = [];

    /**
     * @var bool True unless we have advanced past the end of the data array
     */
    protected $isValid = true;

    /**
     * AbstractCollection constructor.
     *
     * @param mixed $data The data to wrap
     */
    public function __construct($data = [])
    {
        $this->setData($data);
    }

    /**
     * Set collection data.
     *
     * Sets the collection data.
     *
     * @param array|Traversable $data The data to wrap
     *
     * @return $this
     */
    protected function setData($data)
    {
        if (is_null($data)) {
            $data = [];
        }
        $this->assertIsTraversable($data);
        foreach ($data as $index => $value) {
            $this->set($index, $value);
        }
        reset($this->data);

        return $this;
    }

    /**
     * Assert input data is of the correct structure.
     *
     * @param mixed $data Data to check
     *
     * @throws InvalidArgumentException If invalid data structure
     */
    protected function assertIsTraversable($data)
    {
        // @todo this is not the right message usually... fix it.
        if (!is_traversable($data)) {
            throw new InvalidArgumentException(__CLASS__ . ' expected traversable data, got: ' . gettype($data));
        }
    }

    /**
     * Invoke object.
     *
     * Magic "invoke" method. Called when object is invoked as if it were a function.
     *
     * @param mixed $val   The value (depends on other param value)
     * @param mixed $index The index (depends on other param value)
     *
     * @return array|CollectionInterface (Depends on parameter values)
     */
    public function __invoke($val = null, $index = null)
    {
        if (is_null($val)) {
            if (is_null($index)) {
                return $this->toArray();
            }

            return $this->delete($index);
        }
        if (is_null($index)) {
            // @todo cast $val to array?
                return $this->merge($val);
        }

        return $this->set($val, $index);
    }

    /**
     * Whether an offset exists.
     *
     * @param mixed $offset An offset to check for.
     *
     * @return bool true on success or false on failure.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        return $this->retrieve($offset);
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @param mixed $offset The offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * @inheritDoc
     */
    public function count(callable $callback = null)
    {
        if (!is_null($callback)) {
            return $this->filter($callback)->count();
        }
        return count($this->data);
    }

    /**
     * Return the current element.
     *
     * Returns the current element in the collection. The internal array pointer
     * of the data array wrapped by the collection should not be advanced by this
     * method. No side effects. Return current element only.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Return the current key.
     *
     * Returns the current key in the collection. No side effects.
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Advance the internal pointer forward.
     *
     * Although this method will return the current value after advancing the
     * pointer, you should not expect it to. The interface does not require it
     * to return any value at all.
     *
     * @return mixed
     */
    public function next()
    {
        $next = next($this->data);
        $key  = key($this->data);
        if (isset($key)) {
            return $next;
        }
        $this->isValid = false;
    }

    /**
     * Rewind the internal pointer.
     *
     * Return the internal pointer to the first element in the collection. Again,
     * this method is not required to return anything by its interface, so you
     * should not count on a return value.
     *
     * @return mixed
     */
    public function rewind()
    {
        $this->isValid = !empty($this->data);

        return reset($this->data);
    }

    /**
     * Is internal pointer in a valid position?
     *
     * If the internal pointer is advanced beyond the end of the collection, this method will return false.
     *
     * @return bool True if internal pointer isn't past the end
     */
    public function valid()
    {
        return $this->isValid;
    }

    public function sort($alg = null)
    {
        if (is_null($alg)) {
            $alg = 'natcasesort';
        }
        $alg($this->data);

        return collect($this->data);
    }

    /**
     * Does this collection have a value at given index?
     *
     * @param mixed $index The index to check
     *
     * @return bool
     */
    public function has($index)
    {
        return array_key_exists($index, $this->data);
    }

    /**
     * Set a value at a given index.
     *
     * Setter for this collection. Allows setting a value at a given index.
     *
     * @param mixed $index The index to set a value at
     * @param mixed $val   The value to set $index to
     *
     * @return $this
     */
    public function set($index, $val)
    {
        $this->data[$index] = $val;

        return $this;
    }

    /**
     * Unset a value at a given index.
     *
     * Unset (delete) value at the given index.
     *
     * @param mixed $index The index to unset
     * @param bool  $throw True if you want an exception to be thrown if no data found at $index
     *
     * @throws OutOfBoundsException If $throw is true and $index isn't found
     *
     * @return $this
     */
    public function delete($index, $throw = false)
    {
        if (isset($this->data[$index])) {
            unset($this->data[$index]);
        } else {
            if ($throw) {
                throw new OutOfBoundsException('No value found at given index: ' . $index);
            }
        }

        return $this;
    }

    /**
     * Get index of a value.
     *
     * Given a value, this method will return the index of the first occurrence of that value.
     *
     * @param mixed $value Value to get the index of
     * @param bool  $throw Whether to throw an exception if value isn't found
     *
     * @return int|null|string
     */
    public function indexOf($value, $throw = true)
    {
        $return = null;
        $this->first(function($val, $key) use (&$return, $value) {
            if ($val == $value) {
                $return = $key;
                return true;
            }
        });
        if ($throw && is_null($return)) {
            throw new OutOfBoundsException(sprintf(
                'Value "%s" not found in collection.',
                $value
            ));
        }
        return $return;
    }

    /**
     * Get this collection's keys as a collection.
     *
     * @return CollectionInterface Containing this collection's keys
     */
    public function keys()
    {
        return collect(array_keys($this->data));
    }

    /**
     * Get this collection's values as a collection.
     *
     * This method returns this collection's values but completely re-indexed (numerically).
     *
     * @return CollectionInterface Containing this collection's values
     */
    public function values()
    {
        return collect(array_values($this->data));
    }

    /**
     * Merge data into collection.
     *
     * Merges input data into this collection. Input can be an array or another collection.
     * Returns a NEW collection object.
     *
     * @param Traversable|array $data The data to merge with this collection
     *
     * @return CollectionInterface A new collection with $data merged in
     */
    public function merge($data)
    {
        $this->assertIsTraversable($data);
        $coll = collect($this->data);
        foreach ($data as $index => $value) {
            $coll->set($index, $value);
        }

        return $coll;
    }

    /**
     * Determine if this collection contains a value.
     *
     * Allows you to pass in a value or a callback function and optionally an index,
     * and tells you whether or not this collection contains that value.
     * If the $index param is specified, only that index will be looked under.
     *
     * @param mixed|callable $value The value to check for
     * @param mixed          $index The (optional) index to look under
     *
     * @return bool True if this collection contains $value
     *
     * @todo Maybe add $identical param for identical comparison (===)
     * @todo Allow negative offset for second param
     */
    public function contains($value, $index = null)
    {
        return (bool) $this->first(function ($val, $key) use ($value, $index) {
            if (is_callable($value)) {
                $found = $value($val, $key);
            } else {
                $found = ($value == $val);
            }
            if ($found) {
                if (is_null($index)) {
                    return true;
                }
                if (is_array($index)) {
                    return in_array($key, $index);
                }

                return $key == $index;
            }

            return false;
        });
    }

    /**
     * Pop an element off the end of this collection.
     *
     * @return mixed The last item in this collectio n
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    /**
     * Shift an element off the beginning of this collection.
     *
     * @return mixed The first item in this collection
     */
    public function shift()
    {
        return array_shift($this->data);
    }

    /**
     * Pad this collection to a certain size.
     *
     * Returns a new collection, padded to the given size, with the given value.
     *
     * @param int   $size The number of items that should be in the collection
     * @param mixed $with The value to pad the collection with
     *
     * @return CollectionInterface A new collection padded to specified length
     */
    public function pad($size, $with = null)
    {
        return collect(array_pad($this->data, $size, $with));
    }

    /**
     * Apply a callback to each item in collection.
     *
     * Applies a callback to each item in collection and returns a new collection
     * containing each iteration's return value.
     *
     * @param callable $callback The callback to apply
     *
     * @return CollectionInterface A new collection with callback return values
     */
    public function map(callable $callback)
    {
        $iter = 0;
        $transform = [];
        foreach ($this as $key => $val) {
            $transform[$key] = $callback($val, $key, $iter++);
        }
        return collect($transform);
    }

    /**
     * Apply a callback to each item in collection.

     * Applies a callback to each item in collection. The callback should return
     * false to filter any item from the collection.

     * @param callable $callback     The callback function
     * @param null     $extraContext Extra context to pass as third param in callback

     * @return $this
     * @todo Is this method really useful? I don't think it is... Probably should just get rid of it because map() and
     *       each() can do everything walk() can do and more...
     */
    public function walk(callable $callback, $extraContext = null)
    {
        array_walk($this->data, $callback, $extraContext);

        return $this;
    }

    /**
     * Iterate over each item in the collection, calling $callback on it. Return false to stop iterating.
     *
     * @param callable    $callback A callback to use
     *
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this as $key => $val) {
            if (!$callback($val, $key)) {
                break;
            }
        }

        return $this;
    }

    /**
     * Filter the collection.
     *
     * Using a callback function, this method will filter out unwanted values, returning
     * a new collection containing only the values that weren't filtered.
     *
     * @param callable $callback The callback function used to filter
     *
     * @return CollectionInterface A new collection with only values that weren't filtered
     */
    public function filter(callable $callback)
    {
        $iter = 0;
        $filtered = [];
        foreach ($this as $key => $val) {
            if ($callback($val, $key, $iter++)) {
                $filtered[$key] = $val;
            }
        }
        return collect($filtered);
    }

    /**
     * Filter the collection.
     *
     * Using a callback function, this method will filter out unwanted values, returning
     * a new collection containing only the values that weren't filtered.
     *
     * @param callable $callback The callback function used to filter
     *
     * @return CollectionInterface A new collection with only values that weren't filtered
     */
    public function exclude(callable $callback)
    {
        $iter = 0;
        $filtered = [];
        foreach ($this as $key => $val) {
            if (!$callback($val, $key, $iter++)) {
                $filtered[$key] = $val;
            }
        }
        return collect($filtered);
    }

    /**
     * Return the first item that meets given criteria.
     *
     * Using a callback function, this method will return the first item in the collection
     * that causes the callback function to return true.
     *
     * @param callable|null $callback The callback function
     * @param mixed|null    $default  The default return value
     *
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return $this->getOffset(0);
        }

        foreach ($this as $index => $value) {
            if ($callback($value, $index)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Return the last item that meets given criteria.
     *
     * Using a callback function, this method will return the last item in the collection
     * that causes the callback function to return true.
     *
     * @param callable|null $callback The callback function
     * @param mixed|null    $default  The default return value
     *
     * @return mixed
     */
    public function last(callable $callback = null, $default = null)
    {
        $reverse = $this->reverse();
        if (is_null($callback)) {
            return $reverse->getOffset(0);
        }
        return $reverse->first($callback);
    }

    /**
     * Returns collection in reverse order.
     *
     * @return CollectionInterface This collection in reverse order.
     */
    public function reverse()
    {
        return collect(array_reverse($this->data, true));
    }

    /**
     * Get unique items.
     *
     * Returns a collection of all the unique items in this collection.
     *
     * @return CollectionInterface This collection with duplicate items removed
     */
    public function unique()
    {
        return collect(array_unique($this->data));
    }

    /**
     * Collection factory method.
     *
     * This method will analyze input data and determine the most appropriate Collection
     * class to use. It will then instantiate said Collection class with the given
     * data and return it.
     *
     * @param mixed $data The data to wrap
     *
     * @return CollectionInterface A collection containing $data
     */
    public static function factory($data = null)
    {
        return new Collection($data);
    }

    /**
     * Determine if structure contains all numeric values.
     *
     * @param mixed $data The input data
     *
     * @return bool
     */
    public static function isNumeric($data)
    {
        if (!is_traversable($data) || empty($data)) {
            return false;
        }
        foreach ($data as $val) {
            if (!is_numeric($val)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasOffset($offset)
    {
        try {
            $this->getOffsetKey($offset);
            return true;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getOffsetKey($offset)
    {
        if (!is_null($key = $this->foldRight(function($val, $carry, $key, $iter) use ($offset) {
            return ($iter === $offset) ? $key : $carry;
        }))) {
            return $key;
        }
        throw new OutOfBoundsException("Offset does not exist: $offset");
    }

    /**
     * @inheritdoc
     */
    public function getOffset($offset)
    {
        return $this->retrieve($this->getOffsetKey($offset));
    }

    /**
     * @param int $offset The numerical offset
     *
     * @throws OutOfBoundsException if no pair at position
     *
     * @return array
     */
    public function getOffsetPair($offset)
    {
        $pairs = $this->pairs();

        return $pairs[$this->getOffsetKey($offset)];
    }

    /**
     * Get each key/value as an array pair.
     *
     * Returns a collection of arrays where each item in the collection is [key,value]
     *
     * @return CollectionInterface
     */
    public function pairs()
    {
        return collect(array_map(
            function ($key, $val) {
                return [$key, $val];
            },
            array_keys($this->data),
            array_values($this->data)
        ));
    }

    /**
     * Get duplicate values.
     *
     * Returns a collection of arrays where the key is the duplicate value
     * and the value is an array of keys from the original collection.
     *
     * @return CollectionInterface A new collection with duplicate values.
     */
    public function duplicates()
    {
        $dups = [];
        $this->walk(function ($val, $key) use (&$dups) {
            $dups[$val][] = $key;
        });

        return collect($dups)->filter(function ($val) {
            return count($val) > 1;
        });
    }

    // END Iterator methods

    /**
     * Counts how many times each value occurs in a collection.
     *
     * Returns a new collection with values as keys and how many times that
     * value appears in the collection. Works best with scalar values but will
     * attempt to work on collections of objects as well.
     *
     * @return CollectionInterface
     *
     * @todo Right now, collections of arrays or objects are supported via the
     * __toString() or spl_object_hash()
     * @todo NumericCollection::counts() does the same thing...
     */
    public function frequency()
    {
        $frequency = [];
        foreach ($this as $key => $val) {
            if (!is_scalar($val)) {
                if (!is_object($val)) {
                    $val = new ArrayIterator($val);
                }

                if (method_exists($val, '__toString')) {
                    $val = (string) $val;
                } else {
                    $val = spl_object_hash($val);
                }
            }
            if (!isset($frequency[$val])) {
                $frequency[$val] = 0;
            }
            $frequency[$val]++;
        }

        return collect($frequency);
    }

    /**
     * @inheritDoc
     */
    public function add($index, $value)
    {
        if (!$this->has($index)) {
            return $this->set($index, $value);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function get($index, $default = null)
    {
        try {
            return $this->retrieve($index);
        } catch (OutOfBoundsException $e) {
            return $default;
        }
    }

    /**
     * @inheritdoc
     */
    public function retrieve($index)
    {
        if (!$this->has($index)) {
            throw new OutOfBoundsException(__CLASS__ . ' could not retrieve value at index ' . $index);
        }
        return $this->data[$index];
    }

    /**
     * @inheritDoc
     */
    public function take($index)
    {
        try {
            $item = $this->retrieve($index);
            $this->data = $this->except([$index])->toArray();
        } catch (OutOfBoundsException $e) {
            // do nothing...
            $item = null;
        }
        return $item;
    }

    /**
     * @inheritDoc
     */
    public function prepend($item)
    {
        array_unshift($this->data, $item);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function append($item)
    {
        array_push($this->data, $item);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function chunk($size)
    {
        $data = [];
        $group = $iter = 0;
        foreach ($this as $key => $val) {
            $data[$group][$key] = $val;
            if ($iter++ > $size) {
                $group++;
                $iter = 0;
            }
        }
        return collect($data);
    }

    /**
     * @inheritDoc
     */
    public function combine($values)
    {
        if (!is_traversable($values)) {
            throw new InvalidArgumentException(sprintf(
                'Expecting traversable data for %s but got %s.',
                __METHOD__,
                typeof($values)
            ));
        }
        return collect(
            array_combine(
                $this->keys()->toArray(),
                collect($values)->values()->toArray()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function diff($data)
    {
        return collect(
            array_diff(
                $this->toArray(),
                collect($data)->toArray()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function diffKeys($data)
    {
        return collect(
            array_diff_key(
                $this->toArray(),
                collect($data)->toArray()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function every($nth, $offset = null)
    {
        return $this->slice($offset)->filter(function($val, $key, $iter) use ($nth) {
            return $iter % $nth == 0;
        });
    }

    /**
     * @inheritDoc
     */
    public function except($indexes)
    {
        return $this->diffKeys(collect($indexes)->flip());
    }

    /**
     * @inheritDoc
     */
    public function flip()
    {
        return collect(array_flip($this->data));
    }

    /**
     * @inheritDoc
     */
    public function intersect($data)
    {
        return collect(
            array_intersect(
                $this->toArray(),
                collect($data)->toArray()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function intersectKeys($data)
    {
        return collect(
            array_intersect_key(
                $this->toArray(),
                collect($data)->toArray()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(callable $callback = null)
    {
        if (!is_null($callback)) {
            return $this->all($callback);
        }
        return empty($this->data);
    }

    /**
     * @inheritDoc
     */
    public function only($indices)
    {
        return $this->intersectKeys(collect($indices)->flip()->toArray());
    }

    /**
     * @inheritDoc
     */
    public function pipe(callable $callback)
    {
        return $callback($this);
    }

    /**
     * @inheritDoc
     */
    public function random($num)
    {
        return $this->shuffle()->slice(0, $num);
    }

    /**
     * @inheritDoc
     */
    public function indicesOf($value)
    {
        return $this->filter(function($val) use ($value) {
            return $val == $value;
        })->map(function($val, $key) {
            return $key;
        });
    }

    /**
     * @inheritDoc
     */
    public function shuffle()
    {
        return collect(shuffle($data = $this->data));
    }

    /**
     * @inheritDoc
     */
    public function slice($offset, $length = null)
    {
        return collect(array_slice($this->data, $offset, $length, true));
    }

    /**
     * @inheritDoc
     */
    public function splice($offset, $length = null)
    {
        return $this->intersectKeys($this->slice($offset, $length)->toArray());
    }

    /**
     * @inheritDoc
     */
    public function split($num)
    {
        $data = [];
        $group = $iter = 0;
        $size = (int) ($this->count() / $num);
        foreach ($this as $key => $val) {
            $data[$group][$key] = $val;
            if ($iter++ > $size) {
                $group++;
                $iter = 0;
            }
        }
        return collect($data);
    }

    /**
     * @inheritDoc
     */
    public function transform(callable $callback)
    {
        $this->data = $this->map($callback)->toArray();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function union($data)
    {
        // @todo Need a merge that doesn't change this collection
        return collect(
            array_merge(
                collect($data)->toArray(),
                $this->toArray()
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function zip(...$data)
    {
        return collect(
            array_map(
                $this->toArray(),
                ...$data
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function foldRight(callable $callback, $initial = null)
    {
        $iter = 0;
        $carry = $initial;
        foreach ($this as $key => $val) {
            $carry = $callback($val, $carry, $key, $iter++);
        }
        return $carry;
    }

    /**
     * @inheritDoc
     */
    public function foldLeft(callable $callback, $initial = null)
    {
        return $this->reverse()->foldRight($callback, $initial);
    }

    /**
     * @inheritDoc
     */
    public function all(callable $callback = null)
    {
        if (is_null($callback)) {
            $callback = function($val) {
                return (bool) $val;
            };
        }
        return $this->filter($callback)->isEmpty();
    }

    /**
     * @inheritDoc
     */
    public function none(callable $callback = null)
    {
        if (is_null($callback)) {
            $callback = function($val) {
                return (bool) $val;
            };
        }
        return $this->filter($callback)->isEmpty();
    }

    // BEGIN Numeric Collection Methods
    // These methods only really work on numeric data.

    /**
     * Increment an item.
     *
     * Increment the item specified by $key by one value. Intended for integers
     * but also works (using this term loosely) for letters. Any other data type
     * it may modify is unintended behavior at best.
     *
     * This method modifies its internal data array rather than returning a new
     * collection.
     *
     * @param mixed $key      The key of the item you want to increment.
     * @param int   $interval The interval that $key should be incremented by
     *
     * @return $this
     */
    public function increment($key, $interval = 1)
    {
        $val = $this->retrieve($key);
        for ($i = 0; $i < $interval; $i++) {
            $val++;
        }
        $this->set($key, $val);

        return $this;
    }

    /**
     * Decrement an item.
     *
     * Frcrement the item specified by $key by one value. Intended for integers.
     * Does not work for letters and if it does anything to anything else, it's
     * unintended at best.
     *
     * This method modifies its internal data array rather than returning a new
     * collection.
     *
     * @param mixed $key      The key of the item you want to decrement.
     * @param int   $interval The interval that $key should be decremented by
     *
     * @return $this
     */
    public function decrement($key, $interval = 1)
    {
        $val = $this->retrieve($key);
        for ($i = 0; $i < $interval; $i++) {
            $val--;
        }
        $this->set($key, $val);

        return $this;
    }

    /**
     * Get the sum.
     *
     * @return int|float The sum of all values in collection
     */
    public function sum()
    {
        return array_sum($this->toArray());
    }

    /**
     * Get the average.
     *
     * @return float|int The average value from the collection
     */
    public function average()
    {
        return $this->sum() / $this->count();
    }

    /**
     * Get the mode.
     *
     * @return float|int The mode
     */
    public function mode()
    {
        $counts = $this->counts()->toArray();
        arsort($counts);
        $mode = key($counts);

        return (strpos($mode, '.')) ? floatval($mode) : intval($mode);
    }

    /**
     * Get the median value.
     *
     * @return float|int The median value
     */
    public function median()
    {
        $count = $this->count();
        $data  = $this->toArray();
        natcasesort($data);
        $middle = $count / 2;
        $values = array_values($data);
        if ($count % 2 == 0) {
            // even number, use middle
            $low  = $values[$middle - 1];
            $high = $values[$middle];

            return ($low + $high) / 2;
        }
        // odd number return median
        return $values[$middle];
    }

    /**
     * Get the maximum value.
     *
     * @return mixed The maximum
     */
    public function max()
    {
        return max($this->data);
    }

    /**
     * Get the minimum value.
     *
     * @return mixed The minimum
     */
    public function min()
    {
        return min($this->data);
    }

    /**
     * Get the number of times each item occurs in the collection.

     * This method will return a NumericCollection where keys are the
     * values and values are the number of times that value occurs in
     * the original collection.

     * @return CollectionInterface
     */
    public function counts()
    {
        return collect(array_count_values($this->toArray()));
    }
}
