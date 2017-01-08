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
}
