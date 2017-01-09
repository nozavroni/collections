<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2017 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Contracts\Structure;

use SplDoublyLinkedList;

interface Listable extends Collectable
{
    public function bump();
    public function drop();
    public function top();
    public function bottom();
    public function toArray();

//    /**
//     * Get SplDoublyLinkedList.
//     *
//     * @return SplDoublyLinkedList
//     */
//    public function toDll();
}
