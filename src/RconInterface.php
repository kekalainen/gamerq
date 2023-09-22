<?php

namespace Kekalainen\GameRQ;

use Kekalainen\GameRQ\Exceptions\ConnectionException;

interface RconInterface
{
    /**
     * Opens an RCON connection to the given server.
     *
     * @throws ConnectionException if the connection fails.
     */
    public function connect(string $address, int $port, string $password, int $timeout = 1): void;


    /**
     * Closes the RCON connection, if open.
     */
    public function disconnect(): void;

    /**
     * Executes the given command on the connected server.
     */
    function command(string $command): string;
}
