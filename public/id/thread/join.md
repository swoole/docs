# Manajemen Thread

## Thread::join()

Tunggu child thread sampe selesai. Kalo child thread masih jalan, `join()` bakal blocking.

```php
$thread = Thread::exec(__FILE__, $i);
$thread->join();
```

## Thread::joinable()

Cek apakah child thread udah selesai.

### Return Value
- `true` artinya child thread udah selesai, saat panggil `join()` nggak bakal blocking
- `false` artinya masih jalan

```php
$thread = Thread::exec(__FILE__, $i);
var_dump($thread->joinable());
```

## Thread::detach()

Lepas child thread dari kontrol parent thread, biar bisa exit tanpa perlu panggil `join()` buunggu thread selesai dan bersihin resource.

```php
$thread = Thread::exec(__FILE__, $i);
$thread->detach();
unset($thread);
```

## Thread::getId()

Static method buat dapetin `ID` thread saat ini. Dipanggil dari dalam child thread.

```php
var_dump(Thread::getId());
```

## Thread::getArguments()

Static method buat dapetin argument dari thread saat ini. Dipanggil dari dalam child thread, argument dikirim oleh parent thread lewat `Thread::exec()`.

```php
var_dump(Thread::getArguments());
```

## Thread::$id

Pake property object ini buat dapetin `ID` child thread.

```php
$thread = Thread::exec(__FILE__, $i);
var_dump($thread->id);
```
