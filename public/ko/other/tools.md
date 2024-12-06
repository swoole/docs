# 도구 사용


## yasd

[yasd](https://github.com/swoole/yasd)

단일 단계 디버그 도구로, `Swoole` 코루outine 환경에서 사용할 수 있으며, `IDE`와 명령행 디버그 모드를 지원합니다.


## tcpdump

네트워크 통신 프로그램을 디버그할 때 tcpdump는 필수 도구입니다. tcpdump는 매우 강력하여 네트워크 통신의 모든 세부 사항을 볼 수 있습니다. 예를 들어 TCP에서는 3차握手, PUSH/ACK 데이터 전송, close 4차 손짓 등 모든 세부 사항을 볼 수 있습니다. TCP 패킷의字节수, 시간 등을 포함합니다.


### 사용 방법

가장 간단한 사용 예시:

```shell
sudo tcpdump -i any tcp port 9501
```
* -i 매개변수는 네트워크 인터페이스를 지정하며, any는 모든 네트워크 인터페이스를 의미합니다.
* tcp는 오직 TCP 프로토콜만 감시합니다.
* port는 감시할 포트 번호를 지정합니다.

!> tcpdump는 root 권한이 필요합니다. 통신 데이터 내용을 볼 필요가 있다면 `-Xnlps0` 매개변수를 추가할 수 있습니다. 다른 더 많은 매개변수는 인터넷상의 글을 참고하세요.


### 실행 결과

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
* `13:29:11.563473` 시간은 마이크로 초까지 정확합니다.
*  localhost.42333 > localhost.9501은 통신의 흐름을 나타내며, 42333는 클라이언트, 9501은 서버입니다.
* [S]는 이것이 SYN 요청임을 나타냅니다.
* [.]는 이것이 ACK 확인 패킷임을 나타내며, (클라이언트)SYN->(서버)SYN->(클라이언트)ACK는 3차握手 과정입니다.
* [P]는 이것이 데이터 전송임을 나타내며, 서버에서 클라이언트로 또는 클라이언트에서 서버로 전송될 수 있습니다.
* [F]는 이것이 FIN 패킷임을 나타내며, 연결 종료 작업을 나타내며, 클라이언트/서버 모두 시작할 수 있습니다.
* [R]는 이것이 RST 패킷임을 나타내며, F 패킷과 같은 역할을 하지만, RST는 연결이 닫힐 때 여전히 처리되지 않은 데이터가 있다는 것을 나타냅니다. 즉, 연결을 강제로 끊어버리는 것을 의미합니다.
* win 342는 슬라이딩 창 크기를 나타냅니다.
* length 12는 데이터 패킷의 크기를 나타냅니다.


## strace

strace는 시스템 호출의 실행 상황을 추적할 수 있는 도구로, 프로그램에 문제가 발생한 후에는 strace를 사용하여 문제를 분석하고 추적할 수 있습니다.

!> FreeBSD/MacOS에서는 truss를 사용할 수 있습니다.


### 사용 방법

```shell
strace -o /tmp/strace.log -f -p $PID
```

* -f는 멀티스레드와 멀티프로세스를 추적하며, -f 매개변수를 적용하지 않으면 자식 프로세스와 자식 스레드의 실행 상황을 포착할 수 없습니다.
* -o는 결과를 파일로 출력합니다.
* -p $PID는 추적할 프로세스 ID를 지정하며, ps aux 명령어로 확인할 수 있습니다.
* -tt은 시스템 호출이 발생한 시간을 인쇄하며, 마이크로 초까지 정확합니다.
* -s는 문자열 출력의 길이를 제한합니다. 예를 들어 recvfrom 시스템 호출에서 받은 데이터는 기본적으로 32바이트만 출력됩니다.
* -c는 각 시스템 호출의 소모 시간을 실시간으로 통계합니다.
* -T는 각 시스템 호출의 소모 시간을 출력합니다.


## gdb

GDB는 GNU 오픈 소스 조직이 발표한 강력한 UNIX 하의 프로그램 디버그 도구로, C/C++ 개발된 프로그램을 디버그하는 데 사용할 수 있습니다. PHP와 Swoole는 C 언어로 개발되었기 때문에 PHP+Swoole 프로그램을 디버그하기 위해 GDB를 사용할 수 있습니다.

gdb 디버그는 명령행 인터렉티브 방식으로, 자주 사용하는 명령어를 숙지해야 합니다.


### 사용 방법

```shell
gdb -p 进程ID
gdb php
gdb php core
```

gdb는 3가지 사용 방식이 있습니다:

* 실행 중인 PHP 프로그램을 추적하기 위해 gdb -p 进程ID 사용합니다.
* gdb를 사용하여 PHP 프로그램을 실행하고 디버그하기 위해 gdb php -> run server.php을 사용합니다.
* PHP 프로그램이 coredump을 생성한 후 gdb를 사용하여 core 메모리 이미지를 로딩하여 디버그하기 위해 gdb php core 사용합니다.

!> PATH 환경 변수에 php가 없다면, gdb를 사용할 때 절대 경로를 지정해야 합니다. 예를 들어 gdb /usr/local/bin/php


### 자주 사용하는 명령어

* `p`: print, C 변수의 값을 출력합니다.
* `c`: continue, 중지된 프로그램을 계속 실행합니다.
* `b`: breakpoint, 브레이크 포인트를 설정합니다. 함수 이름에 따라 설정할 수 있습니다. 예를 들어 `b zif_php_function` 또는 소스 코드의 줄 번호에 따라 설정할 수 있습니다. 예를 들어 `b src/networker/Server.c:1000`
* `t`: thread, 스레드를 전환합니다. 프로세스가 여러 개의 스레드를 가지고 있다면 t 명령어로 다른 스레드로 전환할 수 있습니다.
* `ctrl + c`: 현재 실행 중인 프로그램을 중단합니다. c 명령어와 함께 사용합니다.
* `n`: next, 다음 줄을 실행합니다. 단일 단계 디버그입니다.
* `info threads`: 실행 중인 모든 스레드를 확인합니다.
* `l`: list, 소스 코드를 확인합니다. `l 函数名` 또는 `l 行号`으로 사용할 수 있습니다.
* `bt`: backtrace, 실행 시의 함수 호출 스택을 확인합니다.
* `finish`: 현재 함수를 완료합니다.
* `f`: frame, bt와 함께 사용하여 함수 호출 스택의 특정 레벨로 전환합니다.
* `r`: run, 프로그램을 실행합니다.


## zbacktrace

zbacktrace는 PHP 소스 패키지에서 제공하는 gdb 커스텀 명령어로, bt 명령어와 비슷한 기능을 합니다. 다만 zbacktrace가 보이는 호출 스택은 PHP 함수 호출 스택이며, C 함수가 아닙니다.

php-src를 다운로드하고 압축을 풀어 루트 디렉토리에서 `.gdbinit` 파일을 찾습니다. gdb shell에서 다음 명령어를 입력합니다.

```shell
source .gdbinit
zbacktrace
```
`.gdbinit`는 또한 다른 더 많은 명령어를 제공하며, 소스 코드를 확인하여 자세한 정보를 이해할 수 있습니다.

#### gdb+zbacktrace를 사용한 죽음의 고리 문제 추적

```shell
gdb -p 进程ID
```

* `ps aux` 도구를 사용하여 죽음의 고리를 겪는 Worker 프로세스 ID를 찾습니다.
* `gdb -p`를 사용하여 지정한 프로세스를 추적합니다.
* 반복해서 `ctrl + c` , `zbacktrace` , `c`를 호출하여 프로그램이 어느 PHP 코드에서 고리를 겪는지 확인합니다.
* 해당 PHP 코드를 찾아 해결합니다.
## lsof

Linux 플랫폼에서 `lsof` 도구를 사용하면 특정 프로세스가 열어둔 파일 핸들을 확인할 수 있습니다. 이는 swoole의 작업 프로세스가 열어둔 모든 소켓, 파일, 자원을 추적하는 데 사용할 수 있습니다.


### 사용 방법

```shell
lsof -p [프로세스 ID]
```


### 실행 결과

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

* so 파일은 프로세스가 로딩한 동적 연결 라이브러리입니다.
* IPv4/IPv6 TCP (LISTEN)는 서버가 수신하는 포트입니다.
* UDP는 서버가 수신하는 UDP 포트입니다.
* unix type=DGRAM일 때는 프로세스가 만든 [unixSocket](/learn?id=IPC)입니다.
* IPv4 (ESTABLISHED)는 서버에 연결된 TCP 클라이언트를 나타내며, 클라이언트의 IP와 PORT, 그리고 상태(ESTABLISHED)를 포함합니다.
* 9u / 10u는 해당 파일 핸들의 fd 값(파일 설명자)을 나타냅니다.
* 더 많은 정보는 lsof 매뉴얼을 참고하세요.


## perf

`perf` 도구는 Linux 커널에서 제공하는 매우 강력한 동적 추적 도구로, `perf top` 명령어는 실시간으로 실행 중인 프로그램의 성능 문제를 분석하는 데 사용할 수 있습니다. `callgrind`, `xdebug`, `xhprof` 등의 도구와 달리, `perf`는 코드를 수정하지 않고도 프로필 결과 파일을 내보낼 수 있습니다.


### 사용 방법

```shell
perf top -p [프로세스 ID]
```

### 출력 결과

![perf top 출력 결과](../_images/other/perf.png)

`perf` 결과는 현재 프로세스가 실행 중인 각각의 C 함수의 실행 시간을 선명하게 보여주어, 어떤 C 함수가 CPU 자원을 많이 차지하는지 이해할 수 있습니다.

Zend VM에 익숙하다면, 일부 Zend 함수가 너무 많이 호출되는 것은 프로그램에서 일부 함수를 대량으로 사용하여 CPU 자원이 지나치게 높다는 것을 의미할 수 있으며, 이를 대상으로 성능 최적화를 할 수 있습니다.
