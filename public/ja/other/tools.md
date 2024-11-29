# ツールの使用

## yasd

[yasd](https://github.com/swoole/yasd)

単歩デバッグツールで、Swooleコーン環境に使用でき、IDEやコマンドラインでのデバッグモードをサポートしています。

## tcpdump

ネットワーク通信プログラムをデバッグするためにはtcpdumpが必須のツールです。tcpdumpは非常に強力で、ネットワーク通信の各詳細を見ることができます。例えばTCPでは、3回の手順握手、PUSH/ACKデータのプッシュ、CLOSEの4回の手順をすべて見ることができます。これには、各ネットワークパケットの字节数や時間も含まれます。

### 使用方法

最も簡単な使用例は以下の通りです：

```shell
sudo tcpdump -i any tcp port 9501
```
* `-i` 引数はネットワークカードを指定し、anyはすべてのネットワークカードを意味します
* tcpはTCPプロトコルのみを監視することを指定します
* portは監視するポートを指定します

!> tcpdumpはroot権限が必要です。通信データの内容を見る必要がある場合は、「-Xnlps0」引数を加えることができます。その他のより多くの引数は、インターネット上の記事を参照してください。

### 実行結果

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
* `13:29:11.563473` 時間は微妙まで正確です
*  localhost.42333 > localhost.9501 は通信の流れを示しており、42333はクライアント、9501はサーバー端です
* [S] はSYNリクエストを意味します
* [.] はACK確認パケットを意味し、(client)SYN->(server)SYN->(client)ACKは3回の手順握手プロセスです
* [P] はデータプッシュを意味し、サーバー端からクライアントへ、またはクライアントからサーバー端へプッシュされることができます
* [F] はFINパケットを意味し、接続の閉鎖操作であり、client/serverのどちらかが開始する可能性があります
* [R] はRSTパケットを意味し、Fパケットと同じ機能を持ちますが、RSTは接続が閉鎖された時にまだ処理されていないデータがあることを示します。これは強制的に接続を切断することを意味します
* win 342はスライドウィンドウのサイズを指します
* length 12はデータパケットのサイズを指します

## strace

straceはシステム呼び出しの実行状況を追跡することができ、プログラムが問題が発生した後、straceを使用して問題を分析し追跡することができます。

!> FreeBSD/MacOSではtrussを使用することができます。

### 使用方法

```shell
strace -o /tmp/strace.log -f -p $PID
```

* `-f` はマルチスレッドとマルチプロセスを追跡することを意味し、-fパラメータを省略するとサブプロセスやサブスレッドの運用状況をキャプチャすることはできません
* `-o` は結果をファイルに出力することを意味します
* `-p $PID` とは、追跡するプロセスのIDを指定し、ps auxを使用して確認することができます
* `-tt` はシステム呼び出しが発生した時間を印刷し、微妙まで正確です
* `-s` は文字列の印刷長さを制限し、例えばrecvfromシステム呼び出しで受信したデータはデフォルトで32バイトしか印刷されません
* `-c` はリアルタイムで各システム呼び出しの消耗時間を統計します
* `-T` は各システム呼び出しの消耗時間を印刷します

## gdb

GDBはGNUオープンソース組織が公開している強力なUNIXプロセスデバッグツールであり、C/C++で開発されたプログラムをデバッグするために使用できます。PHPとSwooleはC言語で開発されているため、PHP+SwooleのプログラムをデバッグするためにGDBを使用することができます。

gdbデバッグはコマンドライン対話式であり、一般的な指令をマスターする必要があります。

### 使用方法

```shell
gdb -p プロセスID
gdb php
gdb php core
```

gdbには3つの使用方法があります：

* 実行中のPHPプログラムを追跡するためにgdb -p プロセスIDを使用する
* gdbを使用してPHPプログラムを実行しデバッグする、gdb php -> run server.phpでデバッグする
* PHPプログラムがcoredumpが発生した後にgdbでcoreメモリイメージを読み込んでデバッグする、gdb php core

!> PATH環境変数にphpが含まれていない場合は、gdbでは絶対パスを指定する必要があります。例えばgdb /usr/local/bin/php

### 一般的な指令

* `p` : print、C変数の値を印刷します
* `c` : continue、中止されたプログラムを再開します
* `b` : breakpoint、ブレークポイントを設定します。関数名に基づいて設定することもできます。例えば`b zif_php_function`。また、ソースコードの行数に基づいてブレークポイントを設定することもできます。例えば`b src/networker/Server.c:1000`
* `t` : thread、スレッドに切り替えます。プロセスが複数のスレッドを持っている場合は、t指令を使用して異なるスレッドに切り替えることができます
* `ctrl + c` : 現在の実行中のプログラムを中断します。c指令と組み合わせて使用します
* `n` : next、次の行を実行し、単歩デバッグを行います
* `info threads` : 実行中のすべてのスレッドを表示します
* `l` : list、ソースコードを表示します。例えば`l 関数名`または`l 行号`と指定することができます
* `bt` : backtrace、実行中の関数呼び出しスタックを表示します
* `finish` : 現在の関数を完了します
* `f` : frame、btと組み合わせて使用し、関数呼び出しスタックの特定のレベルに切り替えます
* `r` : run、プログラムを実行します

## zbacktrace

zbacktraceはPHPソースパッケージが提供するgdbカスタム指令で、bt指令と同等の機能を持っていますが、btとは異なりzbacktraceが見える呼び出しスタックはPHP関数呼び出しスタックであり、C関数ではありません。

php-srcをダウンロードし、展開した後、rootディレクトリから`.gdbinit`ファイルを見つけます。gdb shellで以下を入力します

```shell
source .gdbinit
zbacktrace
```
`.gdbinit`には他にもっと多くの指令があり、ソースコードを参照して詳細な情報を確認することができます。

#### gdb+zbacktraceを使用して死循環問題を追跡する

```shell
gdb -p プロセスID
```

* ps auxツールを使用して死循環が発生しているWorkerプロセスのIDを見つけます
* `gdb -p`で指定されたプロセスを追跡します
* 反復して `ctrl + c` 、`zbacktrace`、`c` を呼び出して、プログラムがどのPHPコードで循環しているかを確認します
* 対応するPHPコードを特定して問題を解決します

## lsof

Linuxプラットフォームでは`lsof`ツールを使用して、あるプロセスが開いているファイルハンドラを確認することができます。これはswooleのワークプロセスが開いているすべてのソケット、ファイル、リソースを追跡するために使用できます。

### 使用方法

```shell
lsof -p [プロセスID]
```

### 実行結果

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

* soファイルはプロセスが読み込んだダイナミックリンクライブラリです
* IPv4/IPv6 TCP (LISTEN) はサーバーがlistenしているポートです
* UDPはサーバーがlistenしているUDPポートです
* unix type=DGRAMの時、プロセスが作成した[unixSocket](/learn?id=什么是IPC)です
* IPv4 (ESTABLISHED) はサーバーに接続されたTCPクライアントを表し、クライアントのIPとPORT、状態(ESTABLISHED)を含みます
* 9u / 10uは、そのファイルハンドラのfd値(ファイル記述子)を指します
* その他のより多くの情報はlsofのマニュアルを参照してください

## perf

`perf`ツールは
