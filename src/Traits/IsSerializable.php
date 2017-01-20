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
 * Interface IsSerializable.
 *
 * Ensures a class can be converted to an array using toArray()
 *
 * @package Noz\Contracts
 */
trait IsSerializable
{
    /**
     * Serialize collection data and return it.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * Unserialize serialized data.
     *
     * @param string $serialized The serialized data
     */
    abstract public function unserialize($serialized);

    /**
     * @return array
     */
    abstract public function toArray();
}
