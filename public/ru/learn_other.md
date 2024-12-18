# Прочее знание


## Установка таймаута и попыток решения DNS

В сетевом программировании часто используются функции `gethostbyname` и `getaddrinfo` для разрешения доменных имен, но эти две `C` функции не предоставляют параметров таймаута. На самом деле можно изменить `/etc/resolv.conf`, чтобы установить таймаут и логику попыток.

!> См. документацию по `man resolv.conf`


### Несколько NameServer <!-- {docsify-ignore} -->

```
nameserver 192.168.1.3
nameserver 192.168.1.5
option rotate
```

Можно настроить несколько `nameserver`, и система автоматически будет чередовать их, перейдя на следующий `nameserver` для повторной попытки в случае неудачи с первым `nameserver`.

Конфигурация `option rotate` служит для балансировки нагрузки между `nameserver` с использованием циклического режима.


### Таймаут управления <!-- {docsify-ignore} -->

```
option timeout:1 attempts:2
```

* `timeout`: контролирует таймаут при приеме `UDP`, в секундах, по умолчанию `5` секунд
* `attempts`: контролирует количество попыток, в случае настройки на `2`, означает, что максимальное количество попыток составляет `2`, по умолчанию `5`

Если у нас есть `2` `nameserver` и `attempts` равно `2`, а таймаут составляет `1`, то в случае отсутствия ответа со всех `DNS` серверов максимальное ожидание составляет `4` секунды (`2x2x1`).

### Отслеживание вызовов <!-- {docsify-ignore} -->

Можно использовать [strace](/other/tools?id=strace) для отслеживания и подтверждения.

Установите `nameserver` на два несуществующих `IP`, а в PHP коде используйте `var_dump(gethostbyname('www.baidu.com'));` для разрешения доменного имени.

```
socket(AF_INET, SOCK_DGRAM|SOCK_CLOEXEC|SOCK_NONBLOCK, IPPROTO_IP) = 3
connect(3, {sa_family=AF_INET, sin_port=htons(53), sin_addr=inet_addr("10.20.128.16")}, 16) = 0
poll([{fd=3, events=POLLOUT}], 1, 0)    = 1 ([{fd=3, revents=POLLOUT}])
sendto(3, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=3, events=POLLIN}], 1, 1000

)  = 0 (Timeout)
socket(AF_INET, SOCK_DGRAM|SOCK_CLOEXEC|SOCK_NONBLOCK, IPPROTO_IP) = 4
connect(4, {sa_family=AF_INET, sin_port=htons(53), sin_addr=inet_addr("10.20.128.18")}, 16) = 0
poll([{fd=4, events=POLLOUT}], 1, 0)    = 1 ([{fd=4, revents=POLLOUT}])
sendto(4, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=4, events=POLLIN}], 1, 1000


)  = 0 (Timeout)
poll([{fd=3, events=POLLOUT}], 1, 0)    = 1 ([{fd=3, revents=POLLOUT}])
sendto(3, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=3, events=POLLIN}], 1, 1000


)  = 0 (Timeout)
poll([{fd=4, events=POLLOUT}], 1, 0)    = 1 ([{fd=4, revents=POLLOUT}])
sendto(4, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=4, events=POLLIN}], 1, 1000



)  = 0 (Timeout)
close(3)                                = 0
close(4)                                = 0
```

Как видно, здесь было проведено `4` попытки, а таймаут для `poll` был установлен в `1000ms` (1 секунду).
