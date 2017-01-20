<?php
/*
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 *
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/nozavroni/collections/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz;

use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Iterator;
use Noz\Immutable\Collection;
use Noz\Immutable\Sequence;
use Noz\Contracts\CollectionInterface;
use RuntimeException;
use Serializable;
use Traversable;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Collection factory.
 *
 * Simply an alias to (new Collection($in)). Allows for a little more concise and
 * simpler instantiation of a collection. Also I plan to eventually support
 * additional input types that will make this function more flexible and forgiving
 * than simply instantiating a Collection object, but for now the two are identical.
 *
 * @param array|Iterator $data Either an array or an iterator of data
 *
 * @return CollectionInterface
 */
function collect($data = null)
{
    return Collection::factory($data);
}

/**
 * Invoke a callable and return result.
 *
 * Pass in a callable followed by whatever arguments you want passed to
 * it and this function will invoke it with your arguments and return
 * the result.
 *
 * @param callable $callback The callback function to invoke
 * @param array ...$args     The args to pass to your callable
 *
 * @return mixed The result of your invoked callable
 */
function invoke(callable $callback, ...$args)
{
    return call_user_func($callback, ...$args);
}

/**
 * Underscore function.

 * This function is meant to work sort of like jQuery's "$()". It is a contextual catch-all type function. It works
 * as a short-hand alias for invoke, collect, and with.

 * @param callable|mixed    $in
 * @param mixed ...         $_
 *
 * @return mixed|CollectionInterface
 */
function _($in, ...$args)
{
    if (is_callable($in)) {
        return invoke($in, ...$args);
    }
    if (is_traversable($in)) {
        return collect($in);
    }
    return $in;
}

/**
 * Determine if data is traversable.
 *
 * Pass in any variable and this function will tell you whether or not it
 * is traversable. Basically this just means that it is either an array or an iterator.
 * This function was written simply because I was tired of if statements that checked
 * whether a variable was an array or a descendant of \Iterator. So I wrote this guy.
 *
 * @param mixed $data The variable to determine traversability
 *
 * @return bool True if $input is an array or an Iterator
 */
function is_traversable($data)
{
    return is_array($data) || $data instanceof Traversable;
}

/**
 * Can data be converted to an array?
 *
 * @param mixed $data The data to check
 *
 * @return bool
 */
function is_arrayable($data)
{
    if (!is_array($data)) {
        if (is_object($data)) {
            return (
                method_exists($data, 'toArray') ||
                $data instanceof Traversable
            );
        }
        return false;
    }
    return true;
}

/**
 * Convert any traversable to an array.
 *
 * This is useful because it will take any traversable data structure (including an array) and return an array.
 * It can optionally do this recursively.
 *
 * @param array|Traversable $data      Traversable data
 * @param bool              $recursive Apply recursively?
 *
 * @return array
 */
function traversable_to_array($data, $recursive = true)
{
    $arr = [];
    foreach ($data as $key => $val) {
        if ($recursive) {
            if (is_traversable($val)) {
                $val = traversable_to_array($val);
            }
        }
        $arr[$key] = $val;
    }
    return $arr;
}

/**
 * Convert data to an array.
 *
 * Accepts any kind of data and converts it to an array. If strict mode is on, only data that returns true from
 * is_arrayable() will be converted to an array. Anything else will cause an InvalidArgumentException to be thrown.

 * @param mixed $data   Data to convert to array
 * @param bool  $strict Whether to use strict mode

 * @return array
 *
 * @throws InvalidArgumentException
 */
function to_array($data, $strict = true)
{
    if (is_arrayable($data)) {
        if (!is_array($data)) {
            // this is what makes toArray() work recursively
            // it must stay right where it is do not move it
            if (method_exists($data, 'toArray')) {
                $data = $data->toArray();
            }
            if ($data instanceof Traversable) {
                $data = traversable_to_array($data);
            }
        }
        return $data;
    }
    if ($strict) {
        throw new InvalidArgumentException(sprintf(
            'Invalid argument for "%s". Cannot convert "%s" to an array.',
            __FUNCTION__,
            typeof($data)
        ));
    }
    if (is_object($data)) {
        $values = [];
        foreach ($data as $key => $val) {
            $values[$key] = $val;
        }
        return $values;
    }
    if (is_null($data)) {
        return [];
    }
    return [$data];
}

/**
 * Get object count.
 *
 * This function will accept any data and attempt to return its count.
 *
 * @param mixed $data The data to count
 *
 * @return int
 */
function get_count($data)
{
    if (is_null($data)) {
        return $data;
    }

    if (is_array($data)) {
        return count($data);
    }

    if (is_numeric($data)) {
        return (int) $data;
    }

    if ($data === '') {
        return 0;
    }

    if (is_object($data)) {
        if (method_exists($data, 'count')) {
            $count = $data->count();
        } elseif (method_exists($data, '__toString')) {
            $count = (int) $data->__toString();
        } elseif (is_traversable($data)) {
            $count = 0;
            foreach ($data as $item) {
                $count++;
            }
        }
        if (isset($count)) {
            return (int) $count;
        }
    }

    throw new RuntimeException('Cannot convert to int.');
}

