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
use Noz\Traits\IsArrayable;
use OutOfRangeException;
use RuntimeException;

use Iterator;
use ArrayAccess;
use Countable;
use SplFixedArray;
use Traversable;

use Illuminate\Support\Str;

use Noz\Contracts\Arrayable;
use Noz\Contracts\Immutable;
use Noz\Contracts\Invokable;
use Noz\Contracts\Structure\Sequenceable;

use Noz\Traits\IsContainer;
use Noz\Traits\IsImmutable;

use function
    Noz\to_array,
    Noz\is_traversable;

class Sequence implements
    Sequenceable,
    ArrayAccess,
    Immutable,
    Countable,
    Arrayable,
    Invokable,
    Iterator
{
    use IsImmutable, IsContainer, IsArrayable;

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
     * indexing, sub-sequence selection, etc.
     *
     * @internal param int $offset The offset to return
     *
     * @return mixed
     *
     * @todo Put all the slice logic into a helper function or several
     */
    public function __invoke()
    {
        $args = func_get_args();
        if (count($args)) {
            $count = $this->count();
            $offset = array_pop($args);
            if (Str::contains($offset, static::SLICE_DELIM)) {
                // return slice
                list($start, $end) = explode(static::SLICE_DELIM, $offset, 2);
                if ($start == '') {
                    $start = 0;
                }
                if ($end == '') {
                    $end = $count - 1;
                }
                if (is_numeric($start) && is_numeric($end)) {
                    if ($start < 0) {
                        $start = $count - abs($start);
                    }
                    if ($end < 0) {
                        $end = $count - abs($end);
                    }
                    $length = $end - $start + 1;
                    return new static(array_slice($this->getData(), $start, $length));
                }
            }
            if (is_numeric($offset)) {
                if ($offset < 0) {
                    $offset = $count - abs($offset);
                }
                return $this[$offset];
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
                'Cannot %s, %s is immutable.',
                __METHOD__,
                __CLASS__
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
     * Return the current element
     * @link  http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->data->current();
    }

    /**
     * Move forward to next element
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->data->next();
    }

    /**
     * Return the key of the current element
     * @link  http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->data->key();
    }

    /**
     * Checks if current position is valid
     * @link  http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->data->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->data->rewind();
    }

    /**
     * Count elements of an object
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->data->count();
    }

    public function offsetGet($offset)
    {
        if (Str::contains($offset, static::SLICE_DELIM)) {
            return $this($offset)->toArray();
        }
        if ($offset < 0) {
            $offset = $this->count() + $offset;
        }
        try {
            return $this->data->offsetGet($offset);
        } catch (RuntimeException $e) {
            throw new OutOfRangeException($e->getMessage());
        }
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function offsetExists($offset)
    {
        return $this->data->offsetExists($offset);
    }

    /**
     * Prepend item to collection.
     * Prepend an item to this collection (in place).
     * @param mixed $item Item to prepend to collection
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
     * Append an item to this collection (in place).
     * @param mixed $item Item to append to collection
     * @return Sequence
     */
    public function append($item)
    {
        $arr = $this->getData();
        array_push($arr, $item);
        return new static($arr);
    }

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
     * Passes entire collection to provided callback and returns the result.
     * @param callable $funk The callback funkshun
     * @return mixed
     */
    public function pipe(callable $funk)
    {
        return $funk($this);
    }

    /**
     * Does every item return true?
     * If callback is provided, this method will return true if all items in collection cause callback to return true.
     * Otherwise, it will return true if all items in the collection have a truthy value.
     * @param callable|null $funk The callback
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
     * This method is the exact opposite of "all".
     * @param callable|null $funk The callback
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

    public function last(callable $funk = null, $default = null)
    {
        // TODO: Implement last() method.
    }

    /**
     * Return new sequence with the first item "bumped" off.
     * @return Sequenceable
     */
    public function bump()
    {
        // TODO: Implement bump() method.
    }

    /**
     * Return new sequence with the last item "dropped" off.
     * @return Sequenceable
     */
    public function drop()
    {
        // TODO: Implement drop() method.
    }

    /**
     * Get collection as a sequence.
     * @return array
     */
    public function toSeq()
    {
        // TODO: Implement toSeq() method.
    }

    /**
     * Get collection as a dictionary.
     * @return array
     */
    public function toDict()
    {
        // TODO: Implement toDict() method.
    }

    /**
     * Get collection as a set.
     * @return array
     */
    public function toSet()
    {
        // TODO: Implement toSet() method.
    }

    /**
     * Get collection as a map.
     * @return array
     */
    public function toMap()
    {
        // TODO: Implement toMap() method.
    }

    /**
     * Get collection as a list.
     * @return array
     */
    public function toList()
    {
        // TODO: Implement toList() method.
    }
}