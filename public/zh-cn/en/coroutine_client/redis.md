# Coroutine Redis Client

!> This client is no longer recommended for use. It is recommended to use `Swoole\Runtime::enableCoroutine + phpredis` or `predis` to achieve coroutine support. More information can be found [here](/runtime) on how to make native `PHP` `redis` client coroutine-aware.
## Usage Example

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
});
```

!> `subscribe` and `pSubscribe` cannot be used in scenarios with `defer(true)`.
## Methods

!> The usage of methods is basically consistent with [phpredis](https://github.com/phpredis/phpredis).

The following explanations are different from the implementation of [phpredis](https://github.com/phpredis/phpredis):

1. Redis commands not yet implemented: `scan object sort migrate hscan sscan zscan`;

2. The usage of `subscribe pSubscribe`, no need to set a callback function;

3. Support for serializing PHP variables: set the third parameter of the `connect()` method to `true` to enable the serialization of PHP variables, default is `false`.
### __construct()

Constructor for the Redis coroutine client, which can set configuration options for the `Redis` connection, consistent with the parameters of the `setOptions()` method.

```php
Swoole\Coroutine\Redis::__construct(array $options = null);
```
### setOptions()

This method was added after version 4.2.10, used to set some configurations of the `Redis` client after construction and connection.

This function is in the style of Swoole and needs to be configured through a `Key-Value` key-value pair array.

```php
Swoole\Coroutine\Redis->setOptions(array $options): void
```

  * **Configurable Options**

Key | Description
---|---
`connect_timeout` | Connection timeout, default is the global coroutine `socket_connect_timeout` (1 second).
`timeout` | Timeout, default is the global coroutine `socket_timeout`, refer to [client timeout rules](/coroutine_client/init?id=timeout-rules).
`serialize` | Automatic serialization, default is off.
`reconnect` | Number of automatic connection attempts. If the connection is normally closed due to timeout or other reasons and a request is made next time, it will automatically attempt to reconnect before sending the request. Default is `1` attempt (`true`). After a specified number of failures, it will not continue to attempt and manual reconnection is required. This mechanism is only used for connection keep-alive and will not resend requests leading to errors on non-idempotent interfaces.
`compatibility_mode` | Inconsistency solution for `hmGet/hGetAll/zRange/zRevRange/zRangeByScore/zRevRangeByScore` function results compared to `php-redis`. After enabling, `Co\Redis` and `php-redis` return results consistently. Default is off. [This configuration is available in `v4.4.0` or later versions]
### set()

Store data.

```php
Swoole\Coroutine\Redis->set(string $key, mixed $value, array|int $option): bool
```

  * **Parameters** 

    * **`string $key`**
      * **Functionality**: Key of the data
      * **Default Value**: None
      * **Other Values**: None

    * **`mixed $value`**
      * **Functionality**: Data content [non-string types will be automatically serialized]
      * **Default Value**: None
      * **Other Values**: None

    * **`array|int $options`**
      * **Functionality**: Options
      * **Default Value**: None
      * **Other Values**: None

      !> Explanation of `$option`:  
      `int`: Sets the expiration time, e.g., `3600`  
      `array`: Advanced expiration settings, e.g., `['nx', 'ex' => 10]` or `['xx', 'px' => 1000]`

      !> `px`: Represents expiration time in milliseconds  
      `ex`: Represents expiration time in seconds  
      `nx`: Sets the timeout if the key does not exist  
      `xx`: Sets the timeout if the key exists
### request()

Sends a custom command to the Redis server. Similar to `rawCommand` in phpredis.

```php
Swoole\Coroutine\Redis->request(array $args): void
```

  * **Parameters**

    * **`array $args`**
      * **Description**: List of arguments, must be in array format.【The first element must be the `Redis` command, the other elements are the command's parameters, which will be automatically packaged into a `Redis` protocol request for transmission.】
      * **Default**: None
      * **Other values**: None

  * **Return Value**

Depends on how the `Redis` server handles the command. It may return a number, boolean, string, array, or other types.

  * **Usage Example**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379); // If using a local UNIX Socket, the host parameter should be in the format like `unix://tmp/your_file.sock`
    $res = $redis->request(['object', 'encoding', 'key1']);
    var_dump($res);
});
```
## Attributes
### errCode

Error code.

Error Code | Description
---|---
1 | Error in read or write
2 | Everything else...
3 | End of file
4 | Protocol error
5 | Out of memory
### errMsg

Error message.
### connected

Check if the current `Redis` client has connected to the server.
## Constants

Used for the `multi($mode)` method, with the default mode being `SWOOLE_REDIS_MODE_MULTI`:

* SWOOLE_REDIS_MODE_MULTI
* SWOOLE_REDIS_MODE_PIPELINE

Used to determine the return value of the `type()` command:

* SWOOLE_REDIS_TYPE_NOT_FOUND
* SWOOLE_REDIS_TYPE_STRING
* SWOOLE_REDIS_TYPE_SET
* SWOOLE_REDIS_TYPE_LIST
* SWOOLE_REDIS_TYPE_ZSET
* SWOOLE_REDIS_TYPE_HASH
## Transaction Mode

The transaction mode of Redis can be implemented using `multi` and `exec`.

  * **Tips**

    * Use the `multi` command to start a transaction, then all commands will be queued for execution.
    * Use the `exec` command to execute all operations in the transaction and return all results at once.

  * **Example**

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->multi();
    $redis->set('key3', 'rango');
    $redis->get('key1');
    $redis->get('key2');
    $redis->get('key3');

    $result = $redis->exec();
    var_dump($result);
});
```
## Subscription Mode

