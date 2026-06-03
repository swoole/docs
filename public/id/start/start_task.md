# Menjalankan Tugas Asinkron (Task)

Di program server, kalo ada operasi yang butuh waktu lama—misalnya chat server yang broadcast atau web server yang ngirim email—kalo langsung dipanggil, prosesnya bakal keblokir dan server jadi lemot.

Swoole nyediain fitur pemrosesan asynchronous task. Kamu bisa ngirim task ke pool process TaskWorker biar dieksekusi di latar belakang, nggak ganggu kecepatan proses request yang lagi berjalan.

## Kode Program

Berdasarkan contoh TCP server pertama, tinggal nambahin 2 event callback function: [onTask](/server/events?id=ontask) dan [onFinish](/server/events?id=onfinish). Selain itu, perlu ngatur jumlah process task sesuai dengan lama dan banyaknya task.

Tulis kode berikut ke dalam task.php.

```php
$serv = new Swoole\Server('127.0.0.1', 9501);

// Set jumlah process worker untuk task.
$serv->set([
    'task_worker_num' => 4
]);

// Callback ini jalan di worker process.
$serv->on('Receive', function($serv, $fd, $reactor_id, $data) {
    // Kirim asynchronous task
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id={$task_id}\n";
});

// Proses asynchronous task (callback ini jalan di task process).
$serv->on('Task', function ($serv, $task_id, $reactor_id, $data) {
    echo "New AsyncTask[id={$task_id}]".PHP_EOL;
    // Kembaliin hasil eksekusi task
    $serv->finish("{$data} -> OK");
});

// Proses hasil asynchronous task (callback ini jalan di worker process).
$serv->on('Finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[{$task_id}] Finish: {$data}".PHP_EOL;
});

$serv->start();
```

Setelah manggil `$serv->task()`, program langsung balik dan lanjut jalan ke kode selanjutnya. Callback onTask bakal dieksekusi secara asynchronous di pool process Task. Begitu selesai, `$serv->finish()` dipanggil buat ngembaliin hasil.

!> Operasi finish itu opsional—kamu bisa nggak ngembaliin hasil apa-apa. Kalo di event `onTask` kamu pake `return` buat ngembaliin hasil, itu sama aja kayak panggil `Swoole\Server::finish()`.
