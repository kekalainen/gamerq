# GameRQ - RCON & query library

A PHP library for querying various game servers and sending RCON commands to them.

# Supported games

This library should support all games that implement the [Source query](https://developer.valvesoftware.com/wiki/Server_queries) protocol, [Source RCON protocol](https://developer.valvesoftware.com/wiki/Source_RCON_Protocol), [GameSpy v4 / UT3 query protocol](https://wiki.unrealadmin.org/UT3_query_protocol) or [webrcon](https://github.com/Facepunch/webrcon). Not all protocol features are implemented. Below is an incomplete table of supported games.

| Game | RCON | Query |
| ---- | :--: | :---: |
| [Garry's Mod](https://gmod.facepunch.com) | ✅ | ✅ |
| [Minecraft](https://minecraft.net) | ✅ | ✅ |
| [Rust](https://rust.facepunch.com) | ✅ | ✅ |

# Usage examples

## RCON

```php
$rcon = new \Kekalainen\GameRQ\Rcon\SourceRcon; // Source games & Minecraft
$rcon = new \Kekalainen\GameRQ\Rcon\WebsocketRcon; // Rust

try {
    $rcon->connect($address, $port, $password);

    $response = $rcon->command('status');
    echo var_dump($response);
} catch (\Exception $exception) {
    echo $exception->getMessage();
} finally {
    $rcon->disconnect();
}
```

## Query

```php
$query = new \Kekalainen\GameRQ\Query\SourceQuery; // Source games
$query = new \Kekalainen\GameRQ\Query\MinecraftQuery; // Minecraft (TCP)
$query = new \Kekalainen\GameRQ\Query\GameSpy4Query; // Minecraft (UDP)

try {
    $query->connect($address, $port);

    $info = $query->info();
    echo var_dump($info);
} catch (\Exception $exception) {
    echo $exception->getMessage();
} finally {
    $query->disconnect();
}
```
