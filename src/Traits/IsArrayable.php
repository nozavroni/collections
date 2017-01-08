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

use function
    Noz\is_arrayable,
    Noz\to_array;

trait IsArrayable
{
    /**
     * Get object as array.
     *
     * @return array This object as an array
     */
    public function toArray()
    {
        $arr = [];
        foreach ($this->getData() as $index => $value) {
            if (is_arrayable($value)) {
                $value = to_array($value);
            }
            $arr[$index] = $value;
        }

        return $arr;
    }

    /**
     * Get internal data array.
     *
     * Because collections are immutable, direct access to the underlying data array is discouraged, even from within
     * the collection class itself. Use $this->getData() instead.

     * @return array
     */
    abstract protected function getData();

}