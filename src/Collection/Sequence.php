<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2017 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Collection;

use BadMethodCallException;
use RuntimeException;

use Iterator;
use ArrayAccess;
use Countable;
use Serializable;
use SplFixedArray;
use Traversable;

use Illuminate\Support\Str;

use Noz\Contracts\Arrayable;
use Noz\Contracts\Immutable;
use Noz\Contracts\Invokable;
use Noz\Contracts\Structure\Sequenceable;

use Noz\Traits\IsContainer;
use Noz\Traits\IsImmutable;
use Noz\Traits\IsArrayable;
use Noz\Traits\IsSerializable;

use function
    Noz\to_array,
    Noz\is_traversable,
    Noz\normalize_offset,
    Noz\get_range_start_end;

/**
 * Sequence Collection.
 *
 * A sequence is a collection with consecutive, numeric indexes, starting from zero. It is immutable, and so any
 * operation that requires a change to its state will return a new sequence with whatever changes were intended.
 * The fact that this type of collection is indexed in this way allows some very convenient and useful functionality.
 * For instance, you can treat a sequence as if it were a regular array, using square brackets. Unlike a regular array
 * however, you may use a negative index to get the n-th from the last item. You may also use a string in the form of
 * "$start:$end" to retrieve a "slice" of the sequence.
 */
