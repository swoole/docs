# Swoole\Server\Task

こちらでは`Swoole\Server\Task`についての詳細な紹介です。このクラスは非常にシンプルですが、`new Swoole\Server\Task()`で`Task`オブジェクトを取得することはできません。このようなオブジェクトにはサーバーに関する情報は一切含まれておらず、`Swoole\Server\Task`の任意の方法を実行すると致命的なエラーが発生します。

```shell
/home/task.php 第3行で無効なSwoole\Server\Taskのインスタンスです
```

## プロパティ
### $data
`worker`プロセスから`task`プロセスに伝わったデータ`data`は、このプロパティは`string`タイプの文字列です。

```php
Swoole\Server\Task->data
```
### $dispatch_time
このデータが`task`プロセスに到達した時間`dispatch_time`を返すプロパティで、このプロパティは`double`タイプです。

```php
Swoole\Server\Task->dispatch_time
```
### $id
このデータが`task`プロセスに到達した時間`dispatch_time`を返すプロパティで、このプロパティは`int`タイプの整数です。

```php
Swoole\Server\Task->id
```
### $worker_id
このデータがどの`worker`プロセスから来たかを返すプロパティで、このプロパティは`int`タイプの整数です。

```php
Swoole\Server\Task->worker_id
```
### $flags
この非同期タスクのいくつかのフラッグ情報`flags`で、このプロパティは`int`タイプの整数です。

```php
Swoole\Server\Task->flags
```

?> `flags`の戻り値は以下のいくつかのタイプです：  
  - SWOOLE_TASK_NOREPLY | SWOOLE_TASK_NONBLOCKは、これが`Worker`プロセスから`task`プロセスに送信されたものではないことを示し、この時、もし`onTask`イベントで`Swoole\Server::finish()`を呼び出した場合、警告が発せられます。  
  - SWOOLE_TASK_CALLBACK | SWOOLE_TASK_NONBLOCKは、`Swoole\Server::finish()`の最後の回调関数がnullではないことを示し、`onFinish`イベントは実行されず、この回调関数のみが実行されます。 
  - SWOOLE_TASK_COROUTINE | SWOOLE_TASK_NONBLOCKは、タスクを协程の形で処理することを示します。 
  - SW_TASK_NONBLOCKはデフォルト値で、上記の3つの状況がすべてない場合に適用されます。
## メソッド
### finish()

[タスクプロセス](/learn?id=taskworkerプロセス)で`Worker`プロセスに通知し、投与されたタスクが完了したことを示すために使用されます。この関数は結果データを`Worker`プロセスに渡すことができます。

```php
Swoole\Server\Task->finish(mixed $data): bool
```

  * **パラメータ**

    * `mixed $data`

      * 機能：タスク処理の結果内容
      * デフォルト値：なし
      *その他の値：なし

  * **ヒント**
    * `finish`方法は連続して何度も呼び出すことができ、`Worker`プロセスは何度も[onFinish](/server/events?id=onfinish)イベントをトリガーします
    * [onTask](/server/events?id=ontask)回调関数で`finish`方法を呼び出した後も、`return`データは[onFinish](/server/events?id=onfinish)イベントをトリガーします
    * `Swoole\Server\Task->finish`はオプションです。もし`Worker`プロセスがタスク実行の結果に関心がない場合は、この関数を呼び出す必要はありません
    * [onTask](/server/events?id=ontask)回调関数で`return`文字列をすると、`finish`と同等の効果が得られます

  * **注意**

  !> `Swoole\Server\Task->finish`関数を使用するためには、`Server`に[onFinish](/server/events?id=onfinish)回调関数を設定する必要があります。この関数は[タスクプロセス](/learn?id=taskworkerプロセス)の[onTask](/server/events?id=ontask)回调の中でのみ使用できます

### pack()

与えられたデータをシリアライズします。

```php
Swoole\Server\Task->pack(mixed $data): string|false
```

  * **パラメータ**

    * `mixed $data`

      * 機能：タスク処理の結果内容
      * デフォルト値：なし
      * その他の値：なし

  * **戻り値**
    * 成功した場合はシリアライズされた結果を返します。 
### unpack()

与えられたデータをデシリアライズします。

```php
Swoole\Server\Task->unpack(string $data): mixed
```

  * **パラメータ**

    * `string $data`

      * 機能：デシリアライズする必要があるデータ
      * デフォルト値：なし
      * その他の値：なし

  * **戻り値**
    * 成功した場合はデシリアライズされた結果を返します。 
## 使用例
```php
<?php
$server->on('task', function(Swoole\Server $serv, Swoole\Server\Task $task) {
    $task->finish(['result' => true]);
});
```
