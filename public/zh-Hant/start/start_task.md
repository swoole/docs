# 執行異步任務(Task)

在Server程序中如果需要執行很耗時的操作，比如一個聊天伺服器發送廣播，Web伺服器中發送郵件。如果直接去執行這些函數就會阻塞當前進程，導致伺服器響應變慢。

Swoole提供了異步任務處理的功能，可以投遞一個異步任務到TaskWorker進程池中執行，不影響當前請求的處理速度。

## 程式碼

基於第一個TCP伺服器，只需要增加[onTask](/server/events?id=ontask)和[onFinish](/server/events?id=onfinish) 2個事件回調函數即可。另外需要設置task進程數量，可以根據任務的耗時和任務量配置適量的task進程。

請將以下程式碼寫入task.php。

```php
$serv = new Swoole\Server('127.0.0.1', 9501);

//設定異步任務的工作進程數量。
$serv->set([
    'task_worker_num' => 4
]);

//此回調函數在worker進程中執行。
$serv->on('Receive', function($serv, $fd, $reactor_id, $data) {
    //投遞異步任務
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id={$task_id}\n";
});

//處理異步任務(此回調函數在task進程中執行)。
$serv->on('Task', function ($serv, $task_id, $reactor_id, $data) {
    echo "New AsyncTask[id={$task_id}]".PHP_EOL;
    //返回任務執行的結果
    $serv->finish("{$data} -> OK");
});

//處理異步任務的結果(此回調函數在worker進程中執行)。
$serv->on('Finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[{$task_id}] Finish: {$data}".PHP_EOL;
});

$serv->start();
```

調用`$serv->task()`後，程式立即返回，繼續向下執行程式碼。onTask回調函數Task進程池內被異步執行。執行完成後調用`$serv->finish()`返回結果。

!> finish操作是選填的，也可以不返回任何結果，如果在`onTask`事件中通過`return`返回結果時，等於調用`Swoole\Server::finish()`操作。
