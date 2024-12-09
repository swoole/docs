# Swoole Installation

Die `Swoole` Erweiterung wird nach den `PHP` Standarderweiterungen gebaut. Verwenden Sie `phpize`, um ein Compilationscheck-Script zu generieren, `./configure` für die Compilationsconfigurierungsprüfung, `make` für die Kompilierung und `make install` für die Installation.

* Wenn keine besonderen Anforderungen bestehen, ist es sehr ratsam, die neueste [Swoole](https://github.com/swoole/swoole-src/releases/) Version von Swoole zu kompilieren und zu installieren.
* Wenn der aktuelle Benutzer nicht `root` ist, könnte es sein, dass er keine Schreibrechte für den PHP-Installationsverzeichnis hat, und bei der Installation ist `sudo` oder `su` erforderlich.
* Wenn Sie direkt von einem `git`-Branch aus Code ziehen und aktualisieren, müssen Sie vor der erneuten Kompilation unbedingt `make clean` ausführen.
* Unterstützt werden nur Linux (mit Kernel 2.3.32 oder höher), FreeBSD und MacOS.
* Für niedrige Linux-Systeme (z.B. CentOS 6) kann die von RedHat bereitgestellte `devtools` für die Kompilation verwendet werden, [Referenzdokument](https://blog.csdn.net/ppdouble/article/details/52894271).
* Unter Windows kann man WSL (Windows Subsystem for Linux) oder CygWin verwenden.
* Einige Erweiterungen sind nicht mit der Swoole-Erweiterung kompatibel, siehe [Erweiterungskonflikte](/getting_started/extension).


## Vorbereitungen zur Installation

Vor der Installation muss sichergestellt sein, dass das System bereits die folgenden Software installiert hat



- Für Version 4.8 ist PHP-7.2 oder höher erforderlich

- Für Version 5.0 ist PHP-8.0 oder höher erforderlich

- Für Version 6.0 ist PHP-8.1 oder höher erforderlich

- GCC-4.8 oder höher

- make

- autoconf


## Schnellinstallation

> 1. Herunterladen des Swoole-Quellcodes

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2. Aus Quellcode kompilieren und installieren

Nachdem Sie das Quellcode-Paket heruntergeladen haben, gehen Sie in den Quellcode-Verzeichnis im Terminal und führen Sie die folgenden Befehle aus, um zu kompilieren und zu installieren

!> Ubuntu hat kein phpize-Befehl installiert: `sudo apt-get install php-dev` um phpize zu installieren

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3. Erweiterung aktivieren

Nach erfolgreicher Installation in das System muss eine Zeile `extension=swoole.so` in der `php.ini` hinzugefügt werden, um die Swoole-Erweiterung zu aktivieren


## Vollständiges Beispiel für erweiterte Kompilation

!> Entwickler, die Swoole zum ersten Mal verwenden, sollten zuerst die oben beschriebene einfache Kompilation versuchen. Wenn weitere Bedürfnisse bestehen, können Sie je nach Bedarf und Version die im folgenden Beispiel genannten Kompilationsparameter anpassen. [Referenz für Kompilationsoptionen](/environment?id=编译选项)

Der folgende Befehl lädt und kompilieren Sie die Quellcode der `master`-Branche herunter. Bitte stellen Sie sicher, dass Sie alle Abhängigkeiten installiert haben, sonst stoßen Sie auf verschiedene Abhängigkeitsfehler.

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

> Hinweis: Die Veröffentlichungszeit von PECL ist später als die Veröffentlichungszeit von GitHub

Das Swoole-Projekt wurde in die offizielle PHP-Erweiterungsliste aufgenommen. Neben der manuellen Download- und Kompilierung kann es auch über den von PHP bereitgestellten `pecl`-Befehl installiert werden.

```shell
pecl install swoole
```

Beim Ausführen von PECL zum Installieren von Swoole wird es nach der Installation nachfrage, ob einige Funktionen aktiviert werden sollen. Dies kann auch vor der Ausführung der Installation angegeben werden, zum Beispiel:

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#oder
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```


## Swoole in php.ini hinzufügen

Zum Schluss, nachdem die Kompilation und Installation erfolgreich waren, fügen Sie in die `php.ini` hinzu

```ini
extension=swoole.so
```

Um zu überprüfen, ob `swoole.so` erfolgreich geladen wurde, verwenden Sie `php -m`. Wenn es nicht geladen wurde, könnte dies daran liegen, dass der Pfad zur `php.ini` falsch ist.  
Sie können `php --ini` verwenden, um den absoluten Pfad zur `php.ini` zu ermitteln. Das Element `Loaded Configuration File` zeigt den geladenen `php.ini`-Datei an. Wenn der Wert `none` ist, bedeutet dies, dass keine `php.ini`-Datei geladen wurde, und Sie müssen eine erstellen.

!> Die Unterstützung für die PHP-Version ist konsistent mit der von PHP offiziell gepflegten Version, siehe [Tabelle für die Unterstützung verschiedener PHP-Versionen](http://php.net/supported-versions.php)


## Andere Plattformen für die Kompilation

ARM-Plattform (Raspberry PI)

* Verwenden Sie GCC zur Kreuzkompilierung
* Beim Kompilieren von Swoole müssen Sie den Makefile manuell ändern, um die `-O2` Kompilationsoption zu entfernen

MIPS-Plattform (OpenWrt Router)

* Verwenden Sie GCC zur Kreuzkompilierung

Windows WSL

Das `Windows 10` System bietet Unterstützung für das Linux-Subsystem, und unter der Umgebung `BashOnWindows` kann auch Swoole verwendet werden. Installationsbefehl

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> Unter WSL muss die Option `daemonize` deaktiviert werden  
Bei WSL unterversion 17101 muss nach der编译 configuration der configureoption `HAVE_SIGNALFD` in config.h entfernt werden


## Docker-Offizielles Bild



- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)  
- dockerhub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)


## Kompilationsoptionen

Hier sind zusätzliche Parameter für die ./configure-Kompilationsconfigurierung, die zum Aktivieren bestimmter Funktionen verwendet werden können


### Allgemeine Parameter

#### --enable-openssl

Aktivieren Sie die Unterstützung für `SSL`

> Verwenden Sie die von der Betriebssystem bereitgestellte `libssl.so`-Dynamikverbindungslibrary

#### --with-openssl-dir

Aktivieren Sie die Unterstützung für `SSL` und geben Sie den Pfad zur `openssl`-Bibliothek an, folgen Sie dem Pfadparameter, zum Beispiel: `--with-openssl-dir=/opt/openssl/`

#### --enable-http2

Aktivieren Sie die Unterstützung für `HTTP2`

> Ab Version `V4.3.0` ist keine Abhängigkeit mehr erforderlich, sie ist integriert, aber Sie müssen diesen Kompilationsparameter hinzufügen, um die Unterstützung für `http2` zu aktivieren. Swoole5 aktiviert diese Option standardmäßig.

#### --enable-swoole-json

Aktivieren Sie die Unterstützung für [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode). Ab Swoole5 wird diese Option standardmäßig aktiviert.

> Ab Version `v4.5.7` ist die `json`-Erweiterung erforderlich.

#### --enable-swoole-curl

Aktivieren Sie die Unterstützung für [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl). Um dies zu aktivieren, müssen Sie sicherstellen, dass sowohl `php` als auch `Swoole` die gleiche `libcurl`-Freigabebibliothek und Kopfdateien verwenden, sonst können unerwartete Probleme auftreten.

> Ab Version `v4.6.0` verfügbar. Wenn beim Kompilieren der Fehler `curl/curl.h: No such file or directory` auftritt, sehen Sie sich [Installationsprobleme](/question/install?id=libcurl) an.

#### --enable-cares

Aktivieren Sie die Unterstützung für `c-ares`

> Ab Version `v4.7.0` ist die `c-ares`-Bibliothek erforderlich. Wenn beim Kompilieren der Fehler `ares.h: No such file or directory` auftritt, sehen Sie sich [Installationsprobleme](/question/install?id=libcares) an.

#### --with-jemalloc-dir

Aktivieren Sie die Unterstützung für `jemalloc`

#### --enable-brotli

Aktivieren Sie die Unterstützung für die `libbrotli`-Komprimierung

#### --with-brotli-dir

Aktivieren Sie die Unterstützung für `libbrotli`-Komprimierung und geben Sie den Pfad zur `libbrotli`-Bibliothek an, folgen Sie dem Pfadparameter, zum Beispiel: `--with-brotli-dir=/opt/brotli/`

#### --enable-swoole-pgsql

Aktivieren Sie die协程isierung der `PostgreSQL`-Datenbank.

> Vor Swoole5.0 wurde die `PostgreSQL`-Datenbank协程isiert, indem ein协程-Client verwendet wurde. Ab Swoole5.1 kann neben dem Einsatz eines协程-Clients auch die native `pdo_pgsql` zur协程isierung der `PostgreSQL`-Datenbank verwendet werden.

#### --with-swoole-odbc

Aktivieren Sie die协程isierung für `pdo_odbc`. Nachdem diese Option aktiviert wurde, können alle Datenbanken, die die `odbc`-Schnittstelle unterstützen,协程isiert werden.



>`v5.1.0` verfügbar, abhängig von unixodbc-dev

Beispielkonfiguration

```
with-swoole-odbc="unixODBC,/usr"
```

#### --with-swoole-oracle

Aktivieren Sie die协程isierung für `pdo_oci`. Nachdem diese Option aktiviert wurde, werden alle insert-, update-, delete- und select-Operationen an der `oracle`-Datenbank asynchron über Coroutine durchgeführt.

>`v5.1.0` verfügbar

#### --enable-swoole-sqlite

Aktivieren Sie die协程isierung für `pdo_sqlite`. Nachdem diese Option aktiviert wurde, werden alle insert-, update-, delete- und select-Operationen an der `sqlite`-Datenbank asynchron über Coroutine durchgeführt.

>`v5.1.0` verfügbar

#### --enable-swoole-thread

Aktivieren Sie das Mehr线程-Modell von Swoole. Nachdem diese Kompilationsoption hinzugefügt wurde, wird Swoole von einem Prozess mit mehreren Threads zu einem Prozess mit einer Thread-Pool umgewandelt.

>`v6.0` verfügbar und PHP muss im ZTS (Zend Thread Safety)-Modus laufen

#### --enable-iouring

Nachdem diese Kompilationsoption hinzugefügt wurde, wird die asynchrone Dateiverarbeitung von Swoole von asynchronen Threads zu einem `iouring`-Modus umgewandelt.

>`v6.0` verfügbar und es ist eine Abhängigkeit von `liburing` erforderlich, um diese Funktion zu unterstützen. Wenn die Datenträgheit gut ist, ist der Unterschied in den Leistungen zwischen beiden Modi nicht groß, aber bei hohem I/O-Druck ist der `iouring`-Modus im Vergleich zum asynchronen Thread-Modus in der Regel effizienter.
### Sonderparameter

!> **Ohne historische Gründe wird empfohlen, dies nicht zu aktivieren**

#### --enable-mysqlnd

Aktivieren Sie die Unterstützung für `mysqlnd`, um die Methode `Coroutine\MySQL::escapse` zu aktivieren. Nachdem dieser Parameter aktiviert wurde, muss PHP den `mysqlnd`-Modul haben, sonst wird Swoole nicht funktionieren können.

> Abhängigkeit von der `mysqlnd`-Erweiterung

#### --enable-sockets

Erhöhen Sie die Unterstützung für PHP-`sockets`-Ressourcen. Wenn dieser Parameter aktiviert ist, können Sie mit `[Swoole\Event::add](/event?id=add)` Verbindungen, die von der `sockets`-Erweiterung erstellt wurden, zur [Ereigneschleife](/learn?id=Was_ist_eine_Ereigneschleife) von Swoole hinzufügen.  
Die [getSocket()](/server/methods?id=getsocket)-Methoden von `Server` und `Client` sind ebenfalls von diesem编译parameter abhängig.

> Abhängigkeit von der `sockets`-Erweiterung, die Funktion dieses Parameters wurde nach Version `v4.3.2` abgeschwächt, da das in Swoole integrierte [Coroutine\Socket](/coroutine_client/socket) die meisten Aufgaben erledigen kann


### Debug-Parameter

!> **In Produktionsumgebung darf dies nicht aktiviert werden**

#### --enable-debug

Aktivieren Sie den Debugmodus. Um mit `gdb` die追踪 zu führen, muss dieser Parameter bei der编译 von Swoole hinzugefügt werden.

#### --enable-debug-log

Aktivieren Sie das Kernel DEBUG-Log. **（Swoole-Version >= 4.2.0）**

#### --enable-trace-log

Aktivieren Sie das Trace-Log, nachdem diese Option aktiviert wurde, wird Swoole verschiedene detaillierte Debug-Logs ausgeben, die nur für Kernel-Entwicklung verwendet werden sollten.

#### --enable-swoole-coro-time

Aktivieren Sie die Berechnung der Coroutine-Laufzeit. Nachdem diese Option aktiviert wurde, können Sie die Ausführungzeit von Swoole\Coroutine::getExecuteTime() verwenden, um die Laufzeit der Coroutine zu berechnen, einschließlich I/O-Wartetzeiten nicht inbegriffen.


### PHP-Compilerparameter

#### --enable-swoole

Statische编译 Swoole als Erweiterung in PHP, nach den folgenden Schritten sollte die Option `--enable-swoole` erscheinen.

```shell
cp -r /home/swoole-src /home/php-src/ext
cd /home/php-src
./buildconf --force
./configure --help | grep swoole
```

!> Diese Option wird verwendet, wenn PHP und nicht Swoole编译

## Häufige Fragen

* [Häufige Fragen zur Installation von Swoole](/question/install)
