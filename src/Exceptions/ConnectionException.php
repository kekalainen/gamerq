<?php

namespace Kekalainen\GameRQ\Exceptions;

use Throwable;

class ConnectionException extends Exception
{
    public function __construct(string $reason = '', int $code = 0, ?Throwable $previous = null)
    {
        $message = 'Connection failed.';

        if (!empty($reason)) $message .= " Reason: \"$reason\".";

        parent::__construct($message, $code, $previous);
    }
}
