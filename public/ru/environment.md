# Установка Swoole

Расширение Swoole построено в соответствии со стандартом PHP. Используйте phpize для генерации скрипта для проверки сборки, ./configure для проверки конфигурации сборки, make для сборки и make install для установки.

* Если нет особых требований, обязательно установите последнюю [версию Swoole](https://github.com/swoole/swoole-src/releases/) из исходников.
* Если текущий пользователь не является root, возможно, у вас нет права на запись в каталог установки PHP, при установке потребуется sudo или su.
* Если вы обновляете код с ветки git напрямую с помощью git pull, перед повторной сборкой обязательно выполните make clean.
* Поддерживается только `Linux` (с ядром 2.3.32 и выше), `FreeBSD`, `MacOS`.
* Для систем с базой Linux низкой версии (например, CentOS 6) можно использовать инструменты разработки, предоставленные RedHat: [Справочник](https://blog.csdn.net/ppdouble/article/details/52894271).
* На платформе Windows можно использовать WSL (Windows Subsystem for Linux) или CygWin.
* Некоторые расширения несовместимы с расширением Swoole, смотрите [конфликты расширений](/getting_started/extension).

## Подготовка к установке

Перед установкой необходимо убедиться, что система уже安装了 следующие программы

- Для версии 4.8 требуется `PHP-7.2` или более новая версия

- Для версии 5.0 требуется `PHP-8.0` или более новая версия

- Для версии 6.0 требуется `PHP-8.1` или более новая версия

- `gcc-4.8` или более новая версия

- `make`

- `autoconf`

## Быстрая установка

> 1. Скачать исходники swoole

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2. Собрать и установить из исходников

После скачивания исходников, перейдите в каталог исходников в терминале и выполните следующие команды для сборки и установки

Если на ubuntu не установлен командный файл phpize: `sudo apt-get install php-dev` для установки phpize

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3. Активировать расширение

После успешной сборки и установки в систему, необходимо добавить строку `extension=swoole.so` в `php.ini`, чтобы активировать расширение Swoole

## Полный пример сборки

!>Developеры, впервые сталкивающиеся с Swoole, должны сначала попробовать простую сборку вышеупомянутой статьи, и если есть дальнейшие потребности, могут настроить следующие параметры сборки в соответствии с конкретными требованиями и версиями. [Справочник параметров сборки](/environment?id=compile_options)

Нижеуказанный скрипт скачает и собирает исходники из ветки master, необходимо убедиться, что вы установили все зависимости, иначе столкнетесь с различными ошибками зависимости.

```shell
mkdir -p ~/build && \
cd ~/build && \
rm -rf ./swoole-src && \
curl -o ./tmp/swoole.tar.gz https://github.com/swoole/swoole-src/archive/master.tar.gz -L && \
tar zxvf ./tmp/swoole.tar.gz && \
mv swoole-src* swoole-src && \
cd swoole-src && \
phpize && \
./configure \
--enable-openssl --enable-sockets --enable-mysqlnd --enable-swoole-curl --enable-cares --enable-swoole-pgsql && \
sudo make && sudo make install
```

## PECL

> Примечание: Публикация PECL происходит после публикации на GitHub

Проект Swoole был включен в официальный репозиторий PHP расширений, помимо ручной загрузки и сборки, его также можно установить одним командой с помощью официальной команды PHP `pecl`

```shell
pecl install swoole
```

При установке Swoole через PECL во время процесса установки он спросит, должны ли быть активированы некоторые функции, что также может быть предоставлено до выполнения установки, например:

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#или
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```

## Добавление Swoole в php.ini

Наконец, после успешной сборки и установки, измените `php.ini`, чтобы добавить

```ini
extension=swoole.so
```

Используйте `php -m` для проверки, успешно ли было загружено `swoole.so`, если нет, возможно, путь к `php.ini` неверен.  
Используйте `php --ini` для определения абсолютного пути к `php.ini`,该项 `Loaded Configuration File` показывает загруженный файл php.ini, если значение равно `none`, значит ни одного php.ini файла не было загружено, вам нужно создать его самостоятельно.

!> Поддержка PHP версий согласована с официальной поддерживаемой версией PHP, смотрите [Таблица поддержки версий PHP](http://php.net/supported-versions.php)

## Сборка и установка на других платформах

Платформа ARM (Raspberry PI)

* Используйте перекрестную сборку GCC
* При сборке Swoole необходимо вручную изменить Makefile, чтобы убрать параметр `-O2`

Платформа MIPS (Router OpenWrt)

* Используйте перекрестную сборку GCC

Windows WSL

В системе Windows 10 появилась поддержка подсистемы Linux, и в среде BashOnWindows также можно использовать Swoole. Команды установки

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> В среде WSL необходимо выключить опцию daemonize  
При WSL ниже 17101 после установки configure необходимо изменить config.h, чтобы выключить HAVE_SIGNALFD

## ОфициальноеDocker-изображение

- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)  
- dockerhub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)

## Компилятионные опции

Вот дополнительные параметры для ./configure, которые используются для включения некоторых функций

### Общие параметры

#### --enable-openssl

Включить поддержку SSL

> Использовать динамическую библиотеку libssl.so, предоставляемую операционной системой

#### --with-openssl-dir

Включить поддержку SSL и указать путь к библиотеке openssl, следует следовать за параметрами пути, например: --with-openssl-dir=/opt/openssl/

#### --enable-http2

Включить поддержку HTTP2

> Зависит от библиотеки nghttp2. Начиная с версии 4.3.0, зависимость не требуется, она встроена, но все равно необходимо добавить этот параметр для включения поддержки HTTP2, по умолчанию включено в Swoole5.

#### --enable-swoole-json

Включить поддержку [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode), начиная с Swoole5 по умолчанию включено

> Зависит от расширения json, доступна с версии 4.5.7

#### --enable-swoole-curl

Включить поддержку [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl), для включения этой функции необходимо убедиться, что PHP и Swoole используют одну и ту же библиотеку libcurl и заголовочные файлы, иначе могут возникнуть непредвиденные проблемы.

> Доступно с версии 4.6.0. Если при сборке возникают ошибки curl/curl.h: No such file or directory, смотрите [Install issues](/question/install?id=libcurl)

#### --enable-cares

Включить поддержку c-ares

> Зависит от библиотеки c-ares, доступна с версии 4.7.0. Если при сборке возникают ошибки ares.h: No such file or directory, смотрите [Install issues](/question/install?id=libcares)

#### --with-jemalloc-dir

Включить поддержку jemalloc

#### --enable-brotli

Включить поддержку сжатия libbrotli

#### --with-brotli-dir

Включить поддержку сжатия libbrotli и указать путь к библиотеке libbrotli, следует следовать за параметрами пути, например: --with-brotli-dir=/opt/brotli/

#### --enable-swoole-pgsql

Включить поддержку协程изирования Postgres

> До Swoole5.0 для Postgres использовались协程 клиенты для协程изирования, после Swoole5.1 можно использовать как协程 клиенты, так и нативные pdo_pgsql для协程изирования Postgres.

#### --with-swoole-odbc

Включить поддержку协程изирования pdo_odbc, после включения этого параметра все базы данных, поддерживающие интерфейс odbc, будут协程изированы.

Доступно с версии 5.1.0, требует зависимости unixodbc-dev

Пример конфигурации

```
with-swoole-odbc="unixODBC,/usr"
```

#### --with-swoole-oracle

Включить поддержку协程изирования pdo_oci, после включения этого параметра все операции с базой данных Oracle будут выполняться в режиме координации.

Доступно с версии 5.1.0

#### --enable-swoole-sqlite

Включить поддержку协程изирования pdo_sqlite, после включения этого параметра все операции с базой данных SQLite будут выполняться в режиме координации.

Доступно с версии 5.1.0

#### --enable-swoole-thread

Включить многопроцессный однопоточный режим Swoole, после добавления этогоcompilerного опции Swoole будет перейти от модели многопроцессов-однопоточных к модели однопроцессов-многопоточных.

Доступно с версии 6.0, и PHP должен работать в режиме ZTS

#### --enable-iouring

После добавления этойcompilerной опции асинхронное обработка файлов в Swoole будет перешла от режима асинхронных线程 к режиму iouring.

Доступно с версии 6.0, и необходимо установить зависимость liburing для поддержки этой функции, если производительность диска хороша, то в обоих режимах производительность не сильно отличается, но при высокой нагрузке на I/O режим iouring будет лучше, чем асинхронные线程.
### Особые параметры

!> **По是没有 историческим причинам не рекомендуется включать**

#### --enable-mysqlnd

Включить поддержку `mysqlnd`, активировать метод `Coroutine\MySQL::escape`. После включения этого параметра `PHP` должен иметь модуль `mysqlnd`, иначе `Swoole` не сможет работать.

> Зависимость от расширения `mysqlnd`

#### --enable-sockets

Увеличить поддержку ресурсов `sockets` в `PHP`. После включения этого параметра, [Swoole\Event::add](/event?id=add) может добавить соединения, созданные расширением `sockets`, в [цикл событий](/learn?id=что такоеeventloop) `Swoole`.  
Методы [getSocket()](/server/methods?id=getsocket) для `Server` и `Client` также зависят от этого compiler-параметра.

> Зависимость от расширения `sockets`, после версии `v4.3.2` функция этого параметра ослаблена, поскольку встроенные в Swoole [Coroutine\Socket](/coroutine_client/socket) могут выполнять большую часть задач


### Параметры для отладки

!> **Не следует включать в производственной среде**

#### --enable-debug

Включить режим отладки. Для отслеживания необходимо добавить этот параметр при сборке `Swoole`.

#### --enable-debug-log

Включить日志内核 DEBUG. **（Версия Swoole >= 4.2.0）**

#### --enable-trace-log

Включить лог отслеживания, после включения этой опции Swoole будет печатать различные подробные отладочные логи, используется только для разработки ядра

#### --enable-swoole-coro-time

Включить расчёт времени выполнения корутин, после включения этой опции можно использовать Swoole\Coroutine::getExecuteTime() для расчёта времени выполнения корутин, исключая время ожидания I/O.


### Параметры для сборки PHP

#### --enable-swoole

Статически скомпилировать расширение Swoole в PHP, следуя следующим действиям, можно увидеть опцию `--enable-swoole`.

```shell
cp -r /home/swoole-src /home/php-src/ext
cd /home/php-src
./buildconf --force
./configure --help | grep swoole
```

!> Эта опция используется при сборке PHP, а не Swoole

## Часто задаваемые вопросы

* [Часто задаваемые вопросы о установке Swoole](/question/install)
