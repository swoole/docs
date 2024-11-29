## カーネルパラメータの調整


## ulimit設定

`ulimit -n`を100000やそれ以上に調整する必要があります。コマンドラインで`ulimit -n 100000`を実行すると変更できます。変更できない場合は、`/etc/security/limits.conf`を設定し、以下の内容を追加してください。

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

`limits.conf`を変更した後、システムを再起動して変更を適用する必要があります。


## カーネル設定

Linuxオペレーティングシステムでカーネルパラメータを変更するには3つの方法があります：



- `/etc/sysctl.conf`ファイルに設定オプションを追加し、フォーマットは`key = value`です。変更を保存した後、`sysctl -p`で新しい設定を読み込みます。

- `sysctl`コマンドを使用して一時的に変更します。例えば：`sysctl -w net.ipv4.tcp_mem="379008 505344 758016"`

- `/proc/sys/`ディレクトリのファイルを直接編集します。例えば：`echo "379008 505344 758016" > /proc/sys/net/ipv4/tcp_mem`

> 第一の方法はオペレーティングシステムが再起動した後に自動的に適用されますが、第二と第三の方法では再起動後に無効になります。


### net.unix.max_dgram_qlen = 100

Swooleはunix socket dgramを使用してプロセス間の通信を行いますが、リクエスト量が多い場合はこのパラメータを調整する必要があります。システムのデフォルトは10で、100やそれ以上に設定することができます。または、workerプロセスの数を増やして、各workerプロセスに割り当てられるリクエスト量を減らします。


### net.core.wmem_max

このパラメータを調整してsocketバッファーのメモリサイズを増やします。

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

socket reuseの有無を指定します。この関数の役割は、Serverが再起動した時に迅速に监听ポートを再使用できることです。このパラメータを指定しなければ、Serverが再起動した時にポートがタイムリーに解放されずに起動に失敗することがあります。


### net.ipv4.tcp_tw_recycle

socketの快速回収を使用します。短絡的なConnectionのServerはこのパラメータを有効にする必要があります。このパラメータは、TIME-WAIT状態のsocketsの快速回収を有効にすることを意味し、Linuxシステムではデフォルトで0で、無効になっています。このパラメータを有効にするとNATユーザーの接続が不安定になる可能性がありますので、慎重にテストした後に有効にしてください。


## メッセージキュー設定

メッセージキューをプロセス間の通信手段として使用する場合、以下のカーネルパラメータを調整する必要があります。



- kernel.msgmnb = 4203520、メッセージキューの最大字节数

- kernel.msgmni = 64、最大で何個のメッセージキューを作成できるか

- kernel.msgmax = 8192、メッセージキューの単一データの最大長さ


## FreeBSD/MacOS



- sysctl -w net.local.dgram.maxdgram=8192
- sysctl -w net.local.dgram.recvspace=200000
  Unix Socketのbuffer区サイズを変更する


## CoreDumpの有効化

カーネルパラメータを設定します。

```
kernel.core_pattern = /data/core_files/core-%e-%p-%t
```

coredumpファイルの制限を`ulimit -c`コマンドで確認します。

```shell
ulimit -c
```

もし0であれば、/etc/security/limits.confを編集してlimitを設定する必要があります。

> CoreDumpを有効にした後、プログラムが異常が発生すると、プロセスをファイルにエクスポートします。これはプログラムの問題を調査するのに非常に役立ちます。


## その他の重要な設定



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

## 設定が適用されたかどうかを確認する

例えば、`net.unix.max_dgram_qlen = 100`を変更した後、以下のように確認します。

```shell
cat /proc/sys/net/unix/max_dgram_qlen
```

変更が成功した場合は、ここに新しい設定値が表示されます。
