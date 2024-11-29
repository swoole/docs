# Swoole\Server\TaskResult

ここでは `Swoole\Server\TaskResult` についての詳細な説明です。
## 属性
### $task_id
対応する `Reactor` スレッドの IDを返します。この属性は `int` 型の整数です。

```php
Swoole\Server\TaskResult->task_id
```
### $task_worker_id
この実行結果がどの `task`プロセスから来たかを返します。この属性は `int` 型の整数です。

```php
Swoole\Server\TaskResult->task_worker_id
```
### $dispatch_time
この接続が携帯するデータ `data`を返します。この属性は `?string` 型の文字列です。

```php
Swoole\Server\TaskResult->dispatch_time
```
### $data
この接続が携帯するデータ `data`を返します。この属性は `string` 型の文字列です。

```php
Swoole\Server\StatusInfo->data
```
