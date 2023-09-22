<?php

namespace Kekalainen\GameRQ\Query;

use Kekalainen\GameRQ\Exceptions\ConnectionException;
use RangeException;

class MinecraftQuery extends SocketQuery
{
    protected const PROTOCOL_VERSION = 0x04;

    // Packet identifiers
    protected const PACKET_ID_HANDSHAKE = 0x00;
    protected const PACKET_ID_STATUS_REQUEST = 0x00;

    // Post-handshake states
    protected const STATE_STATUS = 0x01;

    public function connect(string $address, int $port, int $timeout = 1): void
    {
        parent::connect("tcp://$address", $port, $timeout);

        ConnectionException::wrap(function () use ($address, $port) {
            $this->handshake($address, $port);
        });
    }

    public function info(): array
    {
        $this->write(static::PACKET_ID_STATUS_REQUEST);

        $length = $this->readVarInt();
        $type = $this->readVarInt();

        $status = $this->readString();
        $status = json_decode($status);

        return [
            'name' => $status->version->name,
            'version' => $status->version->protocol,
            'motd' => $this->parseMessage($status->description),
            'favicon' => $status->favicon ?? null,
            'players' => $status->players->online,
            'maxplayers' => $status->players->max,
        ];
    }

    protected function handshake(string $address, int $port): void
    {
        $data = $this->packVarInt(static::PROTOCOL_VERSION) .
            $this->packString($address) .
            pack('n', $port) .
            $this->packVarInt(static::STATE_STATUS);

        $this->write(static::PACKET_ID_HANDSHAKE, $data);
    }

    /**
     * @return int|false
     */
    protected function write(int $id, string $data = '')
    {
        $idData = $this->packVarInt($id) . $data;
        $length = $this->packVarInt($innerLength = strlen($idData));
        $binaryString = $length . $idData;

        $packetLength = strlen($length) + $innerLength;

        return fwrite($this->socket, $binaryString, $packetLength);
    }

    protected function packString(string $payload): string
    {
        return pack('c', strlen($payload)) . $payload;
    }

    protected function readString(): string
    {
        $length = $this->readVarInt();

        $bytesRead = 0;
        $payload = '';

        while ($bytesRead < $length) {
            $segment = $this->read($length);

            $bytesRead += strlen($segment);
            $payload .= $segment;
        }

        return $payload;
    }

    /**
     * Packs a variable-length integer.
     */
    protected function packVarInt(int $payload): string
    {
        if (abs($payload) > 127)
            throw new RangeException('Packing multi-byte values is not implemented.');

        return pack('c', $payload);
    }

    /**
     * Reads a variable-length integer.
     */
    protected function readVarInt(): int
    {
        $byte = ord($this->read(1));
        $payload = $byte & 0x7F;
        $reads = 1;

        // The MSb indicates another byte of data.
        while ($byte & 0x80) {
            if ($reads >= 5)
                throw new RangeException('Value exceeds 5 bytes.');

            $byte = ord($this->read(1));
            $payload |= ($byte & 0x7F) << 7 * $reads;

            $reads += 1;
        }

        return $payload;
    }

    /**
     * Parses the given message object into a string.
     *
     * @param object $message
     */
    protected function parseMessage($message): string
    {
        $extras = array_map(static function ($element) {
            return $element->text;
        }, $message->extra ?? []);

        $text = $message->text;

        if (!empty($extras))
            $text .= (empty($text) ? '' : ' ') .
                implode(' ', $extras);

        return $text;
    }
}
