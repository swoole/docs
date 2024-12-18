# Корутины\WaitGroup

В `Swoole4` можно использовать [Channel](/coroutine/channel) для реализации коммуникации между корутинами, управления зависимостями и синхронизации корутин. На основе [Channel](/coroutine/channel) легко реализовать функциональность `sync.WaitGroup` из `Golang`.

## Реализация кода

> Эта функция написана на PHP и не является кодом C/C++, исходный код находится в [Либарате](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php)

* Метод `add` увеличивает счетчик
* `done` обозначает, что задание завершено
* `wait` ждет завершения всех заданий, чтобы возобновить выполнение текущей корутины
* Объект `WaitGroup` может быть повторно использован после `add`, `done`, `wait`

## Пример использования

```php
<?php
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $wg = new WaitGroup();
    $result = [];

    $wg->add();
    // Запуск первой корутины
    Coroutine::create(function () use ($wg, &$result) {
        // Запуск клиента-корутины client, запрос на домашнюю страницу Таобао
        $cli = new Client('www.taobao.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.taobao.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['taobao'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    $wg->add();
    // Запуск второй корутины
    Coroutine::create(function () use ($wg, &$result) {
        // Запуск клиента-корутины client, запрос на домашнюю страницу Baidu
        $cli = new Client('www.baidu.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.baidu.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['baidu'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    // Замораживание текущей корутины, ожидание завершения всех заданий для возобновления
    $wg->wait();
    // Здесь $result содержит результаты выполнения двух заданий
    var_dump($result);
});
```
