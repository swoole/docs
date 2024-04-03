# Execute asynchronous tasks

In a server program, if there is a need to perform time-consuming operations, such as broadcasting in a chat server or sending emails in a web server, directly executing these functions will block the current process, leading to slow server responses.

Swoole provides the functionality for handling asynchronous tasks, allowing you to dispatch an asynchronous task to the TaskWorker process pool for execution without affecting the processing speed of the current request.

## Program Code

Based on the first TCP server example, you only need to add two event callback functions, [onTask](/server/events?id=ontask) and [onFinish](/server/events?id=onfinish), to accomplish this. Additionally, you need to set the number of task processes according to the time required for the tasks and the task volume.

Please write the following code into task.php:

```php
$serv = new Swoole\Server('127.0.0.1', 9501);

// Set the number of task worker processes.
$serv->set([
    'task_worker_num' => 4
]);

// This callback function is executed in the worker process.
$serv->on('Receive', function($serv, $fd, $reactor_id, $data) {
    // Dispatch an asynchronous task
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id={$task_id}\n";
});

// Handle the asynchronous task (this callback function is executed in the task process).
$serv->on('Task', function ($serv, $task_id, $reactor_id, $data) {
    echo "New AsyncTask[id={$task_id}]".PHP_EOL;
    // Return the result of the task execution
    $serv->finish("{$data} -> OK");
});

// Handle the result of the asynchronous task (this callback function is executed in the worker process).
$serv->on('Finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[{$task_id}] Finish: {$data}".PHP_EOL;
});

$serv->start();
```

After calling `$serv->task()`, the program will immediately return and continue executing the code. The onTask callback function is asynchronously executed in the Task process pool. Once execution is completed, the `$serv->finish()` method is called to return the result.

!> The finish operation is optional. You can also choose not to return any result. If you return a result using `return` in the `onTask` event, it is equivalent to calling the `Swoole\Server::finish()` operation.
