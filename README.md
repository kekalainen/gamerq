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
$rcon = new \Kekalainen\GameRQ\SourceRcon; // Source games
$rcon = new \Kekalainen\GameRQ\WebsocketRcon; // Rust

try {
    $rcon->connect($address, $port, $password);
    $response = $rcon->command('status');
    echo var_dump($response);
} catch (\Exception $e) {
    echo $e->getMessage();
} finally {
    $rcon->disconnect();
}
```

## Query
```php
$query = new \Kekalainen\GameRQ\SourceQuery; // Source games
$query = new \Kekalainen\GameRQ\GameSpy4Query; // Minecraft

try {
    $query->connect($address, $port);
    $info = $query->info();
    echo var_dump($info);
} catch (\Exception $e) {
    echo $e->getMessage();
} finally {
    $query->disconnect();
}
```
