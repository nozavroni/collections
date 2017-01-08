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

use ArrayAccess;
use BadMethodCallException;

use Countable;
use Traversable;
use SplFixedArray;

use Illuminate\Support\Str;

use Noz\Contracts\Structure\Sequenceable;
use Noz\Contracts\Immutable;
use Noz\Contracts\Arrayable;
use Noz\Contracts\Invokable;

use Noz\Traits\IsImmutable;
use Noz\Traits\IsContainer;

use function Noz\to_array;
use function Noz\is_traversable;

class Sequence implements
    ArrayAccess,
    Sequenceable,
    Immutable,
    Countable,
    Arrayable,
    Invokable
{
    use IsImmutable, IsContainer;

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
    public function __construct($data)
    {
        $this->setData($data);
    }

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

    protected function getData()
    {
        return $this->data->toArray();
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

    public function toArray()
    {
        return $this->getData();
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

    public function offsetGet($offset)
    {
        return $this->data->offsetGet($offset);
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
        return array_reduce($this->getData(), $funk, $initial);
    }

    /**
     * Is collection empty?
     * You may optionally pass in a callback which will determine if each of the items within the collection are empty.
     * If all items in the collection are empty according to this callback, this method will return true.
     * @param callable $callback The callback
     * @return bool
     */
    public function isEmpty(callable $callback = null)
    {

    }

    /**
     * Pipe collection through callback.
     * Passes entire collection to provided callback and returns the result.
     * @param callable $callback
     * @return mixed
     */
    public function pipe(callable $callback)
    {
        // TODO: Implement pipe() method.
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
        return $this->fold(function($carry, $val, $key, $iter) use ($funk) {
            if (!$carry) {
                return false;
            }
            if (!is_null($funk)) {
                return $funk($val, $key, $iter);
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
        return $this->fold(function($carry, $val, $key, $iter) use ($funk) {
            if ($carry) {
                return false;
            }
            if (!is_null($funk)) {
                return !$funk($val, $key, $iter);
            }
            return !((bool) $val);
        }, false);
    }

    public function first(callable $funk = null, $default = null)
    {

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

    public function getOffset($offset)
    {
        // TODO: Implement getOffset() method.
    }

    public function hasOffset($offset)
    {
        // TODO: Implement hasOffset() method.
    }

    public function setOffset($offset)
    {
        // TODO: Implement setOffset() method.
    }

    public function unsetOffset($offset)
    {
        // TODO: Implement unsetOffset() method.
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