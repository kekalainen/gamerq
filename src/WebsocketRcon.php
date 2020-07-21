<?php
namespace Kekalainen\GameRQ;

use \WSSC\WebSocketClient;
use \WSSC\Components\ClientConfig;
use \WSSC\Exceptions\ConnectionException;

class WebsocketRcon {
    private $client;

    public function connect($address, $port, $password, $timeout = 1) {
        $config = new ClientConfig();
        $config->setTimeout($timeout);
        $this->client = new WebSocketClient("ws://{$address}:{$port}/{$password}", $config);
    }

    public function disconnect() {
        try {
            if ($this->client) {
                $this->client->close();
            }
        } catch (ConnectionException $e) { }
    }

    public function command($command) {
        $identifier = rand(-1000, 1000);
        $this->client->send(json_encode([
            'Identifier' => $identifier,
            'Message' => $command,
            'Name' => 'WebRcon'
        ]));
        $message = null;
        try {
            while(true) {
                $response = json_decode($this->client->receive());
                if ($response->Identifier == $identifier) {
                    $message = $response->Message;
                    break;
                }
            }
        } catch (ConnectionException $e) { }
        return $message;
    }
}
