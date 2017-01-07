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
interface Equatable
{
    /**
     * Is object equal to another?
     *
     * Returns true if this object is equal to another.
     *
     * @param mixed $object Comparison object
     *
     * @return bool
     */
    public function equalTo($object);

    /**
     * Is object identical to another?
     *
     * Returns true if this object is identical to another.
     *
     * @param mixed $object Comparison object
     *
     * @return bool
     */
    public function identicalTo($object);

    /**
     * Is object greater than another?
     *
     * Returns true if this object is greater than another.
     *
     * @param mixed $object Comparison object
     *
     * @return bool
     */
    public function greaterThan($object);

    /**
     * Is object less than another?
     *
     * Returns true if this object is less than another.
     *
     * @param mixed $object Comparison object
     *
     * @return bool
     */
    public function lessThan($object);
}