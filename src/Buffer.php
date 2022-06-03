<?php

namespace Kekalainen\GameRQ;

class Buffer
{
    protected $data;
    protected $bigEndian;

    /**
     * @param string $binaryString
     * @param bool $bigEndian True for big endian byte order, false for machine byte order.
     */
    public function __construct(string $binaryString, bool $bigEndian = false)
    {
        $this->data = $binaryString;
        $this->bigEndian = $bigEndian;
    }

    /**
     * Gets a byte from the buffer
     */
    public function getByte(): int
    {
        $byte = substr($this->data, 0, 1);
        $this->data = substr($this->data, 1);
        return ord($byte);
    }

    /**
     * Gets a null-terminated string from the buffer
     * 
     * https://developer.valvesoftware.com/wiki/String
     */
    public function getString(): string
    {
        $nullTerminatorPos = strpos($this->data, "\x00");
        $str = substr($this->data, 0, $nullTerminatorPos);
        $this->data = substr($this->data, $nullTerminatorPos + 1);
        return $str;
    }

    /**
     * Gets a 16-bit short integer from the buffer
     */
    public function getShort(): int
    {
        $short = substr($this->data, 0, 2);
        $this->data = substr($this->data, 2);
        return unpack($this->bigEndian ? 'n' : 's', $short)[1];
    }

    /**
     * Gets a 32-bit long integer from the buffer
     */
    public function getLong(): int
    {
        $long = substr($this->data, 0, 4);
        $this->data = substr($this->data, 4);
        return unpack($this->bigEndian ? 'N' : 'l', $long)[1];
    }

    /**
     * Gets a 64-bit long long integer from the buffer
     */
    public function getLongLong(): int
    {
        $long = substr($this->data, 0, 8);
        $this->data = substr($this->data, 8);
        return unpack($this->bigEndian ? 'J' : 'Q', $long)[1];
    }
}
