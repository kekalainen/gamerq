<?php

namespace Kekalainen\GameRQ;

use OutOfBoundsException;

class Buffer
{
    protected const NULL_TERMINATOR = "\x00";

    /**
     * The buffered data.
     *
     * @var string
     */
    protected $data;

    /**
     * The length of the buffered data.
     *
     * @var int
     */
    protected $length;

    /**
     * The current offset into the buffered data.
     *
     * @var int
     */
    protected $offset;

    /**
     * Whether the byte order of the buffered data is big-endian.
     *
     * @var bool
     */
    protected $bigEndian;

    /**
     * @param string $binaryString
     * @param bool $bigEndian True for big endian byte order, false for machine byte order.
     */
    public function __construct(string $binaryString, bool $bigEndian = false)
    {
        $this->offset = 0;
        $this->data = $binaryString;
        $this->length = strlen($this->data);
        $this->bigEndian = $bigEndian;
    }

    /**
     * Gets a byte from the buffer.
     */
    public function getByte(): int
    {
        $char = $this->getChars(1);

        return ord($char);
    }

    /**
     * Gets a null-terminated string from the buffer.
     * 
     * https://developer.valvesoftware.com/wiki/String
     */
    public function getString(): string
    {
        $nullTerminatorPos = strpos($this->data, static::NULL_TERMINATOR, $this->offset);
        $chars = $this->getChars($nullTerminatorPos + 1 - $this->offset);

        return rtrim($chars, static::NULL_TERMINATOR);
    }

    /**
     * Gets a 16-bit short integer from the buffer.
     */
    public function getShort(): int
    {
        return $this->getUnpacked(2, 'n', 's');
    }

    /**
     * Gets a 32-bit long integer from the buffer.
     */
    public function getLong(): int
    {
        return $this->getUnpacked(4, 'N', 'l');
    }

    /**
     * Gets a 64-bit long long integer from the buffer.
     */
    public function getLongLong(): int
    {
        return $this->getUnpacked(8, 'J', 'Q');
    }

    /**
     * Gets a sequence of characters from the buffer.
     */
    protected function getChars(int $length): string
    {
        $end = $this->offset + $length;

        if ($end < 0 || $this->length < $end)
            throw new OutOfBoundsException('Buffer index out of bounds.');

        $chars = substr($this->data, $this->offset, $length);
        $this->offset = $end;

        return $chars;
    }

    /**
     * Gets and unpacks chars from the buffer.
     *
     * @return mixed the (first) unpacked element.
     */
    protected function getUnpacked(int $length, string $formatBigEndian, string $formatLittleEndian)
    {
        $chars = $this->getChars($length);

        $format = $this->bigEndian ?
            $formatBigEndian :
            $formatLittleEndian;

        return unpack($format, $chars)[1];
    }
}
