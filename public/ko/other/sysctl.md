# 커널 매개변수 조정


## ulimit 설정

`ulimit -n`을 100000 이상으로 조정해야 합니다. 명령창에서 `ulimit -n 100000`을 실행하면 변경됩니다. 만약 변경할 수 없다면, `/etc/security/limits.conf` 파일을 설정해야 합니다. 다음과 같은 내용을 추가합니다.

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

`limits.conf` 파일을 변경한 후에는 시스템을 재시작하여 변경 사항이 적용됩니다.


## 커널 설정

`Linux` 운영체에서 커널 매개변수를 변경하는 방법은 3가지가 있습니다:



- `/etc/sysctl.conf` 파일을 수정하여 `key = value` 형식의 구성 옵션을 추가하고, 변경 내용을 저장한 후 `sysctl -p` 명령을 사용하여 새로운 구성을 적용합니다.

- `sysctl` 명령을 사용하여 임시로 변경합니다. 예를 들어: `sysctl -w net.ipv4.tcp_mem="379008 505344 758016"`

- 직접 `/proc/sys/` 디렉토리에서 파일을 수정합니다. 예를 들어: `echo "379008 505344 758016" > /proc/sys/net/ipv4/tcp_mem`

> 첫 번째 방법은 운영체가 재시작될 때 자동으로 적용되며, 두 번째와 세 번째 방법은 재시작 후에 적용되지 않습니다.


### net.unix.max_dgram_qlen = 100

Swoole는 unix socket dgram을 사용하여 프로세스 간 통신을 수행합니다. 요청량이 많다면 이 매개변수를 조정해야 합니다. 시스템의 기본값은 10이며, 100 또는 더 큰 값으로 설정할 수 있습니다. 또는 워커 프로세스의 수를 늘려 단일 워커 프로세스가 할당하는 요청량을 줄입니다.


### net.core.wmem_max

이 매개변수를 변경하면 소켓 캐시 영역의 메모리 크기를 늘립니다.

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

소켓 재사용 여부를 설정합니다. 이 함수의 역할은 서버가 재시작할 때 监听하는 포트를 빠르게 재사용할 수 있도록 합니다. 이 매개변수를 설정하지 않으면 서버가 재시작할 때 포트가 즉시 해제되지 않아 실패합니다.


### net.ipv4.tcp_tw_recycle

TCP 연결 중 TIME-WAIT 소켓의 빠른 재사용을 사용합니다. 짧은 연결을 하는 서버는 이 매개변수를 활성화해야 합니다. 이 매개변수는 TCP 연결 중 TIME-WAIT 소켓의 빠른 재사용을 활성화하는 것을 나타내며, Linux 시스템의 기본값은 0이며 비활성화되어 있습니다. 이 매개변수를 활성화하면 NAT 사용자의 연결이 불안정해질 수 있으니 신중하게 테스트한 후에 활성화하세요.


## 메시지 대기열 설정

메시지 대기열을 프로세스 간 통신 방식으로 사용하는 경우, 다음 커널 매개변수를 조정해야 합니다.



- kernel.msgmnb = 4203520, 메시지 대기열의 최대 바이트 수

- kernel.msgmni = 64, 최대 메시지 대기열 개수

- kernel.msgmax = 8192, 메시지 대기열 단일 데이터의 최대 길이


## FreeBSD/MacOS



- sysctl -w net.local.dgram.maxdgram=8192
- sysctl -w net.local.dgram.recvspace=200000
  Unix Socket의 버퍼 영역 크기를 변경합니다.


## CoreDump 사용开启

커널 매개변수를 설정합니다.

```
kernel.core_pattern = /data/core_files/core-%e-%p-%t
```

`ulimit -c` 명령을 사용하여 현재 coredump 파일의 제한을 확인합니다.

```shell
ulimit -c
```

만약 0이면 `/etc/security/limits.conf`를 수정하여 제한을 설정해야 합니다.

> CoreDump가 활성화되면 프로그램이 예외가 발생하면 프로세스를 파일로 내보냅니다. 이것은 프로그램 문제를 조사하는 데 매우 도움이 됩니다.


## 기타 중요한 구성



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

## 구성 변경 확인

예를 들어 `net.unix.max_dgram_qlen = 100`을 변경한 후에는 다음 명령을 사용하여 확인합니다.

```shell
cat /proc/sys/net/unix/max_dgram_qlen
```

변경이 성공했다면 여기에 새로 설정된 값이 표시됩니다.
