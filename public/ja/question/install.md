# 安装問題

## Swooleのバージョンアップ

peclを使用してインストールまたはアップグレードを行うことができます。

```shell
pecl upgrade swoole
```

また、github/gitee/peclから新しいバージョンをダウンロードし、再インストールしてコンパイルすることもできます。

* Swooleのバージョンを更新するには、古いバージョンのSwooleをアンインストールしたり削除したりする必要はありません。インストールプロセスで古いバージョンが上書きされます。
* Swooleはコンパイルインストール後、追加のファイルはなく、swoole.soのみがあります。他のマシンでコンパイルされたバイナリバージョンであれば、swoole.soを直接上書きすればバージョン切り替えが可能です。
* git cloneでコードをダウンロードし、git pullでコードを更新した後、必ず`phpize`、`./configure`、`make clean`、`make install`を実行する必要があります。
* 对应するdockerを使用してもSwooleのバージョンをアップグレードすることができます。

## phpinfoにはあるがphp -mにはない

まずCLIモードで確認してください。コマンド行で`php --ri swoole`と入力します。

Swooleの拡張情報が出力されたら、インストールが成功したと言えます！

**99.999%の人がこのステップで成功すれば、すぐにswooleを使用できます**

`php -m`や`phpinfo`のウェブページにswooleがあるかどうかは気にしないでください。

なぜならSwooleはcliモードで動作するため、従来のfpmモードでは機能が非常に限られているからです。

fpmモードでは非同期/協程などの主要な機能は**使用できません**。99.999%の人がfpmモードで求めるものを得ることができませんが、なぜfpmモードで拡張情報が表示されないのかと悩んでいます。

**まずはSwooleの運用モードを本当に理解しているかどうかを確認し、それからインストール情報の問題に挑戦しましょう！**

## 原因

Swooleをコンパイルインストールした後、`php-fpm/apache`の`phpinfo`ページにはあるが、CLIの`php -m`にはない場合、cli/php-fpm/apacheが異なるphp.ini設定を使用している可能性があります。

## 解決策

1. php.iniの位置を確認する

cliコマンド行で`php -i | grep php.ini`または`php --ini`を実行し、php.iniの絶対パスを見つける。

`php-fpm/apache`の場合は`phpinfo`ページでphp.iniの絶対パスを見つける。

2. 対応するphp.iniに`extension=swoole.so`があるかどうかを確認する

```shell
cat /path/to/php.ini | grep swoole.so
```

## pcre.h: No such file or directory

Swoole拡張をコンパイルする際に発生する

```bash
fatal error: pcre.h: No such file or directory
```

pcreが欠けているため、libpcreをインストールする必要があります。

### ubuntu/debian

```shell
sudo apt-get install libpcre3 libpcre3-dev
```

### centos/redhat

```shell
sudo yum install pcre-devel
```

### その他のLinux

