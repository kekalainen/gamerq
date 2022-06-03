<?php

namespace Kekalainen\GameRQ;

use \WSSC\WebSocketClient;
use \WSSC\Components\ClientConfig;
use \WSSC\Exceptions\ConnectionException;

class WebsocketRcon
{
    private $client;

    /**
     * @throws ConnectionException if the connection fails.
     */
    public function connect(string $address, int $port, string $password, int $timeout = 1): void
    {
        $config = new ClientConfig();
        $config->setTimeout($timeout);
        $this->client = new WebSocketClient("ws://{$address}:{$port}/{$password}", $config);
    }

    public function disconnect(): void
    {
        try {
            if ($this->client) {
                $this->client->close();
            }
        } catch (ConnectionException $e) {
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
        } catch (ConnectionException $e) {
        }

        return '';
    }
}
