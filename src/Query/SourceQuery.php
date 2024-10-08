<?php

namespace Kekalainen\GameRQ\Query;

use Exception;
use Kekalainen\GameRQ\Buffer;

class SourceQuery extends SocketQuery
{
    // Headers
    const A2S_INFO = 0x54;
    const S2A_INFO_SRC = 0x49;
    const S2C_CHALLENGE = 0x41;

    // Extra data flags
    const EDF_PORT = 0x80;
    const EDF_STEAMID = 0x10;
    const EDF_SOURCETV = 0x40;
    const EDF_KEYWORDS = 0x20;
    const EDF_GAMEID = 0x01;

    public function connect(string $address, int $port, int $timeout = 1): void
    {
        parent::connect("udp://$address", $port, $timeout);
    }

    /**
     * @return int|false
     */
    public function write(int $header, string $body, string $challenge = null)
    {
        // Request format: 5 bytes (unsigned chars) followed by a null-terminated string.
        $binaryString = pack('CCCCCa*', 0xFF, 0xFF, 0xFF, 0xFF, $header, "$body\x00");

        if ($challenge) $binaryString .= $challenge;

        return fwrite($this->socket, $binaryString, strlen($binaryString));
    }

    /**
     * Retrieves information about the server including, but not limited to: its name, the map currently being played, and the number of players.
     * 
     * https://developer.valvesoftware.com/wiki/Server_queries#A2S_INFO
     */
    public function info(): array
    {
        $this->write(self::A2S_INFO, 'Source Engine Query');
        $buffer = new Buffer(substr($this->read(), 4));

        $info = [];

        $info['header'] = $buffer->getByte();

        if ($info['header'] !== self::S2A_INFO_SRC) {
            if ($info['header'] === self::S2C_CHALLENGE) {
                $challenge = pack('l', $buffer->getLong());
                $this->write(self::A2S_INFO, 'Source Engine Query', $challenge);
                $buffer = new Buffer(substr($this->read(), 4));
                $info['header'] = $buffer->getByte();
            }

            if ($info['header'] !== self::S2A_INFO_SRC)
                throw new Exception('Unrecognized response header.');
        }

        $info['protocol'] = $buffer->getByte();

        $info['name'] = $buffer->getString();
        $info['map'] = $buffer->getString();
        $info['folder'] = $buffer->getString();
        $info['game'] = $buffer->getString();

        $info['id'] = $buffer->getShort();
        $info['players'] = $buffer->getByte();
        $info['maxplayers'] = $buffer->getByte();
        $info['bots'] = $buffer->getByte();
        $info['type'] = $buffer->getByte();
        $info['environment'] = $buffer->getByte();
        $info['visibility'] = $buffer->getByte();
        $info['vac'] = $buffer->getByte();
        $info['version'] = $buffer->getString();
        $info['edf'] = $buffer->getByte();

        $edf = $info['edf'];

        if ($edf & self::EDF_PORT)
            $info['port'] = $buffer->getShort();

        if ($edf & self::EDF_STEAMID)
            $info['steamid'] = $buffer->getLongLong();

        if ($edf & self::EDF_SOURCETV) {
            $info['sourcetv_port'] = $buffer->getShort();
            $info['sourcetv_name'] = $buffer->getString();
        }

        if ($edf & self::EDF_KEYWORDS) {
            $info['keywords'] = explode(',', $buffer->getString());

            if ($info['game'] === 'Rust') {
                foreach ($info['keywords'] as $keyword) {
                    if (substr($keyword, 0, 2) == 'mp') {
                        $info['maxplayers'] = intval(substr($keyword, 2));
                    } else if (substr($keyword, 0, 2) == 'cp') {
                        $info['players'] = intval(substr($keyword, 2));
                    }
                }
            }
        }

        if ($edf & self::EDF_GAMEID)
            $info['gameid'] = $buffer->getLongLong();

        return $info;
    }
}