[PCRE公式ウェブサイト](http://www.pcre.org/)からソースパッケージをダウンロードし、pcreライブラリをコンパイルインストールします。

PCREライブラリをインストールした後、swooleを再コンパイルし、`php --ri swoole`を実行してswoole拡張に関する情報を確認し、`pcre => enabled`があるかどうかを確認します。

## '__builtin_saddl_overflow' was not declared in this scope

```
error: '__builtin_saddl_overflow' was not declared in this scope
  if (UNEXPECTED(__builtin_saddl_overflow(Z_LVAL_P(op1), 1, &lresult))) {

note: in definition of macro 'UNEXPECTED'
 # define UNEXPECTED(condition) __builtin_expect(!!(condition), 0)
```

これは既知の問題です。問題はCentOS上のデフォルトのgccが必要な定義を欠いているため、gccをアップグレードした後でも、PECLは古いコンパイラーを見つける可能性があります。

ドライバーをインストールするためには、まずdevtoolset集合をインストールしてgccをアップグレードする必要があります。以下のように実行します：

```shell
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```

## fatal error: 'openssl/ssl.h' file not found

コンパイル時に--with-openssl-dir=/environment?id=通用参数パラメータを追加して指定してください。opensslライブラリのパスを。

!> pecl/environment?id=peclを使用してSwooleをインストールする場合、opensslを有効にすることもできます。--with-openssl-dir=/environment?id=通用参数パラメータを追加して、例えば：`enable openssl support? [no] : yes --with-openssl-dir=/opt/openssl/`

## makeまたはmake installが実行できず、コンパイルエラーが発生する

通知：PHPメッセージ：PHP警告：PHPスタートアップ：swoole：モジュールを初期化できません  
モジュールはmodule API=20090626でコンパイルされました  
PHPはmodule API=20121212でコンパイルされました  
これらのオプションは一致する必要があります  
不明な場所で0行目

PHPバージョンとコンパイル時に使用された`phpize`と`php-config`が一致していないため、絶対パスを使用してコンパイルし、PHPを実行する必要があります。

```shell
/usr/local/php-5.4.17/bin/phpize
./configure --with-php-config=/usr/local/php-5.4.17/bin/php-config

/usr/local/php-5.4.17/bin/php server.php
```

## xdebugのインストール

```shell
git clone git@github.com:swoole/sdebug.git -b sdebug_2_9 --depth=1

cd sdebug

phpize
./configure
make clean
make
make install

#もしphpize、php-configなどの設定ファイルがデフォルトであれば、直接実行できます。
./rebuild.sh
```

php.iniに拡張を読み込むように変更し、以下情報を追加します。

```ini
zend_extension=xdebug.so

xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=localhost
xdebug.remote_port=8000
xdebug.idekey="xdebug"
```

読み込みが成功したかどうかを確認します。

```shell
php --ri sdebug
```

## configure: error: C preprocessor "/lib/cpp" fails sanity check

インストール時にエラーが発生した場合

```shell
configure: error: C preprocessor "/lib/cpp" fails sanity check
```

必要な依存ライブラリが欠けているため、以下のコマンドでインストールすることができます。

```shell
yum install glibc-headers
yum install gcc-c++
```

## PHP7.4.11+で新しいバージョンのSwooleをコンパイルする際のエラーasm goto :id=asm_goto

MacOSでPHP7.4.11+を使用して新しいバージョンのSwooleをコンパイルする際に、以下のようなエラーが発生しました：

```shell
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:523:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:586:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:656:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:766:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
4 errors generated.
make: *** [ext-src/php_swoole.lo] Error 1
ERROR: `make' failed
```

解决方法：/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.hのソースを編集し、自分の対応するヘッダーファイルパスに変更します。

ZEND_USE_ASM_ARITHMETICを常に0に設定し、以下のコードのelseの内容を保持します。

```c
#if defined(HAVE_ASM_GOTO) && !__has_feature(memory_sanitizer)
# define ZEND_USE_ASM_ARITHMETIC 1
#else
# define ZEND_USE_ASM_ARITHMETIC 0
#endif
```

## fatal error: curl/curl.h: No such file or directory :id=libcurl

--enable-swoole-curlオプションを有効にした後、Swoole拡張をコンパイルすると以下のようなエラーが発生します。

```bash
fatal error: curl/curl.h: No such file or directory
```

その理由はcurl依存が欠けているため、libcurlをインストールする必要があります。

### ubuntu/debian

```shell
sudo apt-get install libcurl4-openssl-dev
```

### centos/redhat

```shell
sudo yum install libcurl-devel
```

### alpine

```shell
apk add curl-dev
```

## fatal error: ares.h: No such file or directory :id=libcares

--enable-caresオプションを有効にした後、Swoole拡張をコンパイルすると以下のようなエラーが発生します。

```bash
fatal error: ares.h: No such file or directory
```

その理由はc-ares依存が欠けているため、libcaresをインストールする必要があります。

### ubuntu/debian

```shell
sudo apt-get install libc-ares-dev
```

### centos/redhat

```shell
sudo yum install c-ares-devel
```

### alpine

```shell
apk add c-ares-dev
```

### MacOs

```shell
brew install c-ares
```
