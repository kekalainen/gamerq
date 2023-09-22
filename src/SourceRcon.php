<?php

namespace Kekalainen\GameRQ;

use Kekalainen\GameRQ\Exceptions\ConnectionException;

class SourceRcon implements RconInterface
{
    private $socket;

    const SERVERDATA_AUTH = 3;
    const SERVERDATA_AUTH_RESPONSE = 2;
    const SERVERDATA_EXECCOMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;

    public function connect(string $address, int $port, string $password, int $timeout = 1): void
    {
        $socket = @fsockopen($address, $port, $errorCode, $errorMessage, $timeout);
        if ($socket) {
            stream_set_timeout($socket, $timeout);
            $this->socket = $socket;
            $this->auth($password);
        } else {
            throw new ConnectionException($errorMessage, $errorCode);
        }
    }

    public function disconnect(): void
    {
        @fclose($this->socket);
    }

    /**
     * @return int|false
     */
    public function write(int $type, string $body = null)
    {
        $size = 10;
        if ($body) $size += strlen($body);
        $id = rand(1, 100);
        $binaryString = pack('VVVa*a', $size, $id, $type, "$body\x00", "\x00");
        try {
            return fwrite($this->socket, $binaryString, strlen($binaryString));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function read(): array
    {
        $size = (new Buffer(fread($this->socket, 4)))->getLong();
        $data = fread($this->socket, $size);
        $buffer = new Buffer($data);
        return [
            'size' => $size,
            'id' => $buffer->getLong(),
            'type' => $buffer->getLong(),
            'body' => $buffer->getString()
        ];
    }

    public function auth(string $password): void
    {
        $this->write(self::SERVERDATA_AUTH, $password);
        $data = $this->read();

        if ($data['type'] == self::SERVERDATA_RESPONSE_VALUE) {
            $data = $this->read();
        }

        if ($data['id'] == -1 || $data['type'] != self::SERVERDATA_AUTH_RESPONSE) {
            throw new \Exception('Unauthenticated');
        }
    }

    function command(string $command): string
    {
        $this->write(self::SERVERDATA_EXECCOMMAND, $command);

        $data = $this->read();
        $body = (string) $data['body'];

        // https://developer.valvesoftware.com/wiki/Source_RCON_Protocol#Multiple-packet_Responses
        if ($data['size'] > 4040) {
            $this->write(self::SERVERDATA_RESPONSE_VALUE);

            while (true) {
                $data = $this->read();
                if ($data['type'] == self::SERVERDATA_RESPONSE_VALUE && $data['body'] == '') {
                    $this->read();
                    break;
                }
                $body .= $data['body'];
            }
        }

        return $body;
    }
}
