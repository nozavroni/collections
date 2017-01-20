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

interface Listable extends Collectable
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

    /**
     * Return new sequence with the first item "bumped" off.
     *
     * @return Sequenceable
     */
    public function bump();

    /**
     * Return new sequence with the last item "dr1w22                                    opped" off.
     *
     * @return Sequenceable
     */
    public function drop();

    /**
     * Get the top item of the list.
     *
     * @return mixed
     */
    public function top();

    /**
     * Get the bottom item of the list.
     *
     * @return mixed
     */
    public function bottom();

    /**
     * Convert list to an array.
     *
     * @return array
     */
    public function toArray();

//    /**
//     * Get SplDoublyLinkedList.
//     *
//     * @return SplDoublyLinkedList
//     */
//    public function toDll();
}
