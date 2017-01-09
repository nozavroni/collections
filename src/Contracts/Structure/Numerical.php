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

interface Numerical
{
    public function max();
    public function min();
    public function avg();
    public function mode();
    public function med();
    public function inc($index, $intvl = 1);
    public function dec($index, $intvl = 1);
    public function sum();
    public function count();
}
