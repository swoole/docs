# Einrichten von Problemen


## Swoole-Version upgraden

Möglich ist der Einbau und die Überprüfung mit `pecl`

```shell
pecl upgrade swoole
```

Man kann auch eine neue Version direkt von github/gitee/pecl downloaden, um sie neu zu installieren und zu kompilieren.

* Bei der Aktualisierung der Swoole-Version muss keine alte Version des Swoole uninstalliert oder gelöscht werden, da das Installationsverfahren die alte Version überschreibt
* Nach der Kompilierung und Installation von Swoole gibt es keine zusätzlichen Dateien, nur einen swoole.so, wenn es sich um eine kompilierte Binärversion auf einer anderen Maschine handelt. Es reicht aus, den swoole.so einfach zu überschreiben, um die Version zu wechseln  
* Wenn man Code mit `git clone` zieht und nach dem Ausführen von `git pull` den Code aktualisiert, muss man unbedingt `phpize`, `./configure`, `make clean` und `make install` erneut ausführen
* Man kann auch entsprechende Docker-Images verwenden, um die entsprechende Swoole-Version zu aktualisieren


## In phpinfo ist es vorhanden, aber nicht in php -m

Zuerst überprüfen Sie, ob es im CLI-Modus vorhanden ist, indem Sie den Befehl `php --ri swoole` eingeben

Wenn Sie Informationen zur Swoole-Erweiterung ausgeben, bedeutet dies, dass Sie erfolgreich installiert haben!

**99,999% der Menschen schaffen es in diesem Schritt, Swoole direkt zu verwenden**

Es ist nicht notwendig, sich darum zu kümmern, ob `php -m` oder die phpinfo-Webseite Swoole auflistet

Denn Swoole wird im CLI-Modus ausgeführt, und seine Funktionen sind im traditionellen FPM-Modus sehr begrenzt

In der FPM-Modus können alle Hauptfunktionen wie asynchron/koordination **nicht verwendet** werden, und 99,999% der Menschen können nicht das erhalten, was sie im FPM-Modus wollen, und sind verwirrt darüber, warum keine Erweiterungsinformationen im FPM-Modus erscheinen

**Bestimmen Sie zuerst, ob Sie wirklich ein Verständnis für das Betriebsmodus von Swoole haben, bevor Sie sich weiter mit Installationsproblemen beschäftigen!**


### Ursache

Nach der Kompilierung und Installation von Swoole ist es im `php-fpm/apache` phpinfo-Seiten vorhanden, aber nicht im `php -m`-Befehl, was möglicherweise daran liegt, dass `cli/php-fpm/apache` unterschiedliche php.ini-Konfigurationsdateien verwenden


### Lösung

1. Stellen Sie sicher, wo die php.ini-Datei ist

Führen Sie unter dem `cli`-Befehl `php -i | grep php.ini` oder `php --ini` aus, um den absoluten Pfad zur php.ini-Datei zu finden

Für `php-fpm/apache` sehen Sie im phpinfo-Seiten den absoluten Pfad zur php.ini-Datei

2. Stellen Sie sicher, ob die entsprechende php.ini die Zeile `extension=swoole.so` enthält

```shell
cat /path/to/php.ini | grep swoole.so
```


## pcre.h: No such file or directory

Beim Kompilieren der Swoole-Erweiterung tritt das Problem auf

```bash
fatal error: pcre.h: No such file or directory
```

Der Grund ist das Fehlen von pcre, man muss libpcre installieren


### ubuntu/debian

```shell
sudo apt-get install libpcre3 libpcre3-dev
```

### centos/redhat

```shell
sudo yum install pcre-devel
```


### Andere Linux

