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

use Iterator;
use Noz\Collection\AbstractCollection;
use Noz\Collection\Collection;

/**
 * Collection factory.
 *
 * Simply an alias to (new Collection($in)). Allows for a little more concise and
 * simpler instantiation of a collection. Also I plan to eventually support
 * additional input types that will make this function more flexible and forgiving
 * than simply instantiating a Collection object, but for now the two are identical.
 *
 * @param array|Iterator $in Either an array or an iterator of data
 *
 * @return AbstractCollection A collection object containing data from $in
 *
 * @see AbstractCollection::__construct() (alias)
 */
function collect($in = null)
{
    return Collection::factory($in);
}

/**
 * Invoke a callable and return result.
 *
 * Pass in a callable followed by whatever arguments you want passed to
 * it and this function will invoke it with your arguments and return
 * the result.
 *
 * @param callable $callback The callback function to invoke
 * @param array ...$args The args to pass to your callable
 *
 * @return mixed The result of your invoked callable
 */
function invoke(callable $callback, ...$args)
{
    return $callback(...$args);
}

/**
 * Determine if data is traversable.
 *
 * Pass in any variable and this function will tell you whether or not it
 * is traversable. Basically this just means that it is either an array or an iterator.
 * This function was written simply because I was tired of if statements that checked
 * whether a variable was an array or a descendant of \Iterator. So I wrote this guy.
 *
 * @param mixed $input The variable to determine traversability
 *
 * @return bool True if $input is an array or an Iterator
 */
function is_traversable($input)
{
    return is_array($input) || $input instanceof Iterator;
}

/**
 * Dump and die.
 *
 * @param mixed $input Data to dump
 * @param bool  $exit  Should we exit after dump?
 * @param bool  $label Should we print a label?
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
                    function ($c) {
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
