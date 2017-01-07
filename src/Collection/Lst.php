<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2017 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Collection;

use BadMethodCallException;

use Countable;
use Noz\Contracts\Structure\Listable;
use Traversable;
use SplDoublyLinkedList;

use Noz\Contracts\Structure\Sequenceable;
use Noz\Contracts\Immutable;
use Noz\Contracts\Arrayable;
use Noz\Contracts\Invokable;

use Noz\Traits\IsImmutable;

use function Noz\to_array;
use function Noz\is_traversable;

class Lst implements
    Listable,
    Immutable,
    Countable,
    Arrayable,
    Invokable
{
    use IsImmutable;

    private $data;

    public function __construct($data)
    {
        $data = array_values(to_array($data));
        $this->data = new SplDoublyLinkedList();
        $this->data->setIteratorMode(SplDoublyLinkedList::IT_MODE_KEEP);
        foreach ($data as $key => $val) {
            $this->data->add($key, $val);
        }
    }

    /**
     * To array.
     *
     * @return array
     */
    public function toArray()
    {
        return to_array($this->data);
    }

    /**
     * Invoke set.
     *
     * @return mixed
     */
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    /**
     * Count elements of an object
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

}