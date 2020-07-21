<?php
namespace Kekalainen\GameRQ;

class GameSpy4Query extends SourceQuery {
    protected $sessionid;
    protected $token;

    const CHALLENGE = 0x09;
    const INFORMATION = 0x00;

    public function __construct() {
        $this->sessionid = rand(1, 100);
    }

    public function connect($address, $port, $timeout = 1) {
        parent::connect($address, $port, $timeout);
        $this->handshake();
    }

    public function request($type) {
        $binaryString = pack('cccN', 0xFE, 0xFD, $type, $this->sessionid);
        if ($this->token != null) {
            $binaryString .= $this->token;
        }
        return fwrite($this->socket, $binaryString, strlen($binaryString));
    }

    public function handshake() {
        $this->request(self::CHALLENGE);
        $buffer = new Buffer($this->read(2056));
        $type = $buffer->getByte();
        $sessionid = $buffer->getLong();
        $challengeToken = $buffer->getString();
        $this->token = pack('N', $challengeToken);
    }

    public function info() {
        $this->request(SELF::INFORMATION);
        $buffer = new Buffer($this->read());
        return [
            'type' => $buffer->getByte(),
            'sessionid' => $buffer->getLong(),
            'motd' => $buffer->getString(),
            'gametype' => $buffer->getString(),
            'map' => $buffer->getString(),
            'numplayers' => $buffer->getString(),
            'maxplayers' => $buffer->getString(),
            'hostport' => $buffer->getShort(),
            'hostip' => $buffer->getString()
        ];
    }
}