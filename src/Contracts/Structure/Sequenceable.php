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

/**
 * Interface Sequenceable.
 *
 * A sequenceable is an ordered collection of values, indexed numerically, from zero. Sequence objects should always
 * be consecutively indexed from zero.
 */
interface Sequenceable extends Collectable
{
    /**
     * Prepend item to collection.
     *
     * Return a new list with this item prepended to the collection.
     *
     * @param mixed $item Item to prepend to collection
     *
     * @return Listable
     */
    public function prepend($item);

    /**
     * Append item to collection.
     *
     * Return a new list with this item appended to the collection.
     *
     * @param mixed $item Item to append to collection
     *
     * @return Listable
     */
    public function append($item);

    public function first(callable $funk = null, $default = null);
    public function last(callable $funk = null, $default = null);
    public function reverse();
    /**
     * Return new sequence with the first item "bumped" off.
     *
     * @return Sequenceable
     */
    public function bump();

    /**
     * Return new sequence with the last item "dropped" off.
     *
     * @return Sequenceable
     */
    public function drop();
    public function diff($data);
    public function diffKeys($data);
    public function offsetGet($offset);
    public function offsetSet($offset, $value);
    public function offsetUnset($offset);
    public function offsetExists($offset);
    public function count();
}
