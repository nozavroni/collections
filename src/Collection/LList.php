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

use InvalidArgumentException;

use Countable;
use Traversable;
use SplDoublyLinkedList;

use Noz\Contracts\Structure\Listable;
use Noz\Contracts\Immutable;
use Noz\Contracts\Arrayable;
use Noz\Contracts\Invokable;

use Noz\Traits\IsArrayable;
use Noz\Traits\IsImmutable;

use function
    Noz\to_array,
    Noz\is_traversable;

class LList implements
    Listable,
    Immutable,
    Countable,
    Arrayable,
    Invokable
{
    use IsImmutable, IsArrayable;

    /**
     * @var SplDoublyLinkedList
     */
    private $data;

    /**
     * LList constructor.
     *
     * @param array|Traversable $data The list constructor
     */
    public function __construct($data)
    {
        $this->setData($data);
    }

    /**
     * Set internal data array.
     *
     * @param array|Traversable $data The list constructor
     */
    private function setData($data)
    {
        if (!is_traversable($data)) {
            throw new InvalidArgumentException(
                '%s expects traversable data.',
                __CLASS__
            );
        }
        $dll = new SplDoublyLinkedList($data);
        $dll->setIteratorMode(SplDoublyLinkedList::IT_MODE_KEEP);
        foreach ($data as $key => $val) {
            $dll->push($val);
        }
        $this->data = $dll;
    }

    /**
     * Get internal data array.
     *
     * @return array
     */
    protected function getData()
    {
        return to_array($this->data);
    }

    /**
     * Invoke LList.
     *
     * This method is called when a LList object is invoked (called as if it were a function).
     *
     * @return mixed
     */
    public function __invoke()
    {

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
