<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace Noz\Contracts;

/**
 * InvokableInterface.
 *
 * Ensures a class's instances are invokable. That is, you can call an instance of the class just as if it were a
 * function, by calling call_user_func* on it or invoking it directly using parenthesis.
 *
 * @package Noz\Contracts
 */
interface Invokable
{
    public function __invoke();
}
