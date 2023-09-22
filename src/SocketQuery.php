<?php

namespace Kekalainen\GameRQ;

use Kekalainen\GameRQ\Exceptions\ConnectionException;

abstract class SocketQuery implements QueryInterface
{
    /**
     * @var resource|false|null
     */
    protected $socket;

    public function connect(string $address, int $port, int $timeout = 1): void
    {
        $socket = @fsockopen($address, $port, $errorCode, $errorMessage, $timeout);

        if ($socket) {
            stream_set_timeout($socket, $timeout);
            $this->socket = $socket;
        } else {
            throw new ConnectionException($errorMessage, $errorCode);
        }
    }

    public function disconnect(): void
    {
        if (is_resource($this->socket))
            @fclose($this->socket);
    }

    public function read(int $length = 1400): string
    {
        $binaryString = fread($this->socket, $length);

        if ($binaryString === false)
            throw new ConnectionException('Failed to read from socket.', null);

        return $binaryString;
    }
}
