```
#プロセス\マネージャー

プロセスマネージャーは、[プロセス\プール](/process/process_pool)に基づいて実現されています。複数のプロセスを管理することができます。`プロセス\プール`と比較して、異なるタスクを実行する複数のプロセスを非常に便利に作成でき、各プロセスがコーライブ環境にあるかどうかを制御できます。

## バージョンサポート状況

| バージョン番号 | クラス名                          | 更新説明                                 |
| ------ | ----------------------------- | ---------------------------------------- |
| v4.5.3 | Swoole\Process\ProcessManager | -                                        |
| v4.5.5 | Swoole\Process\Manager        | 再命名、ProcessManager を Manager の別名に |

!> v4.5.3以上のバージョンで利用できます。

## 使用例

```php
use Swoole\Process\Manager;
use Swoole\Process\Pool;

$pm = new Manager();

for ($i = 0; $i < 2; $i++) {
    $pm->add(function (Pool $pool, int $workerId) {
    });
}

$pm->start();
```

## 方法

### __construct()

コンストラクタです。

```php
Swoole\Process\Manager::__construct(int $ipcType = SWOOLE_IPC_NONE, int $msgQueueKey = 0);
```

* **パラメータ**

  * **`int $ipcType`**
    * **機能**：プロセス間の通信モードで、`Process\Pool`の`$ipc_type`と同じです【デフォルトは`0`で、プロセス間の通信特性を使用しません】
    * **デフォルト値**：`0`
    * **その他の値**：なし

  * **`int $msgQueueKey`**
    * **機能**：メッセージキーの `key`で、`Process\Pool`の`$msgqueue_key`と同じです
    * **デフォルト値**：なし
    * **その他の値**：なし

### setIPCType()

作業プロセス間の通信方法を設定します。

```php
Swoole\Process\Manager->setIPCType(int $ipcType): self;
```

* **パラメータ**

  * **`int $ipcType`**
    * **機能**：プロセス間の通信モード
    * **デフォルト値**：なし
    * **その他の値**：なし

### getIPCType()

作業プロセス間の通信方法を取得します。

```php
Swoole\Process\Manager->getIPCType(): int;
```

### setMsgQueueKey()

メッセージキーの`key`を設定します。

```php
Swoole\Process\Manager->setMsgQueueKey(int $msgQueueKey): self;
```

* **パラメータ**

  * **`int $msgQueueKey`**
    * **機能**：メッセージキーの `key`
    * **デフォルト値**：なし
    * **その他の値**：なし

### getMsgQueueKey()

メッセージキーの`key`を取得します。

```php
Swoole\Process\Manager->getMsgQueueKey(): int;
```

### add()

作業プロセスを追加します。

```php
Swoole\Process\Manager->add(callable $func, bool $enableCoroutine = false): self;
```

* **パラメータ**

  * **`callable $func`**
    * **機能**：現在のプロセスが実行する回调関数
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`bool $enableCoroutine`**
    * **機能**：このプロセスにコーライブを作成して回调関数を実行するかどうか
    * **デフォルト値**：false
    * **その他の値**：なし

### addBatch()

批量で作業プロセスを追加します。

```php
Swoole\Process\Manager->addBatch(int $workerNum, callable $func, bool $enableCoroutine = false): self
```

* **パラメータ**

  * **`int $workerNum`**
    * **機能**：批量で追加されるプロセスの数
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`callable $func`**
    * **機能**：これらのプロセスが実行する回调関数
    * **デフォルト値**：なし
    * **その他の値**：なし

  * **`bool $enableCoroutine`**
    * **機能**：これらのプロセスにコーライブを作成して回调関数を実行するかどうか
    * **デフォルト値**：なし
    * **その他の値**：なし

### start()

作業プロセスを起動します。

```php
Swoole\Process\Manager->start(): void
```
```
