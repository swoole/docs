# 安全并发容器 Queue

建立一個並發的 `Queue` 結構，可用作線程參數傳遞給子線程。讀寫時在其他線程是可见的。




## 特性
- `Thread\Queue` 是一個先進先出（FIFO）的數據結構。


- `Map`、`ArrayList`、`Queue` 會自動分配內存，不需要像 `Table` 那樣固定分配。


- 底層會自動加鎖，是線程安全的


- 可傳遞的變數類型參考 [線程參數傳遞](thread/transfer.md)


- 不支援迭代器，底層使用了 `C++ std::queue`，僅支援先進先出操作


- 必須在線程創建前將 `Map`、`ArrayList`、`Queue` 對象作為線程參數傳遞給子線程


- `Thread\Queue` 只能壓入、弹出元素，不能隨機訪問元素


- `Thread\Queue` 内置了線程條件變量，可在 `push/pop` 操作中喚醒、等待其他線程


## 示範

```php
使用 Swoole\Thread；
使用 Swoole\Thread\Queue；

$args = Thread::getArguments();
$c = 4；
$n = 128；

if (empty($args)) {
    $threads = [];
    $queue = new Queue；
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i, $queue)；
    }
    while ($n--) {
        $queue->push(base64_encode(random_bytes(16))， Queue::NOTIFY_ONE)；
        usleep(random_int(10000, 100000));
    }
    $n = 4；
    while ($n--) {
        $queue->push('', Queue::NOTIFY_ONE)；
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($queue->count());
} else {
    $queue = $args[1]；
    while (1) {
        $job = $queue->pop(-1)；
        if (!$job) {
            break;
        }
        var_dump($job)；
    }
}
```


## 常數



名稱 | 作用
---|---
`Queue::NOTIFY_ONE` | 喚醒一個線程
`Queue::NOTIFY_ALL` | 喚醒所有線程


## 方法列表


### __construct()
安全並發容器 `Queue` 構造函數

```php
Swoole\Thread\Queue->__construct()
```


### push()
向隊列尾部中寫入數據

```php
Swoole\Thread\Queue()->push(mixed $value, int $notify_which = 0): void
```

  * **參數**
      * `mixed $value`
          * 功能：寫入的數據內容。
          * 默认值：無。
          * 其它值：無。

      !> 為避免產生歧義，請勿向通道中寫入`null`和`false`
  
      * `int $notify`
          * 功能：是否通知等待讀取數據的線程。
          * 默认值：`0`，不會喚醒任何線程
          * 其它值：`Swoole\Thread\Queue::NOTIFY_ONE` 喚醒一個線程，`Swoole\Thread\Queue::NOTIFY_ALL` 喚醒所有線程。



### pop()
從隊列頭部中提取數據

```php
Swoole\Thread\Queue()->pop(float $timeout = 0): mixed
```

* **參數**
    * `float $wait`
        * 功能：超時時間。
        * 默认值：`0`，表示不等待。
        * 其它值：如果不为`0`， 表示當隊列為空時在`$timeout`秒內等待生產者 `push()` 數據，為負數時表示永不超時。

* **返回值**
    * 返回隊列頭部數據，當隊列為空時直接返回 `NULL`。

> 使用`Queue::NOTIFY_ALL`喚醒所有線程時，只有一個線程可以獲得`push()`操作寫入的數據


### count()
獲取隊列元素數量

```php
Swoole\Thread\Queue()->count(): int
```

* **返回值**
    * 返回隊列數量。

### clean()
清空所有元素

```php
Swoole\Thread\Queue()->clean(): void
```
