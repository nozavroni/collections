<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2017 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Contracts;

/**
 * Interface Comparable.
 *
 * Sorting a collection is an operation that is dependant on many factors. If a collection is sortable, and it contains
 * objects, one way that collection may be sorted is by comparing its objects. Implementing this interface provides a
 * class with the means of comparing its instances with each other in the context of sorting.
 */
interface Comparable
{
    /**
     * Compare this object to another.
     *
     * Compare to another obj and returns an integer representing how this object should be ordered in a collection.

     * @param mixed $object Comparison object

     * @return int
     */
    public function compareTo($object);
}