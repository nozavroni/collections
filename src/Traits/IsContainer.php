<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2017 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */

namespace Noz\Traits;

trait IsContainer
{

    /**
     * Determine if this collection contains a value.
     *
     * Allows you to pass in a value or a callback function and optionally an index,
     * and tells you whether or not this collection contains that value.
     * If the $index param is specified, only that index will be looked under.
     *
     * @param mixed|callable $value The value to check for
     * @param mixed          $index The (optional) index to look under
     *
     * @return bool True if this collection contains $value
     *
     * @todo Maybe add $identical param for identical comparison (===)
     * @todo Allow negative offset for second param
     */
    public function contains($value, $index = null)
    {
        return (bool) $this->fold(function ($carry, $val, $key) use ($value, $index) {
            if ($carry) {
                return $carry;
            }
            if (is_callable($value)) {
                $found = $value($val, $key);
            } else {
                $found = ($value == $val);
            }
            if ($found) {
                if (is_null($index)) {
                    return true;
                }
                if (is_array($index)) {
                    return in_array($key, $index);
                }

                return $key == $index;
            }

            return false;
        });
    }

    abstract public function fold(callable $funk, $initial = null);

}