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
namespace Noz\Traits;

trait IsMutable
{
    protected $mutable = true;

    public function isReadOnly()
    {
        return !$this->mutable;
    }
}
