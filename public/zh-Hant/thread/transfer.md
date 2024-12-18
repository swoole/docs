# 資料型別
這裡列出了可以在執行緒間傳遞和共享的資料型別。

## 基礎型別
`null/bool/int/float` 型別變數，內存尺寸小於 `16 Bytes`，作為值進行傳遞。

## 字串
對字符串進行**內存拷貝**，儲存到 `ArrayList`、`Queue`、`Map`。

## Socket 資源

### 支援的型別列表

- `Co\Socket`

- `PHP Stream`
- `PHP Socket(ext-sockets)`，需開啟 `--enable-sockets` 編譯參數

### 不支援的型別

- `Swoole\Client`

- `Swoole\Server`

- `Swoole\Coroutine\Client`

- `Swoole\Coroutine\Http\Client`

- `pdo` 連接

- `redis` 連接
- 其他的特殊 `Socket` 資源型別

### 資源拷貝

- 在寫入時將進行 `dup(fd)` 操作，與原有資源分離，互不干擾，對原有的資源進行 `close` 操作不會影響到新的資源
- 在讀取時進行 `dup(fd)` 操作，在讀取的子執行緒 `VM` 內構建新的 `Socket` 資源
- 在刪除時進行 `close(fd)` 操作，釋放檔案句柄

這意味著 `Socket` 資源會存在 `3` 次引用計數，分別是：

- `Socket` 資源初始創建時所在的執行緒

- `ArrayList`、`Queue`、`Map` 容器
- 讀取 `ArrayList`、`Queue`、`Map` 容器的子執行緒

當沒有任何執行緒或容器持有此資源，引用計數減為 `0` 時，`Socket` 資源才會被真正地釋放。引用計數不為 `0`，
即使執行了 `close` 操作，也不會關閉連接，不會影響其他執行緒或資料容器持有的 `Socket` 資源。

若希望忽略引用計數，直接關閉 `Socket`，可使用 `shutdown()` 方法，例如：

- `stream_socket_shutdown()`

- `Socket::shutdown()`
- `socket_shutdown()`

> `shutdown` 操作將影響所有執行緒持有的 `Socket` 資源，執行後將不再可用，無法執行 `read/write` 操作

## 陣列
使用 `array_is_list()` 進行判斷陣列的型別，若為數字索引陣列則轉為 `ArrayList`，關聯索引陣列轉為 `Map`。

- 將會遍歷整個陣列，將元素插入到 `ArrayList` 或 `Map` 中
- 支援多维陣列，遞迴遍歷多维陣列轉為嵌套結構的 `ArrayList` 或 `Map`

例子：
```php
$array = [
    'a' => random_int(1, 999999999999999999),
    'b' => random_bytes(128),
    'c' => uniqid(),
    'd' => time(),
    'e' => [
        'key' => 'value',
        'hello' => 'world',
    ];
];

$map = new Map($array);

// $map['e'] 是一個新的 Map 對象，包含兩個元素，key、hello，值為 'value' 和 'world'
var_dump($map['e']);
```

## 物件

### 執行緒資源物件

`Thread\Lock`、`Thread\Atomic`、`Thread\ArrayList`、`Thread\Map` 等執行緒資源物件，可直接儲存到 `ArrayList`、`Queue`、`Map` 中。
此操作僅僅是將物件的引用儲存到容器中，不會進行物件的拷貝。

將物件寫入到 `ArrayList` 或 `Map` 時，只是對執行緒資源增加一次引用計數，不會拷貝。當物件的引用計數為 `0` 時，會被釋放。

例子：

```php
$map = new Thread\Map;
$lock = new Thread\Lock; // 當前引用計數為 1
$map['lock'] = $lock; // 當前引用計數為 2
unset($map['lock']); // 當前引用計數為 1
unset($lock); // 當前引用計數為 0，Lock 物件被釋放
```

支援列表：

- `Thread\Lock`

- `Thread\Atomic`

- `Thread\Atomic\Long`

- `Thread\Barrier`

- `Thread\ArrayList`

- `Thread\Map`
- `Thread\Queue`

請注意`Thread`執行緒物件，不可序列化也不可傳遞，僅在父執行緒中可用。

### 普通 PHP 物件
將在寫入時自動序列化，讀取時反序列化。請注意若物件包含不可序列化型別，將會拋出例外。