class Sequence implements
    Sequenceable,
    ArrayAccess,
    Immutable,
    Countable,
    Arrayable,
    Invokable,
    Iterator
{
    use IsImmutable,
        IsContainer,
        IsArrayable,
        IsSerializable;

    /**
     * Delimiter used to fetch slices.
     */
    const SLICE_DELIM = ':';

    /**
     * Fixed-size data storage array.
     *
     * @var SplFixedArray
     */
    private $data;

    /**
     * Sequence constructor.
     *
     * @param array|Traversable $data The data to sequence
     */
    public function __construct($data = null)
    {
        if (is_null($data)) {
            $data = [];
        }
        $this->setData($data);
    }

    /**
     * Invoke sequence.

     * A sequence is invokable as if it were a function. This allows some pretty useful functionality such as negative
     * indexing, sub-sequence selection, etc. Basically, any way you invoke a sequence, you're going to get back either
     * a single value from the sequence or a subset of it.

     * @internal param mixed $funk Either a numerical offset (positive or negative), a range string (start:end), or a
     * callback to be used as a filter.

     * @return mixed

     * @todo Put all the slice logic into a helper function or several
     */
    public function __invoke()
    {
        $args = func_get_args();
        if ($argc = count($args)) {
            $offset = array_shift($args);
            $count = $this->count();
            if (count($args)) {
                // if there are more args...
                $length =  array_shift($args);
            }
            if (Str::contains($offset, static::SLICE_DELIM)) {
                list($start, $length) = get_range_start_end($offset, $count);
            } else {
                $start = normalize_offset($offset, $count);
            }
            if (isset($length)) {
                return new static(array_slice($this->getData(), $start, $length));
            } else {
                return $this[$start];
            }
        }
        return $this->toArray();
    }

    /**
     * Set data in sequence.
     *
     * Any array or traversable structure passed in will be re-indexed numerically.
     *
     * @param Traversable|array $data The sequence data
     */
    private function setData($data)
    {
        if (!is_traversable($data)) {
            // @todo Maybe create an ImmutableException for this?
            throw new BadMethodCallException(sprintf(
                'Forbidden method call: %s',
                __METHOD__
            ));
        }
        $data = array_values(to_array($data));
        $this->data = SplFixedArray::fromArray($data);
    }

    /**
     * Get data.
     *
     * Get the underlying data array.
     *
     * @return array
     */
    protected function getData()
    {
        return $this->data->toArray();
    }

    /**
     * Return the current element.
     *
     * @return mixed
     */
    public function current()
    {
        return $this->data->current();
    }

    /**
     * Move forward to next element.
     */
    public function next()
    {
        $this->data->next();
    }

    /**
     * Return the key of the current element.
     *
     * @return mixed|null
     */
    public function key()
    {
        return $this->data->key();
    }

    /**
     * Checks if current position is valid.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->data->valid();
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind()
    {
        $this->data->rewind();
    }

    /**
     * Count elements of an object.
     *
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return $this->data->count();
    }

    /**
     * Get item at collection.
     *
     * This method functions as the ArrayAccess getter. Depending on whether an int, a negative int, or a string is passed, this
     *
     * @param int|string $offset Offset (index) to retrieve
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (Str::contains($offset, static::SLICE_DELIM)) {
            return $this($offset)->toArray();
        }
        if ($offset < 0) {
            $offset = $this->count() + $offset;
        }
        return $this->data->offsetGet($offset);
    }

    /**
     * Set offset.
     *
     * Because Sequence is immutable, this operation is not allowed. Use set() instead.
     *
     * @param int $offset  Numeric offset
     * @param mixed $value Value
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException(sprintf(
            'Cannot set value on %s object.',
            __CLASS__
        ));
    }

    /**
     * Set value at given offset.
     *
     * Creates a copy of the sequence, setting the specified offset to the specified value (on the copy), and returns it.
     *
     * @param mixed $offset The index offset to set
     * @param mixed $value  The value to set it to
     *
     * @return $this
     */
    public function set($offset, $value)
    {
        $arr = $this->getData();
        $arr[$offset] = $value;
        return new static($arr);
    }

    /**
     *
     * Because Sequence is immutable, this operation is not allowed. Use set() instead.
     *
     * @param int $offset  Numeric offset
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException(sprintf(
            'Cannot unset value on %s object.',
            __CLASS__
        ));
    }

    /**
     * Get new sequence without specified indices.
     *
     * Creates a copy of the sequence, unsetting the specified offset(s) (on the copy), and returns it.
     *
     * @param int|string|array The offset, range, or set of indices to remove.
     *
     * @return $this
     */
    public function except($offset)
    {
        if (!is_array($offset)) {
            if (is_string($offset) && Str::contains($offset, static::SLICE_DELIM)) {
                list($start, $length) = get_range_start_end($offset, $this->count());
                $indices = array_slice($this->getData(), $start, $length, true);
            } else {
                $indices = array_flip([$offset]);
            }
        } else {
            $indices = array_flip($offset);
        }
        return $this->diffKeys($indices);
    }

    /**
     * Is there a value at specified offset?
     *
     * Returns true of there is an item in the collection at the specified numerical offset.
     *
     * @param mixed $offset The index offset to check
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if ($offset < 0) {
            $offset = $this->count() + $offset;
        }
        return $this->data->offsetExists($offset);
    }

    /**
     * Get diff by index.
     *
     * @param array|Traversable$data The array/traversable
     *
     * @return static
     */
    public function diffKeys($data)
    {
        if (!is_array($data)) {
            $data = to_array($data);
        }
        return new static(array_diff_key(
            $this->getData(),
            $data
        ));
    }

    /**
     * Get diff by value.
     *
     * @param array|Traversable$data The array/traversable
     *
     * @return static
     */
    public function diff($data)
    {
        if (!is_array($data)) {
            $data = to_array($data);
        }
        return new static(array_diff(
            $this->getData(),
            $data
        ));
    }

    /**
     * Prepend item to collection.
     *
     * Prepend an item to this collection (in place).
     *
     * @param mixed $item Item to prepend to collection
     *
     * @return Sequence
     */
     public function prepend($item)
     {
         $arr = $this->getData();
         array_unshift($arr, $item);
         return new static($arr);
     }

    /**
     * Append item to collection.
     *
     * Append an item to this collection (in place).
     *
     * @param mixed $item Item to append to collection
     *
     * @return Sequence
     */
    public function append($item)
    {
        $arr = $this->getData();
        array_push($arr, $item);
        return new static($arr);
    }

    /**
     * Fold (reduce) sequence into a single value.
     *
     * @param callable $funk    A callback function
     * @param mixed    $initial Initial value for accumulator
     *
     * @return mixed
     */
    public function fold(callable $funk, $initial = null)
    {
        $carry = $initial;
        foreach ($this->getData() as $key => $val) {
            $carry = $funk($carry, $val, $key);
        }
        return $carry;
    }

    /**
     * Is collection empty?
     *
     * You may optionally pass in a callback which will determine if each of the items within the collection are empty.
     * If all items in the collection are empty according to this callback, this method will return true.
     *
     * @param callable $funk The callback
     *
     * @return bool
     */
    public function isEmpty(callable $funk = null)
    {
        if (!is_null($funk)) {
            return $this->fold(function ($carry, $val) use ($funk) {
                return $carry && $funk($val);
            }, true);
        }
        return empty($this->data->toArray());
    }

    /**
     * Pipe collection through callback.
     *
     * Passes entire collection to provided callback and returns the result.
     *
     * @param callable $funk The callback funkshun
     *
     * @return mixed
     */
    public function pipe(callable $funk)
    {
        return $funk($this);
    }

    /**
     * Does every item return true?
     *
     * If callback is provided, this method will return true if all items in collection cause callback to return true.
     * Otherwise, it will return true if all items in the collection have a truthy value.
     *
     * @param callable|null $funk The callback
     *
     * @return bool
     */
    public function every(callable $funk = null)
    {
        return $this->fold(function($carry, $val, $key) use ($funk) {
            if (!$carry) {
                return false;
            }
            if (!is_null($funk)) {
                return $funk($val, $key);
            }
            return (bool) $val;
        }, true);
    }

    /**
     * Does every item return false?
     *
     * This method is the exact opposite of "all".
     *
     * @param callable|null $funk The callback
     *
     * @return bool
     */
    public function none(callable $funk = null)
    {
        return !$this->fold(function($carry, $val, $key) use ($funk) {
            if ($carry) {
                return true;
            }
            if (!is_null($funk)) {
                return $funk($val, $key);
            }
            return (bool) $val;
        }, false);
    }

    /**
     * Get first item.
     *
     * Retrieve the first item in the collection or, if a callback is provided, return the first item that, when passed
     * to the callback, returns true.
     *
     * @param callable|null $funk    The callback function
     * @param null          $default The default value
     *
     * @return mixed
     */
    public function first(callable $funk = null, $default = null)
    {
        if (is_null($funk) && $this->count()) {
            return $this[0];
        }
        foreach ($this as $key => $val) {
            if ($funk($val, $key)) {
                return $val;
            }
        }
        return $default;
    }

    /**
     * Get last item.
     *
     * Retrieve the last item in the collection or, if a callback is provided, return the last item that, when passed
     * to the callback, returns true.
     *
     * @param callable|null $funk    The callback function
     * @param null          $default The default value
     *
     * @return mixed
     */
    public function last(callable $funk = null, $default = null)
    {
        return $this->reverse()->first($funk, $default);
    }

    /**
     * Get sequence in reverse order.
     *
     * @return Sequenceable
     */
    public function reverse()
    {
        return new static(array_reverse($this->getData()));
    }

    /**
     * Return new sequence with the first item "bumped" off.
     *
     * @return Sequenceable
     */
    public function bump()
    {
        $arr = $this->getData();
        array_shift($arr);
        return new static($arr);
    }

    /**
     * Return new sequence with the last item "dropped" off.
     *
     * @return Sequenceable
     */
    public function drop()
    {
        $arr = $this->getData();
        array_pop($arr);
        return new static($arr);
    }

    public function unserialize($serialized)
    {
        $this->setData(unserialize($serialized));
    }
}
