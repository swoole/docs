# 工具使用


## yasd

[yasd](https://github.com/swoole/yasd)

單步調試工具，可用於`Swoole`協程環境，支持`IDE`以及命令行的調試模式。


## tcpdump

在調試網絡通訊程序時tcpdump是必備工具。tcpdump很強大，可以看到網絡通訊的每個細節。如TCP，可以看到3次握手，PUSH/ACK數據推送，close4次揮手，全部細節。包括每一次網絡收包的getBytes數，時間等。


### 使用方法

最簡單的一個使用示例：

```shell
sudo tcpdump -i any tcp port 9501
```
* -i 參數指定了網卡，any表示所有網卡
* tcp 指定僅監聽TCP協議
* port 指定監聽的端口

!> tcpdump需要root權限；需要要看通訊的數據內容，可以加`-Xnlps0`參數，其他更多參數請參見網上的文章


### 運行結果

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
* `13:29:11.563473` 時間帶有精確到微秒
*  localhost.42333 > localhost.9501 表示通訊的流向，42333是客戶端，9501是服務器端
* [S] 表示這是一個SYN請求
* [.] 表示這是一個ACK確認包，(client)SYN->(server)SYN->(client)ACK 就是3次握手過程
* [P] 表示這個是一個數據推送，可以是从服務器端向客戶端推送，也可以从客戶端向服務器端推
* [F] 表示這是一個FIN包，是關閉連接操作，client/server都有可能發起
* [R] 表示這是一個RST包，與F包作用相同，但RST表示連接關閉時，仍然有數據未被處理。可以理解為是強制切斷連接
* win 342是指滑動窗口大小
* length 12指數據包的大小


## strace

strace可以追蹤系統調用的執行情況，在程序發生問題後，可以用strace分析和追蹤問題。

!> FreeBSD/MacOS下可以使用truss


### 使用方法

```shell
strace -o /tmp/strace.log -f -p $PID
```

* -f 表示追蹤多線程和多進程，如果不加-f參數，無法抓取到子進程和子線程的運行情況
* -o 表示將結果輸出到一個文件中
* -p $PID，指定追蹤的進程ID，通過ps aux可以看到
* -tt 打印系統調用發生的時間，精確到微秒
* -s 限定字符串打印的長度，如recvfrom系統調用收到的數據，默認只打印32字节
* -c 實時統計每個系統調用的耗時
* -T 打印每個系統調用的耗時


## gdb

GDB是GNU開源組織發布的一個強大的UNIX下的程序調試工具，可以用來調試C/C++開發的程序，PHP和Swoole是使用C語言開發的，所以可以用GDB來調試PHP+Swoole的程序。

gdb調試是命令行互動式的，需要掌握常用的指令。


### 使用方法

```shell
gdb -p 進程ID
gdb php
gdb php core
```

gdb有3種使用方式：

* 跟踪正在運行的PHP程序，使用gdb -p 進程ID
* 使用gdb運行並調試PHP程序，使用gdb php -> run server.php 進行調試
* PHP程序發生coredump後使用gdb加載core內存镜像進行調試 gdb php core

!> 如果PATH環境變量中沒有php，gdb時需要指定絕對路徑，如gdb /usr/local/bin/php


### 常 用指令

* `p`：print，打印C變量的值
* `c`：continue，繼續運行被中止的程序
* `b`：breakpoint，設置斷點，可以按照函數名設置，如`b zif_php_function`，也可以按照源代碼的行數指定斷點，如`b src/networker/Server.c:1000`
* `t`：thread，切換線程，如果進程擁有多個線程，可以使用t指令，切換到不同的線程
* `ctrl + c`：中斷當前正在運行的程序，和c指令配合使用
* `n`：next，執行下一行，單步調試
* `info threads`：查看運行的所有線程
* `l`：list，查看源码，可以使用`l 函數名` 或者 `l 行號`
* `bt`：backtrace，查看運行時的函數調用堆棧
* `finish`：完成當前函數
* `f`：frame，與bt配合使用，可以切換到函數調用堆棧的某一层
* `r`：run，運行程序


### zbacktrace

zbacktrace是PHP源码包提供的一個gdb自定義指令，功能與bt指令類似，與bt不同的是zbacktrace看到的調用堆棧是PHP函數調用堆棧，而不是C函數。

下載php-src，解壓後從根目錄中找到一个`.gdbinit`文件，在gdb shell中輸入

```shell
source .gdbinit
zbacktrace
```
`.gdbinit`還提供了其他更多指令，可以查看源码了解詳細的信息。

#### 使用gdb+zbacktrace追蹤死循環問題

```shell
gdb -p 進程ID
```

* 使用`ps aux`工具找出發生死循環的Worker進程ID
* `gdb -p`跟踪指定的進程
* 反复調用 `ctrl + c` 、`zbacktrace`、`c` 查看程序在哪段PHP代碼發生循環
* 找到對應的PHP代碼進行解決
## lsof

Linux平台提供了`lsof`工具可以查看某个进程打开的文件句柄。可以用于跟踪swoole的工作进程所有打开的socket、file、资源。

### 使用方法

```shell
lsof -p [进程ID]
```

### 运行结果

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

* so文件是进程加载的动态连接库
* IPv4/IPv6 TCP (LISTEN) 是服务器监听的端口
* UDP 是服务器监听的UDP端口
* unix type=DGRAM 时是进程创建的[unixSocket](/learn?id=什么是IPC)
* IPv4 (ESTABLISHED) 表示连接到服务器的TCP客户端，包含了客户端的IP和PORT，以及状态(ESTABLISHED)
* 9u / 10u 表示该文件句柄的fd值(文件描述符)
* 其他更多信息可以参考lsof的手册

## perf

`perf`工具是Linux内核提供一个非常强大的动态跟踪工具，`perf top`指令可用于实时分析正在执行程序的性能问题。与`callgrind`、`xdebug`、`xhprof`等工具不同，`perf`无需修改代码导出profile结果文件。

### 使用方法

```shell
perf top -p [进程ID]
```

### 输出结果

![perf top输出结果](../_images/other/perf.png)

perf结果中清楚地展示了当前进程运行时各个C函数的执行耗时，可以了解哪个C函数占用CPU资源较多。

如果你熟悉Zend VM，某些Zend函数调用过多，可以说明你的程序中大量使用了某些函数，导致CPU占用过高，针对性的进行优化。
