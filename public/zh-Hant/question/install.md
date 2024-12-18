# 安裝問題

## 升級Swoole版本

可以使用pecl進行安裝和升級

```shell
pecl upgrade swoole
```

也可以直接从github/gitee/pecl下載一個新版本，重新安裝編譯。

* 更新Swoole版本，不需要卸載或者刪除舊版本Swoole，安裝過程會覆蓋舊版本
* Swoole編譯安裝後沒有額外的檔案，僅有一個swoole.so，如果是在其他機器編譯好的二進制版本。直接互相覆蓋swoole.so，即可實現版本切換  
* git clone拉取的程式碼，執行git pull更新程式碼後，務必要再次執行`phpize`、`./configure`、`make clean`、`make install`
* 也可以使用對應的docker去升級對應的Swoole版本

## 在phpinfo中有在php -m中没有

先確認CLI模式下是否有，命令列輸入`php --ri swoole`

如果輸出 了Swoole的擴展資訊就說明你安裝成功了!

**99.999%的人在此步成功就可以直接使用swoole了**

不需要管`php -m`或者`phpinfo`網頁列印出來是否有swoole

因為Swoole是運行在cli模式下的，在傳統的fpm模式下功能十分有限

fpm模式下任何異步/協程等主要功能都**不可以使用**，99.999%的人都不能在fpm模式下得到想要的东西，卻糾結為什麼fpm模式下沒有擴展資訊

**先確定你是否真正理解了Swoole的運行模式，再繼續追究安裝資訊問題！**

### 原因

編譯安裝完Swoole後，在`php-fpm/apache`的`phpinfo`頁面中有，在命令行的`php -m`中沒有，原因可能是`cli/php-fpm/apache`使用不同的php.ini配置

### 解決辦法

1. 確認php.ini的位置 

在`cli`命令列下執行`php -i | grep php.ini`或者`php --ini`找到php.ini的絕對路徑

`php-fpm/apache`則是查看`phpinfo`頁面找到php.ini的絕對路徑

2. 查看對應php.ini是否有`extension=swoole.so`

```shell
cat /path/to/php.ini | grep swoole.so
```

## pcre.h: No such file or directory

編譯Swoole擴展出現

```bash
fatal error: pcre.h: No such file or directory
```

原因是缺少pcre，需要安裝libpcre

### ubuntu/debian

```shell
sudo apt-get install libpcre3 libpcre3-dev
```

### centos/redhat

```shell
sudo yum install pcre-devel
```

### 其他Linux

到[PCRE官方網站](http://www.pcre.org/)下載源码包，編譯安裝`pcre`庫。

安裝好`PCRE`庫後需要重新編譯安裝`swoole`，然後使用`php --ri swoole`查看`swoole`擴展相關資訊中是否有`pcre => enabled`

## '__builtin_saddl_overflow' was not declared in this scope

 ```
error: '__builtin_saddl_overflow' was not declared in this scope
  if (UNEXPECTED(__builtin_saddl_overflow(Z_LVAL_P(op1), 1, &lresult))) {

note: in definition of macro 'UNEXPECTED'
 # define UNEXPECTED(condition) __builtin_expect(!!(condition), 0)
```

這是一個已知的問題。問題是CentOS上的默認gcc缺少必需的定義，即使在升級gcc之後，PECL也會找到舊的編譯器。

要安裝驅動程序，必須首先通過安裝devtoolset集合來升級gcc，如下所示：

```shell
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```

## fatal error: 'openssl/ssl.h' file not found

請在編譯時增加[--with-openssl-dir](/environment?id=通用參數)參數指定 openssl 庫的路徑

!> 使用[pecl](/environment?id=pecl)安裝Swoole時，如果要開啟openssl也可以增加[--with-openssl-dir](/environment?id=通用參數)參數，如：`enable openssl support? [no] : yes --with-openssl-dir=/opt/openssl/`

## make或make install無法執行或編譯錯誤

NOTICE: PHP message: PHP Warning:  PHP Startup: swoole: Unable to initialize module  
Module compiled with module API=20090626  
PHP    compiled with module API=20121212  
These options need to match  
in Unknown on line 0  
   
PHP版本和編譯時使用的`phpize`和`php-config`不對應，需要使用絕對路徑來進行編譯，以及使用絕對路徑來執行PHP。

```shell
/usr/local/php-5.4.17/bin/phpize
./configure --with-php-config=/usr/local/php-5.4.17/bin/php-config

/usr/local/php-5.4.17/bin/php server.php
```

## 安裝xdebug

```shell
git clone git@github.com:swoole/sdebug.git -b sdebug_2_9 --depth=1

cd sdebug

phpize
./configure
make clean
make
make install

#如果你的phpize、php-config等配置檔案都是默認的，那麼可以直接執行
./rebuild.sh
```

修改php.ini加載擴展，加入以下資訊

```ini
zend_extension=xdebug.so

xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=localhost
xdebug.remote_port=8000
xdebug.idekey="xdebug"
```

查看是否加載成功

```shell
php --ri sdebug
```

## configure: error: C preprocessor "/lib/cpp" fails sanity check

安裝時如果報錯

```shell
configure: error: C preprocessor "/lib/cpp" fails sanity check
```

表示缺少必要的依賴庫，可使用如下命令安裝

```shell
yum install glibc-headers
yum install gcc-c++
```

## PHP7.4.11+編譯新版本的Swoole時報錯asm goto :id=asm_goto

在 MacOS 中使用PHP7.4.11+編譯新版本的Swoole時，發現形如以下報錯：

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

解決方法：修改`/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h`源码，注意修改為自己對應的頭檔路徑；

將`ZEND_USE_ASM_ARITHMETIC`修改成恒定為`0`，即保留下述程式碼中`else`的內容

```c
#if defined(HAVE_ASM_GOTO) && !__has_feature(memory_sanitizer)
# define ZEND_USE_ASM_ARITHMETIC 1
#else
# define ZEND_USE_ASM_ARITHMETIC 0
#endif
```

## fatal error: curl/curl.h: No such file or directory :id=libcurl

打開`--enable-swoole-curl`選項後，編譯Swoole擴展出現

```bash
fatal error: curl/curl.h: No such file or directory
```

原因是缺少curl依賴，需要安裝libcurl

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

## 致命錯誤：ares.h：找不到檔案或目錄：id=libcares

開啟 `--enable-cares` 選項後，編譯 Swoole 擴展出現

```bash
fatal error: ares.h: No such file or directory
```

原因是缺少 c-ares 相依，需要安裝 libcares

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
