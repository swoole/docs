# 기타 지식


## DNS 해석 시각화와 재시도 설정

네트워크 프로그래밍에서 종종 `gethostbyname`와 `getaddrinfo`를 사용하여 도메인 이름을 해석합니다. 이 두 가지 `C` 함수는 시각화 매개변수를 제공하지 않습니다. 실제로 `/etc/resolv.conf`를 수정하여 시각화와 재시도 논리를 설정할 수 있습니다.

!> `man resolv.conf` 문서를 참고하세요.


### 여러 NameServer <!-- {docsify-ignore} -->

```
nameserver 192.168.1.3
nameserver 192.168.1.5
option rotate
```

여러 개의 `nameserver`를 구성할 수 있으며, 기본적으로 하단층은 자동으로 로테이션합니다. 첫 번째 `nameserver`가 조회 실패하면 자동으로 두 번째 `nameserver`로 전환하여 재시도를 시도합니다.

`option rotate` 구성의 역할은 `nameserver` 부하 분산을 수행하고, 로테이션 모드를 사용하는 것입니다.


### 시각화 제어 <!-- {docsify-ignore} -->

```
option timeout:1 attempts:2
```

* `timeout` : `UDP` 수신의 시각화 시간을 제어하는 것으로, 초 단위로 기본적으로 `5`초입니다.
* `attempts` : 시도 횟수를 제어하는 것으로, `2`로 설정하면 최대 `2`회까지 시도하며, 기본적으로 `5`회입니다.

두 개의 `nameserver`가 있고, `attempts`가 `2`, 시각화가 `1`인 경우, 모든 DNS 서버에 응답이 없는 상황에서 최대 대기 시간은 `4`초(`2x2x1`)입니다.

### 호출 추적 <!-- {docsify-ignore} -->

[strace](/other/tools?id=strace)를 사용하여 확인할 수 있습니다.

`nameserver`를 존재하지 않는 두 개의 IP로 설정하고, PHP 코드는 `var_dump(gethostbyname('www.baidu.com'));`을 사용하여 도메인을 해석합니다.

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

여기서 총 `4`번의 재시도가 이루어졌으며, `poll` 호출의 시각화는 `1000ms` ( `1초` )로 설정되었습니다.
