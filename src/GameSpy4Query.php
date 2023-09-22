<?php

namespace Kekalainen\GameRQ;

class GameSpy4Query extends SocketQuery
{
    /** @var int */
    protected $sessionid;

    /** @var string|null */
    protected $token;

    const CHALLENGE = 0x09;
    const INFORMATION = 0x00;

    public function __construct()
    {
        $this->sessionid = rand(1, 100);
    }

    public function connect(string $address, int $port, int $timeout = 1): void
    {
        parent::connect("udp://$address", $port, $timeout);
        $this->handshake();
    }

    /**
     * @return int|false
     */
    public function request(int $type)
    {
        $binaryString = pack('cccN', 0xFE, 0xFD, $type, $this->sessionid);
        if ($this->token) $binaryString .= $this->token;
        return fwrite($this->socket, $binaryString, strlen($binaryString));
    }

    public function handshake(): void
    {
        $this->request(self::CHALLENGE);
        $buffer = new Buffer($this->read(2056), true);
        $type = $buffer->getByte();
        $sessionid = $buffer->getLong();
        $challengeToken = $buffer->getString();
        $this->token = pack('N', $challengeToken);
    }

    public function info(): array
    {
        $this->request(self::INFORMATION);
        $buffer = new Buffer($this->read(), true);

        return [
            'type' => $buffer->getByte(),
            'sessionid' => $buffer->getLong(),
            'motd' => $buffer->getString(),
            'gametype' => $buffer->getString(),
            'map' => $buffer->getString(),
            'players' => (int) $buffer->getString(),
            'maxplayers' => (int) $buffer->getString(),
            'hostport' => $buffer->getShort(),
            'hostip' => $buffer->getString()
        ];
    }
}
