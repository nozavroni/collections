<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Collection;

use function Noz\is_traversable;

class MultiCollection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function contains($value, $index = null)
    {
        if (parent::contains($value, $index)) {
            return true;
        }
        foreach ($this->data as $key => $arr) {
            if (is_traversable($arr)) {
                $coll = static::factory($arr);
                if ($coll->contains($value, $index)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    protected function isConsistentDataStructure($data)
    {
        return static::isMultiDimensional($data);
    }
}
