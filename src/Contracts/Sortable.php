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

interface Sortable
{
    /**
     * Return new collection sorted using specified algorithm..
     *
     * @param string|null $alg Sorting algorithm
     *
     * @return CollectionInterface
     */
    public function sort($alg = null);

    /**
     * Return new collection sorted by key using specified algorithm..
     *
     * @param string|null $alg Sorting algorithm
     *
     * @return CollectionInterface
     */
    public function sortKeys($alg = null);
}
