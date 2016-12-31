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

use SplObjectStorage;
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
     * The required object type.
     *
     * @var string
     */
    protected $type;

    /**
     * ObjectCollection constructor.
     *
     * @param array<object>|SplObjectStorage|null $data An array of objects or SplObjectStorage object
     * @param string                              $type A class that all objects should be an instance of
     */
    public function __construct($data = null, $type = null)
    {
        if (is_null($data)) {
            $data = [];
        }
        $this->setRequiredType($type);
        parent::__construct($data);
    }

    /**
     * Set the required object type.
     *
     * If a required type is set, all objects added to this collection must be of this type.
     *
     * @param string|null $type The required object type
     *
     * @return $this
     */
    public function setRequiredType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function join($delimiter = '')
    {
        // @todo I need to remove join and __toString
        // from AbstractCollection and only use it on collections where it makes sense, such as
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
    public function append($item)
    {
        $this->assertValidType($item);
        return parent::append($item);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend($item)
    {
        $this->assertValidType($item);
        return parent::prepend($item);
    }

    /**
     * Pad this collection to a certain size.
     *
     * Returns a new collection, padded to the given size, with clones of the given object.
     *
     * @param int         $size The number of items that should be in the collection
     * @param object|null $with The value to pad the collection with
     *
     * @return ObjectCollection
     */
    public function pad($size, $with = null)
    {
        $this->assertValidType($with);
        $data = $this->data;
        if (($count = count($data)) < $size) {
            while ($count < $size) {
                $with  = clone $with;
                $count = array_push($data, $with);
            }
        }

        return new self($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData($data)
    {
        if ($data instanceof SplObjectStorage) {
            $tmp = [];
            foreach ($data as $obj) {
                $tmp[spl_object_hash($obj)] = $obj;
            }
            // @todo Data is potentially still lost even though I copy all the objects from the SplObjectStorage object.
            // These objects store not only objects, but also data associated with that object. That data is lost here.
            $data = $tmp;
        }

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
     *
     * @throws InvalidArgumentException
     */
    protected function assertValidType($value)
    {
        if (is_object($value)) {
            if (is_null($this->type)) {
                return;
            }
            if ($value instanceof $this->type) {
                return;
            }
            throw new InvalidArgumentException(sprintf(
                'Invalid object type "%s", expecting "%s".',
                get_class($value),
                $this->type
            ));
        }
        throw new InvalidArgumentException('Invalid value type: "' . gettype($value) . '". Expecting an object.');
    }
}
