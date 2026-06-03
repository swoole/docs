# Panggilan Konkuren

[//]: # (
Di sini fitur setDefer telah dihapus karena klien yang mendukung setDefer disarankan menggunakan one-click coroutine.
)

Menggunakan `coroutine anak (go)` + `channel` untuk mengimplementasikan permintaan konkuren.

!> Sebaiknya baca [ikhtisar](/coroutine) terlebih dahulu untuk memahami konsep dasar coroutine sebelum membaca bagian ini.

### Prinsip Implementasi

* Di `onRequest`, perlu melakukan dua permintaan `HTTP` secara konkuren. Bisa menggunakan fungsi `go` untuk membuat `2` coroutine anak, meminta banyak `URL` secara konkuren.
* Buat `channel`, gunakan sintaks referensi closure `use` untuk melewatkannya ke coroutine anak.
* Coroutine utama memanggil `chan->pop` secara berulang, menunggu coroutine anak menyelesaikan tugas, `yield` masuk ke keadaan ditangguhkan.
* Saat salah satu dari dua coroutine anak yang berjalan konkuren menyelesaikan permintaan, panggil `chan->push` untuk mengirim data ke coroutine utama.
* Setelah coroutine anak selesai meminta `URL`, keluar, coroutine utama kembali dari keadaan ditangguhkan, melanjutkan eksekusi ke bawah dengan memanggil `$resp->end` untuk mengirim respons.

### Contoh Penggunaan

```php
$serv = new Swoole\Http\Server("127.0.0.1", 9503, SWOOLE_BASE);

$serv->on('request', function ($req, $resp) {
	$chan = new Channel(2);
	go(function () use ($chan) {
		$cli = new Swoole\Coroutine\Http\Client('www.qq.com', 80);
			$cli->set(['timeout' => 10]);
			$cli->setHeaders([
			'Host' => "www.qq.com",
			"User-Agent" => 'Chrome/49.0.2587.3',
			'Accept' => 'text/html,application/xhtml+xml,application/xml',
			'Accept-Encoding' => 'gzip',
		]);
		$ret = $cli->get('/');
		$chan->push(['www.qq.com' => $cli->body]);
	});

	go(function () use ($chan) {
		$cli = new Swoole\Coroutine\Http\Client('www.163.com', 80);
		$cli->set(['timeout' => 10]);
		$cli->setHeaders([
			'Host' => "www.163.com",
			"User-Agent" => 'Chrome/49.0.2587.3',
			'Accept' => 'text/html,application/xhtml+xml,application/xml',
			'Accept-Encoding' => 'gzip',
		]);
		$ret = $cli->get('/');
		$chan->push(['www.163.com' => $cli->body]);
	});
	
	$result = [];
	for ($i = 0; $i < 2; $i++)
	{
		$result += $chan->pop();
	}
	$resp->end(json_encode($result));
});
$serv->start();
```

!> Menggunakan fitur [WaitGroup](/coroutine/wait_group) yang disediakan `Swoole` akan lebih sederhana.
