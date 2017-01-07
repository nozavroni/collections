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

use Countable;
use Traversable;
use SplFixedArray;

use Noz\Contracts\Structure\Sequenceable;
use Noz\Contracts\Immutable;
use Noz\Contracts\Arrayable;
use Noz\Contracts\Invokable;

use Noz\Traits\IsImmutable;

use function Noz\to_array;
use function Noz\is_traversable;

class Sequence implements
    Sequenceable,
    Immutable,
    Countable,
    Arrayable,
    Invokable
{
    use IsImmutable;

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
//        $size = count($data);
//        $this->data = new SplFixedArray($size);

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

    public function __invoke()
    {
        return $this->toArray();
    }

    /**
     * Prepend item to collection.
     * Prepend an item to this collection (in place).
     * @param mixed $item Item to prepend to collection
     * @return $this
     */public function prepend($item)
{
    // TODO: Implement prepend() method.
}

    /**
     * Append item to collection.
     * Append an item to this collection (in place).
     * @param mixed $item Item to append to collection
     * @return $this
     */
    public function append($item)
    {
        // TODO: Implement append() method.
    }

    /**
     * Check that collection contains a value.
     * You may optionally pass in a callable to provide your own equality test.
     * @param mixed|callable $value The value to search for
     * @return mixed
     */
    public function contains($value)
    {
        // TODO: Implement contains() method.
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
        // TODO: Implement isEmpty() method.
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
     * @param callable|null $callback The callback
     * @return bool
     */
    public function every(callable $callback = null)
    {
        // TODO: Implement every() method.
    }

    /**
     * Does every item return false?
     * This method is the exact opposite of "all".
     * @param callable|null $callback The callback
     * @return bool
     */
    public function none(callable $callback = null)
    {
        // TODO: Implement none() method.
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