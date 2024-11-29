# Swooleのインストール

`Swoole`拡張は`PHP`標準拡張と同様に構築されています。`phpize`を使用してコンパイル検出スクリプトを生成し、`./configure`でコンパイル設定検査を行い、`make`でコンパイルを行い、`make install`でインストールを行います。

* 特別な要件がない場合は、最新の[Swoole](https://github.com/swoole/swoole-src/releases/)バージョンをコンパイルしてインストールしてください。
* 当前ユーザーが`root`でない場合、PHPのインストールディレクトリの書き込み権限がない可能性があります。インストール時には`sudo`または`su`が必要です。
* `git`ブランチから直接`git pull`でコードを更新した場合は、再コンパイルする前に必ず`make clean`を実行してください。
* Linux(2.3.32以上の内核)、FreeBSD、MacOSの3つのオペレーティングシステムのみサポートされています。
* CentOS 6などの低バージョンのLinuxシステムでは、RedHatが提供する`devtools`でコンパイルすることができます。[参考文档](https://blog.csdn.net/ppdouble/article/details/52894271) 。
* Windowsプラットフォームでは、WSL(Windows Subsystem for Linux)またはCygWinを使用できます。
* 一部の拡張は`Swoole`拡張と互換性がない場合があります。[拡張の衝突](/getting_started/extension)を参照してください。
## インストール準備

インストール前には、以下のソフトウェアがインストールされていることを確認する必要があります- `4.8`バージョンは`PHP-7.2`またはそれ以上が必要- `5.0`バージョンは`PHP-8.0`またはそれ以上が必要- `6.0`バージョンは`PHP-8.1`またはそれ以上が必要- `gcc-4.8`またはそれ以上が必要- `make`
- `autoconf`
## インストールのショートカット

> 1. swooleのソースコードをダウンロードする

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2. ソースコードからコンパイルしてインストールする

ソースコードパッケージをダウンロードした後、ターミナルでソースコードのディレクトリに入り、以下のコマンドを実行してコンパイルとインストールを行います。

!> ubuntuではphpizeがない場合は、`sudo apt-get install php-dev`でphpizeをインストールします。

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3. 拡張を有効にする

システムに成功裏にコンパイル・インストールされた後は、`php.ini`に`extension=swoole.so`という行を追加してSwoole拡張を有効にする必要があります。
## 进級完全コンパイル例

!> Swooleに初めて触れる開発者は、上記のシンプルなコンパイルを試してみてください。さらに必要な場合は、具体的な要件とバージョンに基づいて、以下の例のコンパイルパラメータを調整してください。[コンパイルパラメータ参照](/environment?id=编译选项)

以下のスクリプトは、`master`ブランチのソースコードをダウンロードし、コンパイルすることができます。すべての依存項目をインストールしていることを確認してください。そうでないと、様々な依存性のエラーに遭遇する可能性があります。

```shell
mkdir -p ~/build && \
cd ~/build && \
rm -rf ./swoole-src && \
curl -o ./tmp/swoole.tar.gz https://github.com/swoole/swoole-src/archive/master.tar.gz -L && \
tar zxvf ./tmp/swoole.tar.gz && \
mv swoole-src* swoole-src && \
cd swoole-src && \
phpize && \
./configure \
--enable-openssl --enable-sockets --enable-mysqlnd --enable-swoole-curl --enable-cares --enable-swoole-pgsql && \
sudo make && sudo make install
```
## PECL

> 注意: PECLのリリース日はGitHubのリリース日よりも後れています

SwooleプロジェクトはPHP公式拡張ライブラリに組み込まれており、手動でダウンロード・コンパイルする以外にも、PHP公式が提供する`pecl`コマンドを使用して、一発でダウンロード・インストールすることができます。

```shell
pecl install swoole
```

PECLでSwooleをインストールする際、インストール過程で特定の機能を有効にするかどうかを尋ねることがあります。これは、インストール前に提供することもできます。例えば：

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#または
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```
## Swooleをphp.iniに追加する

最後に、コンパイル・インストールが成功した後、`php.ini`に以下の行を追加してください。

```ini
extension=swoole.so
```

`php -m`を使用して`swoole.so`が成功裏に読み込まれているかどうかを確認します。もし読み込まれていない場合は、`php.ini`のパスが正しくない可能性があります。 
`php --ini`を使用して`php.ini`の絶対パスを確認できます。`Loaded Configuration File`の項目に表示されるのは読み込まれた`php.ini`ファイルで、値が`none`であれば、実際には`php.ini`ファイルを一切読み込んでいないことを意味します。自分で作成する必要があります。

!> PHPバージョンのサポートはPHP公式のメンテナンスバージョンと一致していることを確認してください。参考になる[PHPバージョンサポートスケジュール](http://php.net/supported-versions.php)
## その他のプラットフォームのコンパイル

ARMプラットフォーム（Raspberry PI）

* `GCC`を使用したクロスコンパイル
* `Swoole`をコンパイルする際には、手動で`Makefile`を変更して`-O2`コンパイルパラメータを取り除く必要があります。

MIPSプラットフォーム（OpenWrtルーター）

* `GCC`を使用したクロスコンパイル

Windows WSL

Windows 10はLinuxサブシステムのサポートを追加しました。`BashOnWindows`環境でも`Swoole`を使用できます。インストール命令

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> WSL環境では`daemonize`オプションを閉じる必要があります  
WSL 17101未満では、ソースコードから`configure`をインストールした後、`config.h`を変更して`HAVE_SIGNALFD`を閉じる必要があります。
## Docker公式イメージ- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)  
- dockerhub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)
## コンパイルオプション

これは`./configure`コンパイル設定の追加パラメータで、特定の機能を有効にするためのものです。
### 一般的なパラメータ

#### --enable-openssl

SSLサポートを有効にする

> 操作系統が提供する`libssl.so`ダイナミックリンクライブラリを使用する

#### --with-openssl-dir

SSLサポートを有効にし、`openssl`ライブラリのパスを指定します。パスパラメータを指定する必要があります。例: `--with-openssl-dir=/opt/openssl/`

#### --enable-http2

HTTP/2サポートを有効にする

> `nghttp2`ライブラリに依存します。V4.3.0バージョン以降は依存関係をインストールせず、内蔵に変更されましたが、HTTP/2サポートを有効にするためには、このコンパイルパラメータを追加する必要があります。`Swoole5`はこのパラメータをデフォルトで有効にしています。

#### --enable-swoole-json

`swoole_substr_json_decode`に対するサポートを有効にする。`Swoole5`からはこのパラメータがデフォルトで有効になっています。

> `json`拡張に依存しており、v4.5.7バージョンで利用可能です。

#### --enable-swoole-curl

SWOOLE_HOOK_NATIVE_CURLに対するサポートを有効にする。この機能を有効にするには、`php`と`Swoole`が同じ`libcurl`の共有ライブラリとヘッドレスファイルを使用することを確認する必要があります。そうでなければ、予期せぬ問題が発生する可能性があります。

> v4.6.0バージョンで利用可能です。コンパイル時に`curl/curl.h: No such file or directory`というエラーが発生した場合は、[インストール問題](/question/install?id=libcurl)を参照してください。

#### --enable-cares

c-aresに対するサポートを有効にする

> c-aresライブラリに依存しており、v4.7.0バージョンで利用可能です。コンパイル時に`ares.h: No such file or directory`というエラーが発生した場合は、[インストール問題](/question/install?id=libcares)を参照してください。

#### --with-jemalloc-dir

jemallocに対するサポートを有効にする

#### --enable-brotli

libbrotli圧縮に対するサポートを有効にする

#### --with-brotli-dir

libbrotli圧縮に対するサポートを有効にし、libbrotliライブラリのパスを指定します。パスパラメータを指定する必要があります。例: `--with-brotli-dir=/opt/brotli/`

#### --enable-swoole-pgsql

PostgreSQLデータベースのキューサービス化を有効にする。

> Swoole5.0以前はキューサービス化用のキューアンチでPostgreSQLを行っていましたが、Swoole5.1以降は、キューアンチ用のキューアンチとしても使用できるようになりました。さらに、原生的なpdo_pgsqlを使用してPostgreSQLをキューアンチできるようになりました。

#### --with-swoole-odbc

pdo_odbcのキューアンチを開始します。このパラメータを有効にした場合、odbcインターフェースをサポートするすべてのデータベースがキューアンチできるようになります。



>`v5.1.0`バージョン以降に利用可能で、unixodbc-devに依存します。

例えばの設定

```
with-swoole-odbc="unixODBC,/usr"
```

#### --with-swoole-oracle

pdo_ociのキューアンチを開始します。このパラメータを有効にした場合、oracleデータベースの追加、削除、更新、検索操作がすべてキューアンチされます。

>`v5.1.0`バージョン以降に利用可能です。

#### --enable-swoole-sqlite

pdo_sqliteのキューアンチを開始します。このパラメータを有効にした場合、sqliteデータベースの追加、削除、更新、検索操作がすべてキューアンチされます。

>`v5.1.0`バージョン以降に利用可能です。

#### --enable-swoole-thread

swooleのマルチタスクモードを開始します。このコンパイルオプションを追加した後、swooleはマルチプロセス単一タスクモードからマルチプロセスマルチタスクモードに変更されます。

>`v6.0`バージョン以降に利用可能で、PHPはZTSモードでなければなりません。

#### --enable-iouring

このコンパイルオプションを追加した後、swooleのファイル非同期処理は非同期タスクモードからiouringモードに変更されます。

>`v6.0`バージョン以降に利用可能で、liburingの依存をインストールしてこの特性をサポートする必要があります。ディスク性能が良ければ、異なるモード間のパフォーマンスの差はほとんどありませんが、I/O圧力が大きい場合には、iouringモードのパフォーマンスが非同期タスクモードよりも優れています。
### 特別なパラメータ

!> **特に歴史的な理由がない限り、このオプションを有効にすることはお勧めしません**

#### --enable-mysqlnd

`mysqlnd`サポートを有効にし、`Coroutine\MySQL::escapse`メソッドを有効にします。このオプションを有効にした場合、`PHP`には`mysqlnd`モジュールが必要であり、そうでなければ`swoole`は動作しなくなります。

> `mysqlnd`拡張に依存しています。

#### --enable-sockets

PHPの`sockets`リ
