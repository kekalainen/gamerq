<?php

namespace Kekalainen\GameRQ\Rcon;

use Kekalainen\GameRQ\Exceptions\ConnectionException;
use WSSC\Components\ClientConfig;
use WSSC\Exceptions\ConnectionException as WSSCConnectionException;
use WSSC\WebSocketClient;

class WebSocketRcon implements RconInterface
{
    /** @var WebSocketClient|null */
    private $client;

    public function connect(string $address, int $port, string $password, int $timeout = 1): void
    {
        try {
            $config = new ClientConfig();
            $config->setTimeout($timeout);

            $this->client = new WebSocketClient("ws://{$address}:{$port}/{$password}", $config);
        } catch (WSSCConnectionException $exception) {
            throw new ConnectionException($exception->getMessage(), $exception->getCode());
        }
    }

    public function disconnect(): void
    {
        try {
            if ($this->client)
                $this->client->close();
        } catch (WSSCConnectionException $exception) {
        }
    }

    public function command(string $command): string
    {
        $identifier = rand(-1000, 1000);

        $this->client->send(json_encode([
            'Identifier' => $identifier,
            'Message' => $command,
            'Name' => 'WebRcon'
        ]));

        try {
            while (true) {
                $response = json_decode($this->client->receive());

                if ($response->Identifier == $identifier)
                    return $response->Message;
            }
        } catch (WSSCConnectionException $exception) {
        }

        return '';
    }
}
