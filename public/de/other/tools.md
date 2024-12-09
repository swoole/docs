# Werkzeuganwendung


## yasd

[yasd](https://github.com/swoole/yasd)

Ein Einzelschritt-Debuggingswerkzeug, das für die `Swoole`-Coroutine-Umwelt geeignet ist und sowohl in `IDE` als auch im Kommandozeilen-Debuggingsmodus unterstützt wird.


## tcpdump

tcpdump ist ein unverzichtbares Werkzeug bei der debugging von Netzwerkcommutationsprogrammen. tcpdump ist sehr mächtig und kann jedes Detail der Netzwerkkommunikation sehen. Zum Beispiel kann man bei TCP die dreifache Handshake sehen, PUSH/ACK-Datenpush, den vierfachen CLOSE-Wisch und alle Details. Dazu gehören auch die Anzahl der Bytes und die Zeit für jedes Netzwerkpaket.


### Gebrauchsanweisung

Ein einfacher Gebrauchsanweisungsbeispiel:

```shell
sudo tcpdump -i any tcp port 9501
```
* Der `-i` Parameter legt die Netzwerkschnittstelle fest, `any` bedeutet alle Netzwerkschnittstellen
* `tcp` legt fest, dass nur der TCP-Protokoll überwacht wird
* `port` legt den zu überwachenden Port fest

!> tcpdump benötigt Root-Berechtigungen; um den Inhalt der Kommunikation zu sehen, kann der `-Xnlps0` Parameter hinzugefügt werden, weitere Parameter finden Sie in Online-Artikeln


### Betriebsergebnis

```
13:29:07.788802 IP localhost.42333 > localhost.9501: Flags [S], seq 828582357, win 43690, options [mss 65495,sackOK,TS val 2207513 ecr 0,nop,wscale 7], length 0
13:29:07.788815 IP localhost.9501 > localhost.42333: Flags [S.], seq 1242884615, ack 828582358, win 43690, options [mss 65495,sackOK,TS val 2207513 ecr 2207513,nop,wscale 7], length 0
13:29:07.788830 IP localhost.42333 > localhost.9501: Flags [.], ack 1, win 342, options [nop,nop,TS val 2207513 ecr 2207513], length 0
13:29:10.298686 IP localhost.42333 > localhost.9501: Flags [P.], seq 1:5, ack 1, win 342, options [nop,nop,TS val 2208141 ecr 2207513], length 4
13:29:10.298708 IP localhost.9501 > localhost.42333: Flags [.], ack 5, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 0
13:29:10.298795 IP localhost.9501 > localhost.42333: Flags [P.], seq 1:13, ack 5, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 12
13:29:10.298803 IP localhost.42333 > localhost.9501: Flags [.], ack 13, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 0
13:29:11.563361 IP localhost.42333 > localhost.9501: Flags [F.], seq 5, ack 13, win 342, options [nop,nop,TS val 2208457 ecr 2208141], length 0
13:29:11.563450 IP localhost.9501 > localhost.42333: Flags [F.], seq 13, ack 6, win 342, options [nop,nop,TS val 2208457 ecr 2208457], length 0
13:29:11.563473 IP localhost.42333 > localhost.9501: Flags [.], ack 14, win 342, options [nop,nop,TS val 2208457 ecr 2208457], length 0
```
* `13:29:11.563473` Die Zeit ist bis auf die Mikrosekunde genau
*  localhost.42333 > localhost.9501 zeigt die Richtung der Kommunikation, 42333 ist der Client, 9501 ist der Server
* [S] bedeutet, dass dies eine SYN-Anfrage ist
* [.] bedeutet, dass dies eine ACK-Bestätigungspaket ist, (client)SYN->(server)SYN->(client)ACK ist der dreifache Handshakeprozess
* [P] bedeutet, dass dies eine Datenpush ist, die sowohl vom Server zum Client als auch vom Client zum Server sein kann
* [F] bedeutet, dass dies ein FIN-Paket ist, ein Operation zur Schließung der Verbindung, sowohl Client als auch Server können dies initiieren
* [R] bedeutet, dass dies ein RST-Paket ist, ähnelt dem F-Paket, aber RST bedeutet, dass bei Schließung der Verbindung immer noch unbehandelte Daten vorhanden sind. Es kann als Zwangsabbrechung der Verbindung verstanden werden
* win 342 bezieht sich auf die Größe des Schiebewindors
* length 12 bezieht sich auf die Größe des Datapakets


## strace

strace kann den Ausführungsstatus von Systemaufrufen verfolgen. Nach Problemen mit einem Programm kann strace zur Analyse und Verfolgung des Problems verwendet werden.

!> Unter FreeBSD/MacOS kann man truss verwenden


### Gebrauchsanweisung

```shell
strace -o /tmp/strace.log -f -p $PID
```

* `-f` bedeutet, mehr线程 und mehrprozesse zu verfolgen, ohne den `-f` Parameter kann man die运行情况 von Tochterprozessen und Tochterthreads nicht erfassen
* `-o` bedeutet, das Ergebnis in eine Datei zu schreiben
* `-p $PID` legt den zu verfolgenden Prozess-ID fest, der über `ps aux` eingesehen werden kann
* `-tt` druckt die Zeit der Systemaufrufe aus, bis auf die Mikrosekunde genau
* `-s` beschränkt die Länge der ausgegebenen Zeichenketten, zum Beispiel die von recvfrom empfangenen Daten des Systemaufrufs, standardmäßig werden nur 32 Byte gedruckt
* `-c` statistiziert die Zeit, die jeder Systemaufruf verbraucht
* `-T` druckt die Zeit, die jeder Systemaufruf verbraucht


## gdb

GDB ist ein leistungsstarkes Debuggingwerkzeug, das von der GNU-Open-Source-Organisation veröffentlicht wurde und für die Debugging von Programmen unter UNIX verwendet werden kann. Es eignet sich für die Debugging von Programmen, die in C/C++ entwickelt wurden. PHP und Swoole werden in C entwickelt, daher können sie mit GDB调试.

Das GDB-Debugging ist interaktiv über die Kommandozeile und erfordert ein gutes Verständnis der häufig verwendeten Befehle.


### Gebrauchsanweisung

```shell
gdb -p ProzessID
gdb php
gdb php core
```

Es gibt drei Möglichkeiten, GDB zu verwenden:

* Um den laufenden PHP-Programm zu verfolgen, verwenden Sie `gdb -p ProzessID`
* Um den PHP-Programm mit GDB zu laufen und zu debuggen, verwenden Sie `gdb php -> run server.php` zum Debuggen
* Nachdem ein PHP-Programm einen Core-Dump generiert hat, verwenden Sie `gdb php core` zum Debuggen

!> Wenn der `PATH` Umgebungsvariable keinen PHP enthält, muss bei GDB die absolute Pfad angegeben werden, zum Beispiel `gdb /usr/local/bin/php`


### Häufige Befehle

* `p`: print, druckt den Wert einer C-Variablen aus
* `c`: continue, setzt den ausgegebenen Programm fort
* `b`: breakpoint, setzt einen Breakpoint, kann nach Funktionennamen oder nach Zeilennummern in der Quellcode festgelegt werden, zum Beispiel `b zif_php_function` oder `b src/networker/Server.c:1000`
* `t`: thread, wechselt zur nächsten Thread, wenn ein Prozess mehrere Threads hat, kann der `t` Befehl verwendet werden, um zu einem anderen Thread zu wechseln
* `ctrl + c`: unterbricht das derzeit laufende Programm, wird zusammen mit dem `c` Befehl verwendet
* `n`: next, führt den nächsten Schritt aus, ist ein Einzelschritt-Debugging
* `info threads`: zeigt alle laufenden Threads an
* `l`: list, zeigt den Quellcode an, kann mit `l Funktionname` oder `l Zeilennummer` verwendet werden
* `bt`: backtrace, zeigt den Funktionsaufrufstapel bei der Ausführung an
* `finish`: beendet die aktuelle Funktion
* `f`: frame, wird zusammen mit `bt` verwendet, um zu einer bestimmten Ebene im Funktionsaufrufstapel zu wechseln
* `r`: run, lädt das Programm


## zbacktrace

zbacktrace ist ein benutzerdefiniertes GDB-Befehl, der in der PHP-Quellpakete bereitgestellt wird. Seine Funktion ähnelt der des `bt` Befehls, unterscheidet sich jedoch darin, dass der von zbacktrace gezeigte Aufrufstapel aus PHP-Funktionen besteht, nicht aus C-Funktionen.

Laden Sie die PHP-Quellpakete herunter, entpacken Sie sie und suchen Sie im Wurzelverzeichnis nach einer `.gdbinit`-Datei. Geben Sie im GDB-Shell folgenden Befehl ein:

```shell
source .gdbinit
zbacktrace
```
Die `.gdbinit`-Datei bietet auch viele andere Befehle, die für eine genauere Verständnis der Informationen aus der Quelle verwendet werden können.

#### Verwenden Sie GDB+zbacktrace, um ein Deadlock-Problem zu verfolgen

```shell
gdb -p ProzessID
```

* Verwenden Sie das `ps aux`-Werkzeug, um den Prozess-ID des Worker-Prozess zu finden, der ein Deadlock verursacht
* Verwenden Sie `gdb -p` um den angegebenen Prozess zu verfolgen
* Wiederholen Sie wiederholt `ctrl + c`, `zbacktrace`, `c`, um zu sehen, an welchem PHP-Code der Loop auftritt
* Identifizieren Sie den entsprechenden PHP-Code und lösen Sie das Problem

## lsof

Die Linux-Plattform bietet das Werkzeug `lsof` an, um die von einem Prozess geöffneten Dateihandles zu betrachten. Es kann verwendet werden, um alle geöffneten Sockets, Dateien und Ressourcen der Arbeitsprozesse von swoole zu verfolgen.


### Verwendungsweise

```shell
lsof -p [Prozess-ID]
```


### Ausführungsergebnis

```shell
lsof -p 26821
lsof: WARNING: can't stat() tracefs file system /sys/kernel/debug/tracing
      Output information may be incomplete.
COMMAND   PID USER   FD      TYPE             DEVICE SIZE/OFF    NODE NAME
php     26821  htf  cwd       DIR                8,4     4096 5375979 /home/htf/workspace/swoole/examples
php     26821  htf  rtd       DIR                8,4     4096       2 /
php     26821  htf  txt       REG                8,4 24192400 6160666 /opt/php/php-5.6/bin/php
php     26821  htf  DEL       REG                0,5          7204965 /dev/zero
php     26821  htf  DEL       REG                0,5          7204960 /dev/zero
php     26821  htf  DEL       REG                0,5          7204958 /dev/zero
php     26821  htf  DEL       REG                0,5          7204957 /dev/zero
php     26821  htf  DEL       REG                0,5          7204945 /dev/zero
php     26821  htf  mem       REG                8,4   761912 6160770 /opt/php/php-5.6/lib/php/extensions/debug-zts-20131226/gd.so
php     26821  htf  mem       REG                8,4  2769230 2757968 /usr/local/lib/libcrypto.so.1.1
php     26821  htf  mem       REG                8,4   162632 6322346 /lib/x86_64-linux-gnu/ld-2.23.so
php     26821  htf  DEL       REG                0,5          7204959 /dev/zero
php     26821  htf    0u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    1u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    2u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    3r      CHR                1,9      0t0      11 /dev/urandom
php     26821  htf    4u     IPv4            7204948      0t0     TCP *:9501 (LISTEN)
php     26821  htf    5u     IPv4            7204949      0t0     UDP *:9502 
php     26821  htf    6u     IPv6            7204950      0t0     TCP *:9503 (LISTEN)
php     26821  htf    7u     IPv6            7204951      0t0     UDP *:9504 
php     26821  htf    8u     IPv4            7204952      0t0     TCP localhost:8000 (LISTEN)
php     26821  htf    9u     unix 0x0000000000000000      0t0 7204953 type=DGRAM
php     26821  htf   10u     unix 0x0000000000000000      0t0 7204954 type=DGRAM
php     26821  htf   11u     unix 0x0000000000000000      0t0 7204955 type=DGRAM
php     26821  htf   12u     unix 0x0000000000000000      0t0 7204956 type=DGRAM
php     26821  htf   13u  a_inode               0,11        0    9043 [eventfd]
php     26821  htf   14u     unix 0x0000000000000000      0t0 7204961 type=DGRAM
php     26821  htf   15u     unix 0x0000000000000000      0t0 7204962 type=DGRAM
php     26821  htf   16u     unix 0x0000000000000000      0t0 7204963 type=DGRAM
php     26821  htf   17u     unix 0x0000000000000000      0t0 7204964 type=DGRAM
php     26821  htf   18u  a_inode               0,11        0    9043 [eventpoll]
php     26821  htf   19u  a_inode               0,11        0    9043 [signalfd]
php     26821  htf   20u  a_inode               0,11        0    9043 [eventpoll]
php     26821  htf   22u     IPv4            7452776      0t0     TCP localhost:9501->localhost:59056 (ESTABLISHED)
```

* so-Dateien sind dynamische Linkerbibliotheken, die vom Prozess geladen wurden
* IPv4/IPv6 TCP (LISTEN) sind die Ports, auf denen der Server lauscht
* UDP sind die UDP-Ports, auf denen der Server lauscht
* unix type=DGRAM sind [unixSockets](/learn?id=Was ist IPC), die vom Prozess erstellt wurden
* IPv4 (ESTABLISHED) zeigt an, dass es sich um eine TCP-Client-Verbindung zum Server handelt, die den IP- und PORT des Clients sowie den Zustand (ESTABLISHED) enthält
* 9u / 10u beziehen sich auf die fd-Werte (Dateideskriptoren) des Dateihandles
* Weitere Informationen finden Sie im Handbuch zu lsof


## perf

Das `perf`-Tool ist ein sehr leistungsstarker dynamischer追踪werkzeug, das von der Linux-Kernel bereitgestellt wird. Die `perf top`-Befehl kann verwendet werden, um Echtzeit-Leistungsprobleme von laufenden Programmen zu analysieren. Im Gegensatz zu Werkzeugen wie `callgrind`, `xdebug`, `xhprof` etc., muss `perf` keine Codeänderungen vornehmen, um Profile-Ergebnisse zu exportieren.


### Verwendungsweise

```shell
perf top -p [Prozess-ID]
```

### Ausführungsergebnis

![perf top Ausführungsergebnis](../_images/other/perf.png)

Die Ergebnisse von perf zeigen deutlich die Zeit, die für die Ausführung verschiedener C-Funktionen bei der aktuellen Prozesslaufzeit vergeudet wird, sodass man erkennen kann, welche C-Funktionen den CPU-Ressourcen am meisten beanspruchen.

Wenn Sie mit dem Zend VM vertraut sind und einige Zend-Funktionen zu oft aufgerufen werden, kann dies darauf hindeuten, dass Ihr Programm bestimmte Funktionen in großer Zahl verwendet und somit einen hohen CPU-Anteil beansprucht, was eine gezielte Optimierung erfordert.
