# Weitere Kenntnisse

## Einstellung von DNS-Abfrage-Timeout und -Wiederholversuchen

Im Netzwerkprogrammierung wird häufig die `gethostbyname` und `getaddrinfo` Funktion zur Domain-Resolution verwendet, aber diese beiden `C` Funktionen bieten keine Timeout-Parameter an. Tatsächlich kann die `/etc/resolv.conf` geändert werden, um Timeout- und Wiederholungslogik einzustellen.

!> Referenzieren Sie das `man resolv.conf` Dokument


### Mehrere NameServer <!-- {docsify-ignore} -->

```
nameserver 192.168.1.3
nameserver 192.168.1.5
option rotate
```

Es können mehrere `nameserver` konfiguriert werden, die unteren Ebenen werden automatisch umgehen, und wenn die erste `nameserver` eine Abfrage nicht beantwortet, wird automatisch auf den zweiten `nameserver` gewechselt, um zu versuchen, es erneut zu senden.

Die Konfiguration der `option rotate` dient dazu, eine Lastverteilung zwischen den `nameservern` durchzuführen und verwendet einen Round-Robin-Modus.


### Timeout-Steuerung <!-- {docsify-ignore} -->

```
option timeout:1 attempts:2
```

* `timeout`: steuert die Timeoutzeit für das Empfangen von `UDP`, in Sekunden,默认 ist `5` Sekunden
* `attempts`: steuert die Anzahl der Versuche, bei einer Konfiguration von `2` bedeutet dies, dass bis zu `2` Mal versucht wird,默认 ist `5` Mal

Angenommen, es gibt `2` `nameserver` und `attempts` sind `2`, das Timeout beträgt `1` Sekunde, dann ist die längste Wartezeit, wenn alle DNS-Server keine Antwort geben, `4` Sekunden (`2x2x1`).

### Anruflogik跟踪 <!-- {docsify-ignore} -->

Möglich ist die Verwendung von [strace](/other/tools?id=strace) zur Bestätigung.

Stellen Sie die `nameserver` auf zwei nicht existierende `IP`s, und verwenden Sie PHP-Code, um mit `var_dump(gethostbyname('www.baidu.com'));` die Domain zu lösen.

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

Es ist zu sehen, dass insgesamt `4` Mal versucht wurde, `poll` wurde mit einem Timeout von `1000ms` (1 Sekunde) festgelegt.
