<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Traits;

/**
 * Interface IsArrayable.
 *
 * Ensures a class can be converted to an array using toArray()
 *
 * @package Noz\Contracts
 */
trait IsTraversable
{
    /**
     * @var bool
     */
    protected $traversable = true;

    /**
     * Is this object traversable?
     *
     * @return bool
     */
    public function isTraversable()
    {
        return $this->traversable;
    }

    /**
     * Traverse this object with given callback.
     * Loops over this object, passing each iteration to callback function. Return false at any time from callback to exit loop and return false.
     *
     * @param callable $callback
     *
     * @return bool
     */
    public function traverse(callable $callback)
    {
        if ($this->isTraversable()) {
            foreach ($this as $key => $val) {
                if (!$callback($val, $key)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}