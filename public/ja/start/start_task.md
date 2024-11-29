# 非同期タスク（Task）の実行

サーバープログラムで、チャットサーバーが放送を送信したり、Webサーバーがメールを送信するなど、時間がかかる操作が必要な場合、これらの関数を直接実行すると、現在のプロセスをブロックし、サーバーの応答速度が遅くなります。

Swooleは非同期タスク処理の機能を提供しており、タスクワークャープロセスプールに非同期タスクを投入して実行することができ、現在のリクエスト処理速度に影響を与えません。

## プログラムコード

最初のTCPサーバーに基づいて、[onTask](/server/events?id=ontask)と[onFinish](/server/events?id=onfinish)の2つのイベント回调関数を追加するだけで済みます。また、タスクプロセスの数を設定する必要があります。タスクの時間と量に応じて、適切な数のタスクプロセスを構成することができます。

以下のコードをtask.phpに書き出してください。

```php
$serv = new Swoole\Server('127.0.0.1', 9501);

// 非同期タスクの作業プロセス数を設定します。
$serv->set([
    'task_worker_num' => 4
]);

// workerプロセスで実行される回调関数です。
$serv->on('Receive', function($serv, $fd, $reactor_id, $data) {
    // 非同期タスクを投入
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id={$task_id}\n";
});

// 非同期タスクを処理します（この回调関数はtaskプロセスで実行されます）。
$serv->on('Task', function ($serv, $task_id, $reactor_id, $data) {
    echo "New AsyncTask[id={$task_id}]".PHP_EOL;
    // タスク実行の結果を返す
    $serv->finish("{$data} -> OK");
});

// 非同期タスクの結果を処理します（この回调関数はworkerプロセスで実行されます）。
$serv->on('Finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[{$task_id}] Finish: {$data}".PHP_EOL;
});

$serv->start();
```

`$serv->task()`を呼び出した後、プログラムはすぐに戻り、以下のコードを実行し続けます。onTask回调関数はTaskプロセスプール内で非同期に実行されます。実行が完了した後、`$serv->finish()`を呼び出して結果を返します。

!> finish操作はオプションであり、結果を何も返さなくても大丈夫です。もしonTaskイベント内で`return`を通じて結果を返した場合、これはSwoole\Server::finish()操作と同等です。
