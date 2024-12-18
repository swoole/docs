# Установка проблем


## Обновление версии Swoole

Можно использовать pecl для установки и обновления

```shell
pecl update swoole
```

Также можно скачать новую версию напрямую с github/gitee/pecl, пересоздать и пересмотреть.

* Обновление версии Swoole не требует демонтажа или удаления старой версии Swoole, процесс установки перекроет старую версию
* После компиляции и установки Swoole не создаются дополнительные файлы, есть только swoole.so, если это заранее скомпилированная binaционная версия на другой машине. Просто перекройте swoole.so друг с другом, чтобы изменить версию
* После того как вы clone-нули код, чтобы обновить код, вы должны снова выполнить `phpize`, `./configure`, `make clean`, `make install`
* Также можно использовать соответствующий docker для обновления соответствующей версии Swoole


## В phpinfo есть, а в php -m нет

Сначала убедитесь, что есть в режиме CLI, введите в командной строке `php --ri swoole`

Если вы видите информацию о расширении Swoole, значит, вы успешно установили его!

**99.999% людей на этом этапе могут уже использовать swoole**

Не нужно беспокоиться о том, есть ли swoole в `php -m` или на веб-странице phpinfo

Потому что Swoole работает в режиме CLI, и в традиционном режиме fpm его функции очень ограничены

В режиме fpm никакие основные функции, такие как асинхронность/координационные процессы, **не могут быть использованы**, и 99.999% людей не могут получить то, что хотят в режиме fpm, но беспокоятся о том, почему в режиме fpm нет информации о расширениях

**Сначала убедитесь, что вы действительно понимаете, как работает Swoole, а затем продолжайте искать проблемы с установкой информации!**


### Причины

После компиляции и установки Swoole в странице phpinfo в `php-fpm/apache` есть, а в командной строке `php -m` нет, возможно, потому что `cli/php-fpm/apache` используют разные php.ini конфигурации


### Решение

1. Убедитесь в местоположении php.ini

В командной строке CLI выполните `php -i | grep php.ini` или `php --ini`, чтобы найти абсолютный путь к php.ini

Для `php-fpm/apache` посмотрите на страницу phpinfo, чтобы найти абсолютный путь к php.ini

2. Смотрите, есть ли в соответствующем php.ini `extension=swoole.so`

```shell
cat /path/to/php.ini | grep swoole.so
```


## pcre.h: Не найден такой файл или директория

Возникает при компиляции расширения Swoole

```bash
fatal error: pcre.h: Не найден такой файл или директория
```

Причина в отсутствии pcre, необходимо установить libpcre


### ubuntu/debian

```shell
sudo apt-get install libpcre3 libpcre3-dev
```

### centos/redhat

```shell
sudo yum install pcre-devel
```


### Другие Linux

