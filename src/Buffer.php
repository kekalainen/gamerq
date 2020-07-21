<?php
namespace Kekalainen\GameRQ;

class Buffer {
    private $data;

    public function __construct($binaryString) {
        $this->data = $binaryString;
    }

    /**
     * Gets a byte from the buffer
     */
    public function getByte() {
        $byte = substr($this->data, 0, 1);
        $this->data = substr($this->data, 1);
        return ord($byte);
    }

    /**
     * Gets a null-terminated string from the buffer
     * 
     * https://developer.valvesoftware.com/wiki/String
     * 
     * @return string
     */
    public function getString() {
        $nullTerminatorPos = strpos($this->data, "\x00");
        $str = substr($this->data, 0, $nullTerminatorPos);
        $this->data = substr($this->data, $nullTerminatorPos + 1);
        return $str;
    }

    /**
     * Gets a 16-bit short integer from the buffer
     */
    public function getShort() {
        $short = substr($this->data, 0, 2);
        $this->data = substr($this->data, 2);
        return unpack('s', $short)[1];
    }

    /**
     * Gets a 32-bit long integer from the buffer
     */
    public function getLong() {
        $long = substr($this->data, 0, 4);
        $this->data = substr($this->data, 4);
        return unpack('l', $long)[1];
    }
    
    /**
     * Gets a 64-bit long long integer from the buffer
     */
    public function getLongLong() {
        $long = substr($this->data, 0, 8);
        $this->data = substr($this->data, 8);
        return unpack('q', $long)[1];
    }
}