!> Available in Swoole version >= v4.2.13. **There is a BUG in subscription mode in versions 4.2.12 and earlier**
### Subscription

Different from `phpredis`, `subscribe/psubscribe` in coroutine style.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // or use psubscribe
    {
        while ($msg = $redis->recv()) {
            // $msg is an array, containing the following information
            // $type # type of the return value: subscription success notification
            // $name # name of the channel subscribed to or the source channel name
            // $info  # current number of subscribed channels or information content
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') { // or psubscribe
                // Subscription success message, one for each channel subscribed to
            } else if ($type == 'unsubscribe' && $info == 0){ // or punsubscribe
                break; // Received unsubscribe message and the remaining number of subscribed channels is 0, stop receiving and exit the loop
            } else if ($type == 'message') {  // If psubscribe, then this is pmessage
                var_dump($name); // Print the source channel name
                var_dump($info); // Print the message
                // balabalaba.... // Handle the message
                if ($need_unsubscribe) { // Unsubscribe under certain circumstances
                    $redis->unsubscribe(); // Continue to recv and wait for the unsubscription to complete
                }
            }
        }
    }
});
```
### Unsubscribe

To unsubscribe, use `unsubscribe/punsubscribe`, `$redis->unsubscribe(['channel1'])`

At this point, `$redis->recv()` will receive an unsubscription message. If multiple channels are unsubscribed, multiple messages will be received.

!> Note: After unsubscribing, make sure to continue `recv()` until the last unsubscription message is received (`$msg[2] == 0`). Only after receiving this message will it exit the subscription mode.

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if ($redis->subscribe(['channel1', 'channel2', 'channel3'])) // or use psubscribe
    {
        while ($msg = $redis->recv()) {
            // msg is an array containing the following information
            // $type # return type: show subscription success
            // $name # subscribed channel name or source channel name
            // $info  # the number of channels or information content currently subscribed
            list($type, $name, $info) = $msg;
            if ($type == 'subscribe') // or psubscribe
            {
                // channel subscription success message
            }
            else if ($type == 'unsubscribe' && $info == 0) // or punsubscribe
            {
                break; // received the unsubscribe message, and the number of channels remaining for the subscription is 0, no longer received, break the loop
            }
            else if ($type == 'message') // if it's psubscribe，here is pmessage
            {
                // print source channel name
                var_dump($name);
                // print message
                var_dump($info);
                // handle message
                if ($need_unsubscribe) // in some cases, you need to unsubscribe
                {
                    $redis->unsubscribe(); // continue recv to wait unsubscribe finished
                }
            }
        }
    }
});
```
## Compatibility Mode

The issue of inconsistent result formats returned by `Co\Redis` instructions `hmGet/hGetAll/zrange/zrevrange/zrangebyscore/zrevrangebyscore` compared to the `phpredis` extension has been resolved [#2529](https://github.com/swoole/swoole-src/pull/2529).

To ensure consistency between the return results of `Co\Redis` and `phpredis` for compatibility with older versions, add `$redis->setOptions(['compatibility_mode' => true]);` configuration, after which the results will be consistent.

!> Available in Swoole version `v4.4.0` and above

```php
use Swoole\Coroutine\Redis;
use function Swoole\Coroutine\run;

run(function () {
    $redis = new Redis();
    $redis->setOptions(['compatibility_mode' => true]);
    $redis->connect('127.0.0.1', 6379);

    $co_get_val = $redis->get('novalue');
    $co_zrank_val = $redis->zRank('novalue', 1);
    $co_hgetall_val = $redis->hGetAll('hkey');
    $co_hmget_val = $redis->hmGet('hkey', array(3, 5));
    $co_zrange_val = $redis->zRange('zkey', 0, 99, true);
    $co_zrevrange_val = $redis->zRevRange('zkey', 0, 99, true);
    $co_zrangebyscore_val = $redis->zRangeByScore('zkey', 0, 99, ['withscores' => true]);
    $co_zrevrangebyscore_val = $redis->zRevRangeByScore('zkey', 99, 0, ['withscores' => true]);
});
```
