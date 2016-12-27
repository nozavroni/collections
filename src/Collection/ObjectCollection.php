<?php

/*
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/nozavroni/collections/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Collection;

use BadMethodCallException;
use InvalidArgumentException;

use function Noz\is_traversable;

/**
 * Class ObjectCollection.
 *
 * An object collection - stores objects.
 *
 * @package Noz\Collection
 */
class ObjectCollection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function prepareData($data)
    {
        return $data;
    }

    /**
     * Is correct input data type?
     *
     * @param mixed $data The data to assert correct type of
     *
     * @return bool
     */
    protected function isConsistentDataStructure($data)
    {
        // this collection may only contain scalar or null values
        if (!is_traversable($data)) {
            return false;
        }
        foreach ($data as $key => $val) {
            try {
                $this->assertValidType($val);
            } catch (InvalidArgumentException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assert a value is of valid type.
     *
     * @param mixed $value The value to check type of
     * @throws InvalidArgumentException
     */
    protected function assertValidType($value)
    {
        if (!is_object($value)) {
            throw new InvalidArgumentException("Invalid value type: " . gettype($value) . ". Expecting an object.");
        }
    }

    /**
     * {@inheritdoc}
     */
//    public function __toString()
//    {
//        // @todo __toString is not supposed to throw an exception. I need to remove __toString
//        // from AbstractCollection and only use it on collections where it makes sense, such as
//        // CharCollection and the like...
//        throw new BadMethodCallException(sprintf(
//            'Objects of type, "%s" cannot be converted to a string.',
//            __CLASS__
//        ));
//    }

    /**
     * {@inheritdoc}
     */
    public function join($delimiter = '')
    {
        // @todo I need to remove join
        // from AbstractCollection and only use it on collections where it makes sense, such as
        // CharCollection and the like...
        throw new BadMethodCallException(sprintf(
            'Objects of type, "%s" cannot be converted to a string.',
            __CLASS__
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function set($index, $val)
    {
        $this->assertValidType($val);
        return parent::set($index, $val);
    }

    /**
     * {@inheritdoc}
     */
    public function push(...$items)
    {
        foreach ($items as $item) {
            $this->assertValidType($item);
        }
        return parent::push(...$items);
    }

    /**
     * {@inheritdoc}
     */
    public function unshift(...$items)
    {
        foreach ($items as $item) {
            $this->assertValidType($item);
        }
        return parent::unshift(...$items);
    }

    /**
     * {@inheritdoc}
     */
    public function pad($size, $with = null)
    {
        $this->assertValidType($with);
        $data = $this->data;
        if (($count = count($data)) < $size) {
            while($count < $size) {
                $with = clone $with;
                $count = array_push($data, $with);
            }
        }
        return new self($data);
    }
}
