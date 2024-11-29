# その他の知識
## DNS解析のタイムアウトとリピート設定

ネットワークプログラミングでは、よく`gethostbyname`や`getaddrinfo`を使ってドメイン名を解析しますが、これらのC言語の関数にはタイムアウトパラメータはありません。実際には、`/etc/resolv.conf`を変更してタイムアウトとリピートのロジックを設定することができます。

> `man resolv.conf`のドキュメントを参照してください
### 多個のNameServer <!-- {docsify-ignore} -->

```
nameserver 192.168.1.3
nameserver 192.168.1.5
option rotate
```

複数の`nameserver`を設定することができ、下層は自動的にローリングを行い、最初の`nameserver`の查询に失敗した場合に自動的に次の`nameserver`に切り替えてリピートします。

`option rotate`の設定は、`nameserver`の負荷分散を行い、ローリングモードを使用することを意味します。
### タイムアウト制御 <!-- {docsify-ignore} -->

```
option timeout:1 attempts:2
```

* `timeout`：`UDP`受信のタイムアウト時間を制御し、単位は秒で、デフォルトは`5`秒です
* `attempts`：試みる回数を制御し、`2`に設定すると最大`2`回試みます、デフォルトは`5`回です

2つの`nameserver`があり、`attempts`が`2`、タイムアウトが`1`の場合、すべてのDNSサーバーが応答しない状況では、最長待ち時間は`4`秒（`2x2x1`）になります。
### 問い合わせの追跡 <!-- {docsify-ignore} -->

[strace](/other/tools?id=strace)を使用して確認することができます。

`nameserver`を2つの存在しない`IP`に設定し、PHPコードでは`var_dump(gethostbyname('www.baidu.com'));`でドメイン名を解析します。

```
socket(AF_INET, SOCK_DGRAM|SOCK_CLOEXEC|SOCK_NONBLOCK, IPPROTO_IP) = 3
connect(3, {sa_family=AF_INET, sin_port=htons(53), sin_addr=inet_addr("10.20.128.16")}, 16) = 0
poll([{fd=3, events=POLLOUT}], 1, 0)    = 1 ([{fd=3, revents=POLLOUT}])
sendto(3, "\346\5\1\0\0\1\0\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
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

ここでは合計で`4`回のリピートがありました。`poll`の呼び出しのタイムアウトは`1000ms`（`1秒`）に設定されています。
