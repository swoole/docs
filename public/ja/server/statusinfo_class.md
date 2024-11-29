# Swoole\Server\StatusInfo

ここでは `Swoole\Server\StatusInfo` についての詳細な紹介です。
## 属性
### $worker_id
現在の `worker`プロセスIDを返します。この属性は `int` 型の整数です。

```php
Swoole\Server\StatusInfo->worker_id
```
### $worker_pid
現在の `worker`プロセスの親プロセスIDを返します。この属性は `int` 型の整数です。

```php
Swoole\Server\StatusInfo->worker_pid
```
### $status
プロセスの状態 `status`を返します。この属性は `int` 型の整数です。

```php
Swoole\Server\StatusInfo->status
```
### $exit_code
プロセスの退出状態コード `exit_code`を返します。この属性は `int` 型の整数で、範囲は `0-255`です。

```php
Swoole\Server\StatusInfo->exit_code
```
### $signal
プロセスが退出したシグナル `signal`を返します。この属性は `int` 型の整数です。
