<?php

namespace Kekalainen\GameRQ\Exceptions;

class ConnectionException extends Exception
{
    public function __construct($message, $code, ...$params)
    {
        $newMessage = 'Connection failed.';
        if (!empty($code)) $newMessage .= " Code: $code.";
        if (!empty($message)) $newMessage .= " Message: \"$message\".";

        parent::__construct($newMessage, ...$params);
    }
}
