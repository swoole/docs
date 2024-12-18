# 安裝Swoole

`Swoole`擴展是依照`PHP`標準擴展構建的。使用`phpize`來生成編譯檢測腳本，`./configure`來做編譯配置檢測，`make`進行編譯，`make install`進行安裝。

* 如無特殊需求, 請務必編譯安裝`Swoole`的最新 [Swoole](https://github.com/swoole/swoole-src/releases/) 版本。
* 如果當前用戶不是`root`，可能沒有`PHP`安裝目錄的寫權限，安裝時需要`sudo`或者`su`。
* 如果是在`git`分支上直接`git pull`更新代碼，重新編譯前務必執行`make clean`。
*僅支持 `Linux`(2.3.32 以上內核)、`FreeBSD`、`MacOS` 三種作業系統。
* 低版本Linux系統（如`CentOS 6`）可以使用`RedHat`提供的`devtools`編譯，[參考文檔](https://blog.csdn.net/ppdouble/article/details/52894271)  。
* 在`Windows`平台，可使用`WSL(Windows Subsystem for Linux)`或`CygWin`。
* 部分擴展與`Swoole`擴展不兼容，參考[擴展衝突](/getting_started/extension)。

## 安裝準備

安裝前必須保證系統已經安裝了下列軟體

- `4.8`版本需要 `PHP-7.2` 或更高版本
- `5.0`版本需要 `PHP-8.0` 或更高版本
- `6.0`版本需要 `PHP-8.1` 或更高版本
- `gcc-4.8` 或更高版本
- `make`
- `autoconf`

## 快速安裝

> 1.下載swoole源碼

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2. 從源碼編譯安裝

下載源碼包後，在終端進入源碼目錄，執行下面的命令進行編譯和安裝

!> ubuntu 沒有安裝phpize可執行命令：`sudo apt-get install php-dev`來安裝phpize

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3.啟用擴展

編譯安裝到系統成功後, 需要在`php.ini`中加入一行`extension=swoole.so`來啟用Swoole擴展

## 進階完整編譯示例

!> 初次接觸Swoole的開發者請先嘗試上方的簡單編譯，如果有進一步的需要，可以根據具體的需求和版本，調整以下示例中的編譯參數。[編譯參數參考](/environment?id=編譯選項)

以下腳本会下載並編譯`master`分支的源碼, 需保證你已安裝所有依賴, 否則會遇到各種依賴錯誤。

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

> 注意: PECL發布時間晚於GitHub發布時間

Swoole 項目已收錄到 PHP 官方擴展庫，除了手動下載編譯外，還可以通過 PHP 官方提供的`pecl`命令，一鍵安裝下載

```shell
pecl install swoole
```

通過 PECL 安裝 Swoole 時，在安裝過程中它會詢問是否要啟用某些功能，這也可以在運行安裝之前提供，例如：

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#或者
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```

## 添加Swoole到php.ini

最後，編譯安裝成功後，修改`php.ini`加入

```ini
extension=swoole.so
```

通過`php -m`來查看是否成功加載了`swoole.so`，如果沒有可能是`php.ini`的路徑不對。  
可以使用`php --ini`來定位到`php.ini`的絕對路徑，`Loaded Configuration File`一項顯示的是加載的php.ini文件，如果值為`none`證明根本沒有加載任何`php.ini`文件，需要自己創建。

!> 對`PHP`版本支持和`PHP`官方維護版本保持一致，參考[PHP版本支持時間表](http://php.net/supported-versions.php)

## 其他平台編譯

ARM平台（樹莓派Raspberry PI）

* 使用 `GCC` 交叉編譯
* 在編譯 `Swoole` 時，需要手動修改 `Makefile` 去掉 `-O2` 編譯參數

MIPS平台（OpenWrt路由器）

* 使用 GCC 交叉編譯

Windows WSL

`Windows 10` 系統增加了 `Linux` 子系統支持，`BashOnWindows` 環境下也可以使用 `Swoole`。安裝命令

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> `WSL` 環境下必須關閉 `daemonize` 選項  
低於`17101`的`WSL`，源码安裝`configure`後需要修改 `config.h` 關閉 `HAVE_SIGNALFD`

## Docker官方鏡像

- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)  
- dockerhub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)

## 編譯選項

這裡是`./configure`編譯配置的額外參數，用於開啟某些特性

### 通用參數

#### --enable-openssl

啟用`SSL`支持

> 使用操作系統提供的`libssl.so`動態連接庫

#### --with-openssl-dir

啟用`SSL`支持並指定`openssl`庫的路径, 需跟上路徑參數，如: `--with-openssl-dir=/opt/openssl/`

#### --enable-http2

開啟對`HTTP2`的支持

> 依賴`nghttp2`庫。在`V4.3.0`版本後不再需要安裝依賴, 改为內置, 但仍需要增加該編譯參數來開啟`http2`支持，`Swoole5`默認啟用該參數。

#### --enable-swoole-json

啟用對[swoole_substr_json_decode](/functions?id=swoole_substr_json_decode)的支持，`Swoole5`開始默認啟用該參數

> 依賴`json`擴展，`v4.5.7`版本可用

#### --enable-swoole-curl

啟用對[SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl)的支持，開啟這個需要確保`php`和`Swoole`使用相同的`libcurl`的共享庫和頭文件，否則會出現一些無法預知的問題。

> `v4.6.0`版本可用。如果編譯報錯`curl/curl.h: No such file or directory`，請查看[安裝問題](/question/install?id=libcurl)

#### --enable-cares

啟用對 `c-ares` 的支持

> 依賴`c-ares`庫，`v4.7.0`版本可用。如果編譯報錯`ares.h: No such file or directory`，請查看[安裝問題](/question/install?id=libcares)

#### --with-jemalloc-dir

啟用對 `jemalloc` 的支持

#### --enable-brotli

啟用對 `libbrotli` 壓縮支持

#### --with-brotli-dir

啟用`libbrotli`壓縮支持並指定`libbrotli`庫的路徑, 需跟上路徑參數，如: `--with-brotli-dir=/opt/brotli/`

#### --enable-swoole-pgsql

啟用`PostgreSQL`數據庫協程化。

> `Swoole5.0`之前是使用協程客戶端進行對`PostgreSQL`進行協程化，`Swoole5.1`之後，除了使用協程客戶端進行協程化，也能夠使用原生的`pdo_pgsql`協程化`PostgreSQL`了。

#### --with-swoole-odbc

啟動對`pdo_odbc`協程化，該參數啟用之後，所有支持`odbc`接口的數據庫都能夠協程化了。

>`v5.1.0`版本後可用,需依賴unixodbc-dev

示例配置

```
with-swoole-odbc="unixODBC,/usr"
```

#### --with-swoole-oracle

啟用對`pdo_oci`的協程化，該參數啟用之後，`oracle`數據庫的增刪改查都會觸發協程操作。

>`v5.1.0`版本後可用

#### --enable-swoole-sqlite

啟用對`pdo_sqlite`的協程化，該參數啟用之後，`sqlite`數據庫的增刪改查都會觸發協程操作。

>`v5.1.0`版本後可用

#### --enable-swoole-thread

開啟`swoole`多線程模式，添加這個編譯選項後，`Swoole`將會由多進程單線程模型變成單進程多線程模型。

>`v6.0`版本後可用，且`PHP`必須是`ZTS`模式

#### --enable-iouring

添加這個編譯選項後，`swoole`的文件異步處理將會由異步線程變成`iouring`模式。

>`v6.0`版本後可用，而且需要安裝`liburing`依賴來支持此特性，如果磁盤性能够好的情況下兩種模式性能相差不大，只有`I/O`壓力較大的情況下，`iouring`模式性能會優於異步線程模式。
### 特殊参数

!> **除非有历史原因，否则不建议启用**

#### --enable-mysqlnd

启用`mysqlnd`支持，启用`Coroutine\MySQL::escapse`方法。启用此参数后，`PHP`必须有`mysqlnd`模块，否则会导致`Swoole`无法运行。

> 依赖`mysqlnd`扩展

#### --enable-sockets

增加对PHP的`sockets`资源的支持。开启此参数，[Swoole\Event::add](/event?id=add)就可以添加`sockets`扩展创建的连接到`Swoole`的[事件循环](/learn?id=什么是eventloop)中。  
`Server`和`Client`的 [getSocket()](/server/methods?id=getsocket)方法也需要依赖此编译参数。

> 依赖`sockets`扩展, `v4.3.2`版本后该参数的作用被削弱了, 因为Swoole内置的[Coroutine\Socket](/coroutine_client/socket)可以完成大部分事情

### Debug参数

!> **生产环境不得启用**

#### --enable-debug

打开调试模式。使用`gdb`跟踪需要在编译`Swoole`时增加此参数。

#### --enable-debug-log

打开内核DEBUG日志。**（Swoole版本 >= 4.2.0）**

#### --enable-trace-log

打开追踪日志，开启此选项后swoole将打印各类细节的调试日志，仅内核开发时使用

#### --enable-swoole-coro-time

启用对协程运行时间计算，此选项开启后，可以使用Swoole\Coroutine::getExecuteTime()计算协程执行时间，不包括I\O等待时间。

### PHP编译参数

#### --enable-swoole

静态编译 Swoole 扩展到 PHP 中，根据下面的操作，就能出现`--enable-swoole`这个选项。

```shell
cp -r /home/swoole-src /home/php-src/ext
cd /home/php-src
./buildconf --force
./configure --help | grep swoole
```

!> 此选项是在编译 PHP 而不是 Swoole 时使用的

## 常见问题

* [Swoole安装常见问题](/question/install)