Скачайте исходный пакет с официального сайта PCRE [http://www.pcre.org/](http://www.pcre.org/), скомпилируйте и установите библиотеку pcre.

После установки библиотеки PCRE необходимо пересоздать и пересмотреть Swoole, затем используйте `php --ri swoole` чтобы проверить информацию о расширении Swoole и увидеть, есть ли там `pcre => enabled`


## '__builtin_saddl_overflow' не объявлен в этом контексте

 ```
error: '__builtin_saddl_overflow' не объявлен в этом контексте
  if (UNEXPECTED(__builtin_saddl_overflow(Z_LVAL_P(op1), 1, &lresult))) {

note: в определении макроса 'UNEXPECTED'
 # define UNEXPECTED(condition) __builtin_expect(!!(condition), 0)
```

Это известная проблема. Проблема заключается в том, что по умолчанию gcc на CentOS не содержит необходимые определения, и даже после обновления gcc PECL все равно найдет старый компилятор.

Чтобы установить драйвер, необходимо сначала обновить gcc, установив набор devtoolset, как показано ниже:

```shell
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```


## fatal error: 'openssl/ssl.h' файл не найден

Пожалуйста, добавьте параметр [--with-openssl-dir](/environment?id=общие параметры) при компиляции, чтобы указать путь к библиотеке openssl

!> При установке Swoole с помощью [pecl](/environment?id=pecl), если вы хотите включить openssl, также можно добавить параметр [--with-openssl-dir](/environment?id=общие параметры), например: `включить поддержку openssl? [нет] : да --with-openssl-dir=/opt/openssl/`


## make или make install не могут быть выполнены или возникают ошибки при компиляции

Уведомление: PHP сообщение: PHP Warning:  PHP Startup: swoole: Невозможно инициализировать модуль  
Модуль собран с модульной API=20090626  
PHP    собран с модульной API=20121212  
Эти опции должны соответствовать  
в Неизвестном на линии 0  
   
Версия PHP и использованные при сборке `phpize` и `php-config` не соответствуют друг другу, необходимо использовать абсолютные пути для сборки и выполнения PHP.

```shell
/usr/local/php-5.4.17/bin/phpize
./configure --with-php-config=/usr/local/php-5.4.17/bin/php-config

/usr/local/php-5.4.17/bin/php server.php
```


## Установка xdebug

```shell
git clone git@github.com:swoole/sdebug.git -b sdebug_2_9 --depth=1

cd sdebug

phpize
./configure
make clean
make
make install

# Если ваши phpize, php-config и другие конфигурационные файлы стандартные, то вы можете просто выполнить
./rebuild.sh
```

Изменить php.ini, чтобы добавить расширение, добавьте следующую информацию

```ini
zend_extension=xdebug.so

xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=localhost
xdebug.remote_port=8000
xdebug.idekey="xdebug"
```

Проверьте, успешно ли оно загружено

```shell
php --ri sdebug
```


## configure: error: C preprocessor "/lib/cpp" не проходит проверку умственной чистоты

При установке возникают ошибки

```shell
configure: error: C preprocessor "/lib/cpp" не проходит проверку умственной чистоты
```

Это означает отсутствие необходимых зависимых библиотек, можно установить их с помощью следующих команд

```shell
yum install glibc-headers
yum install gcc-c++
```


## PHP7.4.11+ при компиляции новой версии Swoole возникает ошибка asm goto :id=asm_goto

При использовании PHP7.4.11+ на MacOS при компиляции новой версии Swoole возникают ошибки, подобные следующим:

```shell
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:523:10: error: 'asm goto' конструкции еще не поддерживаются
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:586:10: error: 'asm goto' конструкции еще не поддерживаются
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:656:10: error: 'asm goto' конструкции еще не поддерживаются
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:766:10: error: 'asm goto' конструкции еще не поддерживаются
        __asm__ goto(
                ^
4 ошибки генерированы.
make: *** [ext-src/php_swoole.lo] Error 1
ERROR: `make' failed
```

Решение: Измените исходный код `/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h`, обратите внимание на изменение на путь к своему соответствующему заголовочному файлу;

Измените `ZEND_USE_ASM_ARITHMETIC` на постоянное значение `0`, то есть сохраните содержание `else` в следующем коде

```c
#if defined(HAVE_ASM_GOTO) && !__has_feature(memory_sanitizer)
# define ZEND_USE_ASM_ARITHMETIC 1
#else
# define ZEND_USE_ASM_ARITHMETIC 0
#endif
```


## fatal error: curl/curl.h: Не найден такой файл или директория :id=libcurl

После включения опции `--enable-swoole-curl` при компиляции расширения Swoole возникает

```bash
fatal error: curl/curl.h: Не найден такой файл или директория
```

Причина в отсутствии зависимости curl, необходимо установить libcurl


### ubuntu/debian

```shell
sudo apt-get install libcurl4-openssl-dev
```
### centos/redhat

```shell
sudo yum install libcurl-devel
```

### alpine

```shell
apk add curl-dev
```

## fatal error: ares.h: Нет такого файла или каталога :id=libcares

При включении опции `--enable-cares` при сборке расширений Swoole возникает

```bash
fatal error: ares.h: Нет такого файла или каталога
```

Причина - отсутствие зависимости c-ares, необходимо установить libcares

### ubuntu/debian

```shell
sudo apt-get install libc-ares-dev
```

### centos/redhat

```shell
sudo yum install c-ares-devel
```

### alpine

```shell
apk add c-ares-dev
```

### MacOs

```shell
brew install c-ares
```