Laden Sie die Quellcode-Packung von der [PCRE-Website](http://www.pcre.org/) herunter, kompilieren und installieren Sie die `pcre`-Bibliothek.

Nach der Installation der `PCRE`-Bibliothek müssen Sie Swoole erneut kompilieren und installieren, und dann die相关信息 zur Swoole-Erweiterung mit `php --ri swoole` überprüfen, ob `pcre => enabled` enthalten ist


## '__builtin_saddl_overflow' wurde in diesem Bereich nicht deklariert

 ```
error: '__builtin_saddl_overflow' wurde in diesem Bereich nicht deklariert
  if (UNEXPECTED(__builtin_saddl_overflow(Z_LVAL_P(op1), 1, &lresult))) {

note: in Definition of macro 'UNEXPECTED'
 # define UNEXPECTED(condition) __builtin_expect(!!(condition), 0)
```

Dies ist ein bekanntes Problem. Das Problem ist, dass der Standard-gcc auf CentOS die notwendigen Definitionen nicht bereitstellt, selbst nach dem Upgrade auf gcc findet PECL immer noch den alten Compiler.

Um die Treiber zu installieren, müssen Sie zuerst die devtoolset-Sammlung durch die Installation von centos-release-scl auf升级为 gcc, wie folgt gezeigt:

```shell
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```


## fatal error: 'openssl/ssl.h' file not found

Bitte fügen Sie beim Kompilieren den [--with-openssl-dir](/environment?id=allgemeine Parameter) Parameter hinzu, um den Pfad zur openssl-Bibliothek anzugeben

!> Wenn Sie Swoole mit [pecl](/environment?id=pecl) installieren und openssl-Unterstützung benötigen, können Sie auch den [--with-openssl-dir](/environment?id=allgemeine Parameter) Parameter hinzufügen, wie zum Beispiel: `enable openssl support? [no] : yes --with-openssl-dir=/opt/openssl/`


## make oder make install kann nicht ausgeführt oder kompilieren Fehler

BEWEIS: PHP-Nachricht: PHP Warning:  PHP Startup: swoole: Unable to initialize module  
Modul wurde mit Modul-API=20090626  
PHP wurde mit Modul-API=20121212  
Diese Optionen müssen übereinstimmen  
in Unbekannt auf Zeile 0  
   
Die PHP-Version und die bei der Kompilierung verwendeten `phpize` und `php-config` stimmen nicht überein, Sie müssen die Kompilierung mit absoluten Pfaden durchführen und PHP mit absoluten Pfaden ausführen.

```shell
/usr/local/php-5.4.17/bin/phpize
./configure --with-php-config=/usr/local/php-5.4.17/bin/php-config

/usr/local/php-5.4.17/bin/php server.php
```


## Installation von xdebug

```shell
git clone git@github.com:swoole/sdebug.git -b sdebug_2_9 --depth=1

cd sdebug

phpize
./configure
make clean
make
make install

#Wenn Ihre phpize, php-config und andere Konfigurationsdateien standardmäßig sind, können Sie dies direkt ausführen
./rebuild.sh
```

Ändern Sie die php.ini, um die Erweiterung zu laden, und fügen Sie die folgenden Informationen hinzu

```ini
zend_extension=xdebug.so

xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=localhost
xdebug.remote_port=8000
xdebug.idekey="xdebug"
```

Überprüfen Sie, ob es erfolgreich geladen wurde

```shell
php --ri sdebug
```


## configure: error: C preprocessor "/lib/cpp" fails sanity check

Beim Installieren tritt der Fehler auf

```shell
configure: error: C preprocessor "/lib/cpp" fails sanity check
```

Dies bedeutet, dass fehlende Abhängigkeitsbibliotheken erforderlich sind, die mit dem folgenden Befehl installiert werden können

```shell
yum install glibc-headers
yum install gcc-c++
```


## PHP7.4.11+ Kompilierung neuer Versionen von Swoole mit Fehler asm goto :id=asm_goto

Beim Kompilieren neuer Versionen von Swoole mit PHP7.4.11+ auf MacOS wird der folgende Fehler festgestellt:

```shell
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:523:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:586:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:656:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:766:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
4 errors generated.
make: *** [ext-src/php_swoole.lo] Error 1
ERROR: `make' failed
```

Lösung: Ändern Sie die `/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h`-Quelldatei, beachten Sie, dass Sie den entsprechenden Headerpfad für sich selbst ändern müssen;

Ändern Sie `ZEND_USE_ASM_ARITHMETIC` in `0` zu einem konstanten Wert, also bewahren Sie den `else`-Teil des folgenden Codes bei

```c
#if defined(HAVE_ASM_GOTO) && !__has_feature(memory_sanitizer)
# define ZEND_USE_ASM_ARITHMETIC 1
#else
# define ZEND_USE_ASM_ARITHMETIC 0
#endif
```


## fatal error: curl/curl.h: No such file or directory :id=libcurl

Wenn Sie die `--enable-swoole-curl`-Option aktivieren, tritt beim Kompilieren der Swoole-Erweiterung der Fehler auf

```bash
fatal error: curl/curl.h: No such file or directory
```

Der Grund ist das Fehlen der curl-Abhängigkeit, man muss libcurl installieren


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

## fatale Fehler: ares.h: No such file or directory :id=libcares

Beim Aktivieren der `--enable-cares` Option tritt beim Kompilieren der Swoole-Erweiterung ein

```bash
fatal error: ares.h: No such file or directory
```

Der Grund ist das Fehlen der c-ares Abhängigkeit, die libcares installiert werden muss

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
