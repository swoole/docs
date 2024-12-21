# 服务端 (кооперативная стиль) <!-- {docsify-ignore-all} -->

`Swoole\Coroutine\Server` отличается от [асинхронного стиля](/server/init) серверов тем, что `Swoole\Coroutine\Server` является полностью кооперативным сервером, см. [Полный пример](/coroutine/server?id=полный-пример).

## Преимущества:

- Не требуется установка функций обратной связи событий. Создание соединения, прием данных, отправка данных, закрытие соединения происходят последовательно, нет проблем с параллельностью, как в [асинхронном стиле](/server/init), например:

```php
$serv = new Swoole\Server("127.0.0.1", 9501);

// Слушать событие подключения
$serv->on('Connect', function ($serv, $fd) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);// В этом callback'е кооператив будет замереть
    Co::sleep(5);// Здесь sleep имитирует медленное соединение
    $redis->set($fd,"fd $fd connected");
});

// Слушать событие приема данных
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    $redis = new Redis();
    $redis->connect("127.0.0.1",6379);// В этом callback'е кооператив будет замереть
    var_dump($redis->get($fd));// Возможно, callback 'Receive' создадет соединение с Redis раньше, чем set, и get вернет false, что приведет к логической ошибке
});

// Слушать событие закрытия соединения
$serv->on('Close', function ($serv, $fd) {
    echo "Клиент: Закрыт.\n";
});

// Запустить сервер
$serv->start();
```

Вышеупомянутый сервер в [асинхронном стиле](/server/init) не может гарантировать порядок событий, то есть нельзя гарантировать, что `onConnect` закончит работу перед тем, как начнется `onReceive`, потому что после включения кооперативного стиля,回调 `onConnect` и `onReceive` автоматически создадут кооперативы, и при возникновении IO произойдет [кооперативная диспетчеризация](/coroutine?id=кооперативная-диспетчеризация), в то время как в асинхронном стиле нельзя гарантировать порядок диспетчеризации, в то время как сервер в кооперативном стиле не имеет этой проблемы.

- Можно динамически включать и выключать обслуживание, в то время как сервер в асинхронном стиле после вызова `start()` ничего не может делать, в то время как сервер в кооперативном стиле может динамически включать и выключать обслуживание.

## Недостатки:

- Сервер в кооперативном стиле не создает несколько процессов автоматически, для использования многопроцессорного режима необходимо использовать [Process\Pool](/process/process_pool) модуль.
- Сервер в кооперативном стиле на самом деле является обернутым [Co\Socket](/coroutine_client/socket) модулем, поэтому для использования кооперативного стиля необходимо иметь определенные знания о программировании с сокетами.
- На данный момент уровень обернутой структуры не так высок, как у серверов в асинхронном стиле, некоторые вещи нужно реализовать самостоятельно, например, функция `reload` требует ручного мониторинга сигналов для выполнения логики.