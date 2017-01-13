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

use InvalidArgumentException;

use Countable;
use Traversable;
use Serializable;
use SplDoublyLinkedList;

use Noz\Contracts\Structure\Listable;
use Noz\Contracts\Immutable;
use Noz\Contracts\Arrayable;
use Noz\Contracts\Invokable;

use Noz\Traits\IsArrayable;
use Noz\Traits\IsImmutable;
use Noz\Traits\IsContainer;

use function
    Noz\is_traversable;

class LList implements
    Listable,
    Immutable,
    Countable,
    Arrayable,
    Invokable,
    Serializable
{
    use IsImmutable,
        IsArrayable,
        IsContainer;
    /**
     * @var SplDoublyLinkedList
     */
    private $data;

    /**
     * LList constructor.
     *
     * @param array|Traversable $data The list constructor
     */
    public function __construct($data = null)
    {
        if (is_null($data)) {
            $data = [];
        }
        $this->setData($data);
    }

    /**
     * Set internal data array.
     *
     * @param array|Traversable $data The list constructor
     */
    private function setData($data)
    {
        if (!is_traversable($data)) {
            throw new InvalidArgumentException(
                '%s expects traversable data.',
                __CLASS__
            );
        }
        if ($data instanceof SplDoublyLinkedList) {
            $dll = $data;
        } else {
            $dll = new SplDoublyLinkedList($data);
            $dll->setIteratorMode(SplDoublyLinkedList::IT_MODE_KEEP);
            foreach($data as $key => $val) {
                $dll->push($val);
            }
        }
        $this->data = $dll;
    }

    /**
     * Get internal data array.
     *
     * @return SplDoublyLinkedList
     */
    protected function getData()
    {
        return clone $this->data;
    }

    /**
     * Invoke LList.
     *
     * This method is called when a LList object is invoked (called as if it were a function).
     *
     * @return mixed
     */
    public function __invoke()
    {

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

    /**
     * Return new sequence with the first item "bumped" off.
     *
     * @return Listable
     */
    public function bump()
    {
        $data = $this->getData();
        $data->shift();
        return new static($data);
    }

    /**
     * Return new sequence with the last item "dropped" off.
     *
     * @return Listable
     */
    public function drop()
    {
        $data = $this->getData();
        $data->pop();
        return new static($data);
    }

    /**
     * Get the top item of the list.
     *
     * @return mixed
     */
    public function top()
    {
        return $this->data->top();
    }

    /**
     * Get the bottom item of the list.
     *
     * @return mixed
     */
    public function bottom()
    {
        return $this->data->bottom();
    }

    /**
     * Is collection empty?
     * You may optionally pass in a callback which will determine if each of the items within the collection are empty.
     * If all items in the collection are empty according to this callback, this method will return true.
     *
     * @param callable $predicate The callback
     *
     * @return bool
     */
    public function isEmpty(callable $predicate = null)
    {
        if (!is_null($predicate)) {
            foreach ($this->data as $val) {
                if (!$predicate($val)) {
                    return false;
                }
            }
        }
        return $this->data->isEmpty();
    }

    /**
     * Pipe collection through callback.
     *
     * Passes entire collection to provided callback and returns the result.
     *
     * @param callable $through Function to pipe collection through
     *
     * @return mixed
     */
    public function pipe(callable $through)
    {
        return $through($this);
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
        return $this->fold(function($carry, $val, $key, $iter) use ($funk) {
            if (!$funk($val, $key, $iter)) {
                return false;
            }
            return $carry && true;
        }, true);
    }

    /**
     * Does every item return false?
     *
     * This method is the exact opposite of "all".
     *
     * @param callable|null $callback The callback
     *
     * @return bool
     */
    public function none(callable $callback = null)
    {
        return $this->fold(function($carry, $val, $key, $iter) use ($funk) {
            if ($funk($val, $key, $iter)) {
                return false;
            }
            return $carry && true;
        }, true);
    }

    /**
     * Prepend item to collection.
     *
     * Return a new list with this item prepended to the collection.
     *
     * @param mixed $item Item to prepend to collection
     *
     * @return Listable
     */
    public function prepend($item)
    {
        $data = $this->getData();
        $data->unshift($item);
        return new static($data);
    }

    /**
     * Append item to collection.
     *
     * Return a new list with this item appended to the collection.
     *
     * @param mixed $item Item to append to collection
     *
     * @return Listable
     */
    public function append($item)
    {
        $data = $this->getData();
        $data->push($item);
        return new static($data);
    }

    public function serialize()
    {
        return $this->getData()->serialize();
    }

    public function unserialize($serialized)
    {
        $data = new SplDoublyLinkedList;
        $data->unserialize($serialized);
        $this->data = $data;
    }

    /**
     * @param callable|null $folder
     * @param null          $initial
     *
     * @return null
     */
    public function fold(callable $folder = null, $initial = null)
    {
        $iter = 0;
        $carry = $initial;
        foreach ($this->getData() as $key => $val) {
            $carry = $folder($carry, $val, $key, $iter++);
        }
        return $carry;
    }

}
