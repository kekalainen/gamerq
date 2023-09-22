<?php

namespace Kekalainen\GameRQ;

use Kekalainen\GameRQ\Exceptions\ConnectionException;

interface QueryInterface
{
    /**
     * Opens a connection for querying the given server.
     *
     * @throws ConnectionException if the connection fails.
     */
    public function connect(string $address, int $port, int $timeout = 1): void;

    /**
     * Closes the query connection, if open.
     */
    public function disconnect(): void;

    /**
     * Retrieves information about the connected server.
     */
    public function info(): array;
}
