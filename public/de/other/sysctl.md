# Kernparameteranpassungen


## ULIMIT Einstellungen

`ulimit -n` sollte auf 100000 oder höher angepasst werden. Führen Sie den Befehl `ulimit -n 100000` aus der Kommandozeile aus, um die Änderung vorzunehmen. Wenn dies nicht möglich ist, müssen Sie das `/etc/security/limits.conf`-Datei bearbeiten und Folgendes hinzufügen:

```
* soft nofile 262140
* hard nofile 262140
root soft nofile 262140
root hard nofile 262140
* soft core unlimited
* hard core unlimited
root soft core unlimited
root hard core unlimited
```

Beachten Sie, dass das Bearbeiten der `limits.conf`-Datei eine Systemwiederaufnahme erfordert, um die Änderungen zu aktivieren.


## Kernparameter

Es gibt drei Möglichkeiten, Kernparameter in einem Linux-Betriebssystem zu ändern:



- Bearbeiten Sie das `/etc/sysctl.conf`-Datei und fügen Sie die Konfigurationsoptionen hinzu, die in der Form `key = value` vorliegen. Führen Sie nach der Änderung `sysctl -p` aus, um die neue Konfiguration zu laden.

- Verwenden Sie den `sysctl`-Befehl, um die Parameter vorübergehend zu ändern, zum Beispiel: `sysctl -w net.ipv4.tcp_mem="379008 505344 758016"`

- Bearbeiten Sie direkt die Dateien in dem `/proc/sys/`-Verzeichnis, zum Beispiel: `echo "379008 505344 758016" > /proc/sys/net/ipv4/tcp_mem`

> Die erste Methode wird automatisch nach einem Systemstart wirksam, während die zweite und dritte Methode nach einem Neustart ungültig sind.


### net.unix.max_dgram_qlen = 100

Swoole verwendet Unix-Sockets für Datagramm-Kommunikation zwischen Prozessen. Wenn die Anzahl der Anforderungen groß ist, sollte dieser Parameter angepasst werden. Der Standardwert beträgt 10 und kann auf 100 oder höher erhöht werden. Alternativ kann die Anzahl der Worker-Prozesse erhöht und die Anzahl der Anforderungen pro Worker-Prozess reduziert werden.


### net.core.wmem_max

Ändern Sie diesen Parameter, um die Größe des Socket-Cachebereichs zu erhöhen.

```
net.ipv4.tcp_mem  =   379008       505344  758016
net.ipv4.tcp_wmem = 4096        16384   4194304
net.ipv4.tcp_rmem = 4096          87380   4194304
net.core.wmem_default = 8388608
net.core.rmem_default = 8388608
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
```


### net.ipv4.tcp_tw_reuse

Die Socket-Wiederverwendung, dieser Funktion dient dazu, dass ein Server beim Neustart schnell die监听的Ports wiederverwenden kann. Wenn dieser Parameter nicht festgelegt ist, kann es zu einem Neustartfehler kommen, wenn die Ports nicht rechtzeitig freigesetzt werden.


### net.ipv4.tcp_tw_recycle

Verwenden Sie schnelle Socket-Recycling, für Server mit kurzen Verbindungen sollte dieser Parameter aktiviert werden. Dieser Parameter gibt an, ob TIME-WAIT-Sockets schnell recycelt werden sollen. Der Standardwert in Linux-Systemen beträgt 0, was bedeutet, dass sie deaktiviert sind. Die Aktivierung dieses Parameters kann zu instabilen Verbindungen für NAT-Nutzer führen, bitte testen Sie vorsichtig, bevor Sie ihn aktivieren.


## Message Queue Einstellungen

Wenn Sie eine Message Queue als Mittel der Prozess-zu-Prozess-Kommunikation verwenden, müssen Sie diesen Kernparameter anpassen:



- kernel.msgmnb = 4203520, die maximale Anzahl von Bytes für die Message Queue

- kernel.msgmni = 64, die maximale Anzahl von Message Queues, die zulässig sind
- kernel.msgmax = 8192, die maximale Länge einer einzelnen Nachricht in der Message Queue


## FreeBSD/MacOS



- sysctl -w net.local.dgram.maxdgram=8192
- sysctl -w net.local.dgram.recvspace=200000
  Ändern Sie die Größe des Buffers für Unix-Sockets


## CoreDump aktivieren

Stellen Sie den Kernparameter ein:

```
kernel.core_pattern = /data/core_files/core-%e-%p-%t
```

Verwenden Sie den `ulimit -c`-Befehl, um die aktuelle Beschränkung für coredump-Dateien zu überprüfen

```shell
ulimit -c
```

Wenn der Wert 0 ist, müssen Sie das `/etc/security/limits.conf`-Datei bearbeiten und eine limit festlegen.

> Nachdem core-dump aktiviert wurde, wird der Prozess, wenn er ein Ausnahme erleidet, in eine Datei exportiert. Dies ist sehr hilfreich für die Untersuchung von Programmproblemen.


## Weitere wichtige Konfigurationen



- net.ipv4.tcp_syncookies=1

- net.ipv4.tcp_max_syn_backlog=81920

- net.ipv4.tcp_synack_retries=3

- net.ipv4.tcp_syn_retries=3

- net.ipv4.tcp_fin_timeout = 30

- net.ipv4.tcp_keepalive_time = 300

- net.ipv4.tcp_tw_reuse = 1

- net.ipv4.tcp_tw_recycle = 1

- net.ipv4.ip_local_port_range = 20000 65000

- net.ipv4.tcp_max_tw_buckets = 200000
- net.ipv4.route.max_size = 5242880

## Überprüfen, ob die Konfiguration wirksam ist

Zum Beispiel, nachdem Sie `net.unix.max_dgram_qlen = 100` geändert haben, können Sie den neuen Wert durch folgenden Befehl überprüfen:

```shell
cat /proc/sys/net/unix/max_dgram_qlen
```

Wenn die Änderung erfolgreich ist, ist dies der neue festgelegte Wert.
