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

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use Iterator;
use Noz\Contracts\Arrayable;
use Noz\Contracts\Invokable;
use Noz\Contracts\CollectionInterface;
use OutOfBoundsException;
use Traversable;

use Noz\Traits\IsArrayable;

use function
    Noz\is_traversable,
    Noz\typeof,
    Noz\collect;

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
    Arrayable,
    Invokable,
    Countable,
    Iterator
{
    use IsArrayable;

    /**
     * @var array The collection of data this object represents
     */
    private $data = [];

    /**
     * @var bool True unless we have advanced past the end of the data array
     */
    protected $isValid = true;

    /**
     * AbstractCollection constructor.
     *
     * @param mixed $data The data to wrap
     */
    public function __construct($data = null)
    {
        if (is_null($data)) {
            $data = [];
        }
        if (!is_traversable($data)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid input for %s. Expecting traversable data, got "%s".',
                __METHOD__,
                typeof($data)
            ));
        }
        $this->setData($data);
    }

    public function __invoke()
    {
        $args = collect(func_get_args());
        if ($args->hasOffset(0)) {
            if ($args->hasOffset(1)) {
                // two args only...
                return $this->set($args->getOffset(0), $args->getOffset(1));
            }
            // one arg only...
            $arg1 = $args->getOffset(0);
            if (is_scalar($arg1)) {
                return $this->get($arg1);
            }
            if (is_traversable($arg1)) {
                return $this->union($arg1);
            }
            // @todo Should probably throw ane invalid arg exception here...
        }
        return $this->toArray();
    }

    /**
     * Set underlying data array.
     *
     * Sets the collection data. This method should NEVER be called anywhere other than in __construct().
     *
     * @param array|Traversable $data The data to wrap
     */
    private function setData($data)
    {
        $arr = [];
        foreach ($data as $index => $value) {
            $arr[$index] = $value;
        }
        $this->data = $arr;
        $this->rewind();
    }

    /**
     * Get copy of underlying data array.
     *
     * Returns a copy of this collection's underlying data array. It returns a copy because collections are supposed to
     * be immutable. Nothing outside of the constructor should ever have direct access to the actual underlying array.
     *
     * @return array
     */
    protected function getData()
    {
        return $data = $this->data;
    }

    /**
     * @inheritDoc
     */
    public function count(callable $callback = null)
    {
        if (!is_null($callback)) {
            return $this->filter($callback)->count();
        }
        return count($this->getData());
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

    /**
     * @inheritDoc
     */
    public function sort($alg = null)
    {
        if (is_null($alg)) {
            $alg = 'strnatcasecmp';
        }
        $data = $this->getData();
        uasort($data, $alg);

        return collect($data);
    }

    /**
     * @inheritDoc
     */
    public function sortkeys($alg = null)
    {
        if (is_null($alg)) {
            $alg = 'strnatcasecmp';
        }
        $data = $this->getData();
        uksort($data, $alg);

        return collect($data);
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
        return array_key_exists($index, $this->getData());
    }

    /**
     * Set value at given index.
     *
     * This method simulates setting a value in this collection, but because collections are immutable, it actually
     * returns a copy of this collection with the value in the new collection set to specified value.
     *
     * @param mixed $index The index to set a value at
     * @param mixed $val   The value to set $index to
     *
     * @return CollectionInterface
     */
    public function set($index, $val)
    {
        $copy = $this->getData();
        $copy[$index] = $val;
        return collect($copy);
    }

    /**
     * Unset (delete) value at the given index.
     *
     * Get copy of collection with given index removed.
     *
     * @param mixed $index The index to unset
     *
     * @return CollectionInterface
     */
    public function delete($index)
    {
        return $this->except([$index]);
    }

    /**
     * Get index of a value.
     *
     * Given a value, this method will return the index of the first occurrence of that value.
     *
     * @param mixed $value Value to get the index of
     *
     * @return int|null|string
     */
    public function indexOf($value)
    {
        return $this->foldRight(function($carry, $val, $key, $iter) use ($value) {
            if (is_null($carry) && $val == $value) {
                return $key;
            }
            return $carry;
        });
    }

    /**
     * Get this collection's keys as a collection.
     *
     * @return CollectionInterface Containing this collection's keys
     */
    public function keys()
    {
        return collect(array_keys($this->getData()));
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
        return collect(array_values($this->getData()));
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
     * Pad collection to a certain size.
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
        return collect(array_pad($this->getData(), $size, $with));
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
        return collect(array_reverse($this->getData(), true));
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
        return collect(array_unique($this->getData()));
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
     * @return bool
     */
    public function isNumeric()
    {
        $data = $this->getData();
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
        if (!is_null($key = $this->foldRight(function($carry, $val, $key, $iter) use ($offset) {
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
            array_keys($this->getData()),
            array_values($this->getData())
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
        return collect($this);
    }

    /**
     * @inheritdoc
     * @todo Maybe read would be a better name for this?
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
     * @todo Maybe read would be a better name for this?
     */
    public function retrieve($index)
    {
        if (!$this->has($index)) {
            throw new OutOfBoundsException(__CLASS__ . ' could not retrieve value at index ' . $index);
        }
        return $this->getData()[$index];
    }

    /**
     * @inheritDoc
     */
    public function prepend($item)
    {
        $data = $this->getData();
        array_unshift($data, $item);

        return collect($data);
    }

    /**
     * @inheritDoc
     */
    public function append($item)
    {
        $data = $this->getData();
        array_push($data, $item);

        return collect($data);
    }

    /**
     * @inheritDoc
     */
    public function chunk($size)
    {
        $numchunks = (int) ($this->count() / $size);
        return collect($this->foldRight(function($chunks, $val, $key, $iter) use ($size, $numchunks) {
            if (is_null($chunks)) {
                $chunks = [];
            }
            if ($iter % $size == 0) {
                // start new chunk
                array_push($chunks, []);
            }
            $chunk = array_pop($chunks);
            array_push($chunk, $val);
            array_push($chunks, $chunk);

            return $chunks;
        }));
    }

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
                $this->getData(),
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
        return collect(array_flip($this->getData()));
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
        return empty($this->getData());
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
        return collect(shuffle($this->getData()));
    }

    /**
     * @inheritDoc
     */
    public function slice($offset, $length = null)
    {
        return collect(array_slice($this->getData(), $offset, $length, true));
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
    public function union($data)
    {
        return collect(
            array_merge(
                $this->toArray(),
                collect($data)->toArray()
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
            $carry = $callback($carry, $val, $key, $iter++);
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
     * @param mixed $index    The key of the item you want to increment.
     * @param int   $interval The interval that $key should be incremented by
     *
     * @return CollectionInterface
     */
    public function increment($index, $interval = 1)
    {
        $val = $this->retrieve($index);
        $val += $interval;
        return $this->set($index, $val);
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
     * @param mixed $index      The key of the item you want to decrement.
     * @param int   $interval The interval that $key should be decremented by
     *
     * @return CollectionInterface
     */
    public function decrement($index, $interval = 1)
    {
        $val = $this->retrieve($index);
        $val -= $interval;
        return $this->set($index, $val);
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
        return max($this->getData());
    }

    /**
     * Get the minimum value.
     *
     * @return mixed The minimum
     */
    public function min()
    {
        return min($this->getData());
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
