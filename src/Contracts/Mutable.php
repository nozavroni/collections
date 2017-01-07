<?php
/**
 * Nozavroni/Collections.
 *
 * Just another collections library for PHP5.6+.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Contracts;

/**
 * Interface Mutable.
 */
interface Mutable
{
    /**
     * Is this object read-only?
     *
     * @return false
     */
    public function isReadOnly();
}