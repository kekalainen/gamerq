<?php

namespace Kekalainen\GameRQ\Exceptions;

use Exception as BaseException;
use Throwable;

abstract class Exception extends BaseException
{
    /**
     * Executes the given callable and rethrows any throwable
     * as an instance of this exception class.
     *
     * @return mixed the value returned by the callable.
     * @throws static if the callable throws.
     */
    public static function wrap(callable $callable)
    {
        try {
            return $callable();
        } catch (Throwable $throwable) {
            throw new static(
                $throwable->getMessage(),
                (int) $throwable->getCode(),
                $throwable
            );
        }
    }
}
