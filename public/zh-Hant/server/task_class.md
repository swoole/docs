# Swoole\Server\Task

這裡是對`Swoole\Server\Task`的詳細介紹。這個類很簡單，但是你也無法通過`new Swoole\Server\Task()`來獲得一個`Task`物件，這種物件完全不包含任何服務端的信息，並且你執行`Swoole\Server\Task`任意的方法都會有一個致命錯誤。

```shell
Invalid instance of Swoole\Server\Task in /home/task.php on line 3
```

## 屬性

### $data
`worker`進程傳遞給`task`進程的數據`data`，該屬性是一個`string`類型的字符串。

```php
Swoole\Server\Task->data
```

### $dispatch_time
返回該數據到達`task`進程的時間`dispatch_time`，該屬性是一個`double`類型。

```php
Swoole\Server\Task->dispatch_time
```

### $id
返回該數據到達`task`進程的時間`dispatch_time`，該屬性是一個`int`類型的整數。

```php
Swoole\Server\Task->id
```

### $worker_id
返回該數據來自哪一個`worker`進程，該屬性是一個`int`類型的整數。

```php
Swoole\Server\Task->worker_id
```

### $flags
該異步任務的一些標誌位信息`flags`，該屬性是一個`int`類型的整數。

```php
Swoole\Server\Task->flags
```

?> `flags`返回的結果是以下幾種類型：  
  - SWOOLE_TASK_NOREPLY | SWOOLE_TASK_NONBLOCK 表示這不是`Worker`進程發送給`task`進程的，此時如果在`onTask`事件中調用`Swoole\Server::finish()`的話，將會有一個警告發出。  
  - SWOOLE_TASK_CALLBACK | SWOOLE_TASK_NONBLOCK 表示`Swoole\Server::finish()`中最後一個回調函數不是null，`onFinish`事件將不會執行，而只會執行這個回調函數。 
  - SWOOLE_TASK_COROUTINE | SWOOLE_TASK_NONBLOCK 表示將會通過協程的形式處理任務。 
  - SW_TASK_NONBLOCK 默认值，當以上三種情況都沒有的時候。

## 方法

### finish()

用於在 [Task進程](/learn?id=taskworker進程)中通知`Worker`進程，投遞的任務已完成。此函數可以傳遞結果數據給`Worker`進程。

```php
Swoole\Server\Task->finish(mixed $data): bool
```

  * **參數**

    * `mixed $data`

      * 功能：任務處理的結果內容
      * 默认值：無
      * 其它值：無

  * **提示**
    * `finish`方法可以連續多次調用，`Worker`進程會多次觸發[onFinish](/server/events?id=onfinish)事件
    * 在[onTask](/server/events?id=ontask)回調函數中調用過`finish`方法後，`return`數據依然會觸發[onFinish](/server/events?id=onfinish)事件
    * `Swoole\Server\Task->finish`是可选的。如果`Worker`進程不關心任務執行的結果，不需要調用此函數
    * 在[onTask](/server/events?id=ontask)回調函數中`return`字符串，等於調用`finish`

  * **注意**

  !> 使用`Swoole\Server\Task->finish`函數必須為`Server`設置[onFinish](/server/events?id=onfinish)回調函數。此函數只可用於 [Task進程](/learn?id=taskworker進程)的[onTask](/server/events?id=ontask)回調中

### pack()

將給定的數據序列化。

```php
Swoole\Server\Task->pack(mixed $data): string|false
```

  * **參數**

    * `mixed $data`

      * 功能：任務處理的結果內容
      * 默认值：無
      * 其它值：無

  * **返回值**
    * 調用成功返回序列化後的結果。 

### unpack()

將給定的數據反序列化。

```php
Swoole\Server\Task->unpack(string $data): mixed
```

  * **參數**

    * `string $data`

      * 功能：需要反序列化的數據
      * 默认值：無
      * 其它值：無

  * **返回值**
    * 調用成功返回反序列化後的結果。 

## 使用示例
```php
<?php
$server->on('task', function(Swoole\Server $serv, Swoole\Server\Task $task) {
    $task->finish(['result' => true]);
});
```
