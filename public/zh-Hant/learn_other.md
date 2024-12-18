# 其他知識

## 設定DNS解析超時和重試

在網絡編程中，經常使用`gethostbyname`和`getaddrinfo`來實現域名解析，這兩個`C`函數並未提供超時參數。事實上，可以修改`/etc/resolv.conf`來設定超時和重試邏輯。

!> 可參考`man resolv.conf`文檔

### 多個 NameServer <!-- {docsify-ignore} -->

```
nameserver 192.168.1.3
nameserver 192.168.1.5
option rotate
```

可配置多個`nameserver`，底層會自動輪询，在第一個`nameserver`查詢失敗時會自動切換為第二個`nameserver`進行重試。

`option rotate`配置的作用是，進行`nameserver`負載均衡，使用輪询模式。

### 超時控制 <!-- {docsify-ignore} -->

```
option timeout:1 attempts:2
```

* `timeout`：控制`UDP`接收的超時時間，單位為秒，默認為`5`秒
* `attempts`：控制嘗試的次數，配置為`2`時表示，最多嘗試`2`次，默認為`5`次

假設有`2`個`nameserver`，`attempts`為`2`，超時為`1`，那麼如果所有`DNS`服務器無響應的情況下，最長等待時間為`4`秒（`2x2x1`）。

### 調用跟踪 <!-- {docsify-ignore} -->

可使用[strace](/other/tools?id=strace)跟蹤確認。

將`nameserver`設置為兩個不存在的`IP`，`PHP`代碼使用`var_dump(gethostbyname('www.baidu.com'));`解析域名。

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

可以看到這裡一共重試了`4`次，`poll`調用超時設置為`1000ms`（`1秒`）。
