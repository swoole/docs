# 定時器 Timer

精確到毫秒的定時器。基層基於`epoll_wait`和`setitimer`實現，資料結構使用`最小堆`，可支援添加大量定時器。

* 在同步IO進程中使用`setitimer`和信號實現，如`Manager`和`TaskWorker`進程
* 在異步IO進程中使用`epoll_wait`/`kevent`/`poll`/`select`超時時間實現


## 性能

基層使用最小堆資料結構實現定時器，定時器的添加和刪除，全為內存操作，因此性能是非常高的。

> 官方的基准測試腳本 [timer.php](https://github.com/swoole/benchmark/blob/master/timer.php) 中，添加或刪除`10`萬個隨機時間的定時器耗時為`0.08s`左右。

```shell
~/workspace/swoole/benchmark$ php timer.php
add 100000 timer :0.091133117675781s
del 100000 timer :0.084658145904541s
```

!> 定時器是內存操作，無`IO`消耗


## 差異

`Timer`與`PHP`本身 的`pcntl_alarm`是不同的。`pcntl_alarm`是基於`時鐘信號 + tick`函數實現存在一些缺陷：

  * 最大僅支援到秒，而`Timer`可以到毫秒級別
  * 不支援同時設定多個定時器程序
  * `pcntl_alarm`依賴`declare(ticks = 1)`，性能很差


## 零毫秒定時器

基層不支援時間參數為`0`的定時器。這與`Node.js`等編程語言不同。在`Swoole`裡可以使用[Swoole\Event::defer](/event?id=defer)實現類似的功能。

```php
Swoole\Event::defer(function () {
  echo "hello\n";
});
```

!> 上述代碼與`JS`中的`setTimeout(func, 0)`效果是完全一致的。


## 别名

`tick()`、`after()`、`clear()`都擁有一個函數風格的別名


類靜態方法 | 函數風格別名
---|---
`Swoole\Timer::tick()` | `swoole_timer_tick()`
`Swoole\Timer::after()` | `swoole_timer_after()`
`Swoole\Timer::clear()` | `swoole_timer_clear()`


## 方法


### tick()

設定一個間隔時鐘定時器。

與`after`定時器不同的是`tick`定時器會持續觸發，直到調用 [Timer::clear](/timer?id=clear) 清除。

```php
Swoole\Timer::tick(int $msec, callable $callback_function, ...$params): int
```

!> 1. 定時器僅在当前進程空間內有效  
   2. 定時器是純異步實現的，不能與[同步IO](/learn?id=同步io异步io)的函數一起使用，否則定時器的執行時間會發生錯亂  
   3. 定時器在執行的過程中可能存在一定誤差

  * **參數** 

    * **`int $msec`**
      * **功能**：指定時間
      * **值單位**：毫秒【如`1000`表示`1`秒，`v4.2.10`以下版本最大不得超過 `86400000`】
      * **默認值**：無
      * **其它值**：無

    * **`callable $callback_function`**
      * **功能**：時間到期後所執行的函數，必須是可以調用的
      * **默認值**：無
      * **其它值**：無

    * **`...$params`**
      * **功能**：給執行函數傳遞數據【此參數也為可選參數】
      * **默認值**：無
      * **其它值**：無
      
      !> 可以使用匿名函數的`use`語法傳遞參數到回調函數中

  * **$callback_function 回調函數** 

    ```php
    callbackFunction(int $timer_id, ...$params);
    ```

      * **`int $timer_id`**
        * **功能**：定時器的`ID`【可用於[Timer::clear](/timer?id=clear)清除此定時器】
        * **默認值**：無
        * **其它值**：無

      * **`...$params`**
        * **功能**：由`Timer::tick`傳入的第三个參數`$param`
        * **默認值**：無
        * **其它值**：無

  * **擴展**

    * **定時器校準**

      定時器回調函數的執行時間不影響下一次定時器執行的時間。實例：在`0.002s`設定了`10ms`的`tick`定時器，第一次會在`0.012s`執行回調函數，如果回調函數執行了`5ms`，下一次定時器仍然會在`0.022s`時觸發，而不是`0.027s`。
      
      但如果定時器回調函數的執行時間過長，甚至覆蓋了下一次定時器執行的時間。基層會進行時間校準，丢弃已過期的行為，在下一時間回調。如上面例子中`0.012s`時的回調函數執行了`15ms`，本該在`0.022s`產生一次定時回調。實際上本次定時器在`0.027s`才返回，這時定時早已過期。基層會在`0.032s`時再次觸發定時器回調。
    
    * **協程模式**

      在協程環境下`Timer::tick`回調中會自動創建一個協程，可以直接使用協程相關`API`，無需調用`go`創建協程。
      
      !> 可設置 [enable_coroutine](/timer?id=close-timer-co) 關閉自動創建協程

  * **使用示例**

    ```php
    Swoole\Timer::tick(1000, function(){
        echo "timeout\n";
    });
    ```

    * **正確示例**

    ```php
    Swoole\Timer::tick(3000, function (int $timer_id, $param1, $param2) {
        echo "timer_id #$timer_id, after 3000ms.\n";
        echo "param1 is $param1, param2 is $param2.\n";

        Swoole\Timer::tick(14000, function ($timer_id) {
            echo "timer_id #$timer_id, after 14000ms.\n";
        });
    }, "A", "B");
    ```

    * **錯誤示例**

    ```php
    Swoole\Timer::tick(3000, function () {
        echo "after 3000ms.\n";
        sleep(14);
        echo "after 14000ms.\n";
    });
    ```


### after()

在指定的時間後執行函數。`Swoole\Timer::after`函數是一個一次性定時器，執行完成後就會摧毀。

此函數與`PHP`標準庫提供的`sleep`函數不同，`after`是非阻塞的。而`sleep`調用後會導致當前的進程進入阻塞，將無法處理新的請求。

```php
Swoole\Timer::after(int $msec, callable $callback_function, ...$params): int
```

  * **參數** 

    * **`int $msec`**
      * **功能**：指定時間
      * **值單位**：毫秒【如`1000`表示`1`秒，`v4.2.10`以下版本最大不得超過 `86400000`】
      * **默認值**：無
      * **其它值**：無

    * **`callable $callback_function`**
      * **功能**：時間到期後所執行的函數，必須是可以調用的。
      * **默認值**：無
      * **其它值**：無

    * **`...$params`**
      * **功能**：給執行函數傳遞數據【此參數也為可選參數】
      * **默認值**：無
      * **其它值**：無
      
      !> 可以使用匿名函數的`use`語法傳遞參數到回調函數中

  * **返回值**

    * 執行成功返回定時器`ID`，若取消定時器，可調用 [Swoole\Timer::clear](/timer?id=clear)

  * **擴展**

    * **協程模式**

      在協程環境下[Swoole\Timer::after](/timer?id=after)回調中會自動創建一個協程，可以直接使用協程相關`API`，無需調用`go`創建協程。
      
      !> 可設置 [enable_coroutine](/timer?id=close-timer-co) 關閉自動創建協程

  * **使用示例**

```php
$str = "Swoole";
Swoole\Timer::after(1000, function() use ($str) {
    echo "Hello, $str\n";
});
```
### clear()

使用定时器`ID`来删除定时器。

```php
Swoole\Timer::clear(int $timer_id): bool
```

  * **参数** 

    * **`int $timer_id`**
      * **功能**：定时器`ID`【调用[Timer::tick](/timer?id=tick)、[Timer::after](/timer?id=after)后会返回一个整数的ID】
      * **默认值**：无
      * **其它值**：无

!> `Swoole\Timer::clear`不能用于清除其他进程的定时器，只作用于当前进程

  * **使用示例**

```php
$timer = Swoole\Timer::after(1000, function () {
    echo "timeout\n";
});

var_dump(Swoole\Timer::clear($timer));
var_dump($timer);

// 输出：bool(true) int(1)
// 不输出：timeout
```

### clearAll()

清除当前 Worker 进程内的所有定时器。

!> Swoole版本 >= `v4.4.0` 可用

```php
Swoole\Timer::clearAll(): bool
```

### info()

返回`timer`的信息。

!> Swoole版本 >= `v4.4.0` 可用

```php
Swoole\Timer::info(int $timer_id): array
```

  * **返回值**

```php
array(5) {
  ["exec_msec"]=>
  int(6000)
  ["exec_count"]=> // v4.8.0 添加
  int(5)
  ["interval"]=>
  int(1000)
  ["round"]=>
  int(0)
  ["removed"]=>
  bool(false)
}
```

### list()

返回定时器迭代器, 可使用`foreach`遍历当前 Worker 进程内所有`timer`的 id

!> Swoole版本 >= `v4.4.0` 可用

```php
Swoole\Timer::list(): Swoole\Timer\Iterator
```

  * **使用示例**

```php
foreach (Swoole\Timer::list() as $timer_id) {
    var_dump(Swoole\Timer::info($timer_id));
}
```

### stats()

查看定时器状态。

!> Swoole版本 >= `v4.4.0` 可用

```php
Swoole\Timer::stats(): array
```

  * **返回值**

```php
array(3) {
  ["initialized"]=>
  bool(true)
  ["num"]=>
  int(1000)
  ["round"]=>
  int(1)
}
```

### set()

设置定时器相关参数。

```php
Swoole\Timer::set(array $array): void
```

!> 此方法从 `v4.6.0` 版本标记为废弃。

## 关闭协程 :id=close-timer-co

默认定时器在执行回调函数时会自动创建协程，可单独设置定时器关闭协程。

```php
swoole_async_set([
  'enable_coroutine' => false,
]);
```
