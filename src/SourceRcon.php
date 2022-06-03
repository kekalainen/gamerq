<?php

namespace Kekalainen\GameRQ;

class SourceRcon
{
    private $socket;

    const SERVERDATA_AUTH = 3;
    const SERVERDATA_AUTH_RESPONSE = 2;
    const SERVERDATA_EXECCOMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;

    public function connect($address, $port, $password, $timeout = 1)
    {
        $socket = @fsockopen($address, $port, $errno, $errstr, $timeout);
        if ($socket) {
            stream_set_timeout($socket, $timeout);
            $this->socket = $socket;
            $this->auth($password);
        } else {
            throw new \Exception('Connection failed: ' . $errstr);
        }
    }

    public function disconnect()
    {
        @fclose($this->socket);
    }

    public function write($type, $body)
    {
        $size = 10 + strlen($body);
        $id = rand(1, 100);
        $binaryString = pack('VVVa*a', $size, $id, $type, "$body\x00", "\x00");
        try {
            return fwrite($this->socket, $binaryString, strlen($binaryString));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function read()
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

    public function auth($password)
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

    function command($command)
    {
        $this->write(self::SERVERDATA_EXECCOMMAND, $command);

        $data = $this->read();
        $body = $data['body'];

        // https://developer.valvesoftware.com/wiki/Source_RCON_Protocol#Multiple-packet_Responses
        if ($data['size'] > 4040) {
            $this->write(self::SERVERDATA_RESPONSE_VALUE, null);

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
