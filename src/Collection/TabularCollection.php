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
use Noz\Contracts\CollectionInterface;
use OutOfBoundsException;

use function
    Noz\collect;

/**
 * Class TabularCollection.
 *
 * A collection that works as a table, meaning each item is itself an traversable item, all rows having the same set of
 * keys. This allows you to work with columns as well as rows.
 *
 * @package Noz\Collection
 */
class TabularCollection extends MultiCollection
{
    /**
     * Magic method call.
     *
     * @param string $method The name of the method
     * @param array  $args   The argument list
     *
     * @throws BadMethodCallException If no method exists
     *
     * @return mixed
     *
     * @todo Add phpdoc comments for dynamic methods
     * @todo throw BadMethodCallException
     */
    public function __call($method, $args)
    {
        $argc = count($args);
        if ($argc == 1 && $this->hasColumn($index = array_pop($args))) {
            $column = $this->getColumn($index);
            if (method_exists($column, $method)) {
                return call_user_func_array([$column, $method], $args);
            }
        }
        throw new BadMethodCallException('Method does not exist: ' . __CLASS__ . "::{$method}()");
    }

    /**
     * Does this collection have specified column?
     *
     * @param mixed $column The column index
     *
     * @return bool
     */
    public function hasColumn($column)
    {
        try {
            $this->getColumn($column);

            return true;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * Get column as collection.
     *
     * @param mixed $column The column index
     * @param bool  $throw  Throw an exception on failure
     *
     * @return CollectionInterface|false
     */
    public function getColumn($column, $throw = true)
    {
        $values = array_column($this->data, $column);
        if (count($values)) {
            return collect($values);
        }
        if ($throw) {
            throw new OutOfBoundsException(__CLASS__ . ' could not find column: ' . $column);
        }

        return false;
    }

    /**
     * Does this collection have a row at specified index?
     *
     * @param int $offset The column index
     *
     * @return bool
     */
    public function hasRow($offset)
    {
        try {
            $this->getRow($offset);

            return true;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * Get row at specified index.
     *
     * @param int $offset The row offset (starts from 0)
     *
     * @return CollectionInterface|false
     */
    public function getRow($offset)
    {
        return collect($this->getOffset($offset));
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $callback)
    {
        $ret = [];
        foreach ($this->data as $key => $row) {
            $ret[$key] = $callback(collect($row));
        }

        return collect($ret);
    }

    /**
     * {@inheritdoc}
     */
    public function walk(callable $callback, $extraContext = null)
    {
        foreach ($this as $offset => $row) {
            $callback(collect($row), $offset, $extraContext);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData($data)
    {
        return $data;
    }

    /**
     * Is input data structure valid?
     *
     * In order to determine whether a given data structure is valid for a
     * particular collection type (tabular, numeric, etc.), we have this method.
     *
     * @param mixed $data The data structure to check
     *
     * @return bool True if data structure is tabular
     */
    protected function isConsistentDataStructure($data)
    {
        return static::isTabular($data);
    }
}