/**
 * Normalize offset to positive integer.
 *
 * Provided with the requested offset, whether it be a string, an integer (positive or negative), or some type of
 * object, this function will normalize it to a positive integer offset or, failing that, it will throw an exception.
 * A negative offset will require either the traversable that is being indexed or its total count in order to normalize

 * @param int|mixed $offset The offset to normalize
 * @param int|array|traversable $count  Either the traversable count, or the traversable itself.

 * @return int

 * @throws RuntimeException If offset cannot be normalized
 * @throws InvalidArgumentException If offset is negative and count is not provided
 */
function normalize_offset($offset, $count = null)
{
    if (is_object($offset) && method_exists($offset, '__toString')) {
        $offset = (string) $offset;
    }

    if (!is_numeric($offset)) {
        throw new RuntimeException('Invalid offset.');
    }

    $count = get_count($count);

    if ($offset < 0) {
        if (is_null($count)) {
            throw new InvalidArgumentException('Cannot normalize negative offset without a total count.');
        }
        $offset += $count;
    }
    return (int) $offset;
}

/**
 * Get range start and end from string.
 *
 * Provided a string in the format of "start:end" and the total items in a collection, this function will return an
 * array in the form of [start, length].
 *
 * @param string $range The range to get (in the format of "start:end"
 * @param int|array|traversable $count  Either the traversable count, or the traversable itself.
 *
 * @return array [start, length]
 */
function get_range_start_end($range, $count = null)
{
    $count = get_count($count);
    if (Str::contains($range, Sequence::SLICE_DELIM)) {
        // return slice as a new sequence
        list($start, $end) = explode(Sequence::SLICE_DELIM, $range, 2);
        if ($start == '') {
            $start = 0;
        }
        if ($end == '') {
            $end = $count - 1;
        }
        $start = normalize_offset($start, $count);
        $end = normalize_offset($end, $count) + 1;
        if ($end > $start) {
            $length = $end - $start;
            return [$start, $length];
        }
    }
    throw new RuntimeException('Invalid index range/offset.');
}

/**
 * Get data type.
 *
 * Inspects data to determine its type.
 *
 * @param mixed  $data       The data to check
 * @param bool   $meta       Whether to include meta data such as length/size
// * @param string $returnType What type of value to return (array or string)
 *
 * @return string
 */
function typeof($data, $meta = true/*, $returnType = 'string'*/)
{
    $type = gettype($data);
    if ($meta) {
        switch($type) {
            case 'object':
                $class = get_class($data);
                return "{$type} <{$class}>";
            case 'resource':
                $restype = get_resource_type($data);
                return "{$type} <{$restype}>";
        }
    } else {
        switch($type) {
            case 'object':
                return get_class($data);
            case 'resource':
                return get_resource_type($data);
        }
    }
    return $type;
}

// BEGIN debug/testing functions

/**
 * Dump and die.
 *
 * @param mixed $input Data to dump
 * @param bool  $exit  Should we exit after dump?
 * @param bool  $label Should we print a label?
 * @codeCoverageIgnore
 */
function dd($input, $exit = true, $label = null)
{
    if (is_null($label)) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        $label = 'File: ';
        $label .= pathinfo($trace[0]['file'], PATHINFO_FILENAME);
        $label .= ':' . $trace[0]['line'];
        echo $label . "\n";
    } else {
        echo $label . "\n" . implode(
                array_map(
                    function () {
                        return '-';
                    },
                    str_split($label)
                )
            ) . "\n";
    }
    var_dump($input);
    echo "\n";
    if ($exit) {
        exit;
    }
}

/**
 * Exactly the same as var_dump, except that it returns its output rather than dumping it.
 */
function sdump($var)
{
    ob_start();
    var_dump($var);
    return ob_get_clean();
}

/**
 * Get object hash/checksum.
 * Using a var_dump of an object, this will return a hash that tells you if anything in your object has changed. Just
 * create a hash of an object, do some stuff with it, then create another hash of it and compare the two. If they are
 * different, teh object has changed in some way.
 *
 * @param object $obj The object to hash
 * @param string $alg The hash algorithm (supports md5 or sha1)
 *
 * @return string
 *
 * @throws InvalidArgumentException
 */
function object_hash($obj, $alg = 'md5')
{
    $algorithms = ['md5', 'sha1'];
    if (!in_array($alg, $algorithms)) {
        throw new InvalidArgumentException(sprintf(
            '"%s" is not a valid hash algorithm (%s).',
            $alg,
            implode(', ', $algorithms)
        ));
    }
    return call_user_func($alg, sdump($obj));
}
