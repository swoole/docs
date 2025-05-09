# 安装Swoole

`Swoole`扩展是按照`PHP`标准扩展构建的。使用`phpize`来生成编译检测脚本，`./configure`来做编译配置检测，`make`进行编译，`make install`进行安装。

* 如无特殊需求, 请务必编译安装`Swoole`的最新 [Swoole](https://github.com/swoole/swoole-src/releases/) 版本。
* 如果当前用户不是`root`，可能没有`PHP`安装目录的写权限，安装时需要`sudo`或者`su`。
* 如果是在`git`分支上直接`git pull`更新代码，重新编译前务必要执行`make clean`。
* 仅支持 `Linux`(2.3.32 以上内核)、`FreeBSD`、`MacOS` 三种操作系统。
* 低版本Linux系统（如`CentOS 6`）可以使用`RedHat`提供的`devtools`编译，[参考文档](https://blog.csdn.net/ppdouble/article/details/52894271)  。
* 在`Windows`平台，可使用`WSL(Windows Subsystem for Linux)`或`CygWin`。
* 部分扩展与`Swoole`扩展不兼容，参考[扩展冲突](/getting_started/extension)。

## 安装准备

安装前必须保证系统已经安装了下列软件

- `4.8`版本需要 `PHP-7.2` 或更高版本
- `5.0`版本需要 `PHP-8.0` 或更高版本
- `6.0`版本需要 `PHP-8.1` 或更高版本
- `gcc-4.8` 或更高版本
- `make`
- `autoconf`

## 快速安装

> 1.下载swoole源码

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2.从源码编译安装

下载源代码包后，在终端进入源码目录，执行下面的命令进行编译和安装

!> ubuntu 没有安装phpize可执行命令：`sudo apt-get install php-dev`来安装phpize

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3.启用扩展

编译安装到系统成功后, 需要在`php.ini`中加入一行`extension=swoole.so`来启用Swoole扩展

## 进阶完整编译示例

!> 初次接触Swoole的开发者请先尝试上方的简单编译，如果有进一步的需要，可以根据具体的需求和版本，调整以下示例中的编译参数。[编译参数参考](/environment?id=编译选项)

以下脚本会下载并编译`master`分支的源码, 需保证你已安装所有依赖, 否则会遇到各种依赖错误。

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

> 注意: PECL发布时间晚于GitHub发布时间

Swoole 项目已收录到 PHP 官方扩展库，除了手动下载编译外，还可以通过 PHP 官方提供的`pecl`命令，一键下载安装

```shell
pecl install swoole
```

通过 PECL 安装 Swoole 时，在安装过程中它会询问是否要启用某些功能，这也可以在运行安装之前提供，例如：

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#或者
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```

## PIE

Swoole 项目支持由 PHP 全新的扩展安装工具 PIE 一键下载安装。

```shell
pie install swoole/swoole:5.1.5
```

通过 PIE 安装 Swoole 时，在安装过程中它会询问是否要启用某些功能，这也可以在运行安装之前提供，例如：

```shell
pie install swoole/swoole:5.1.5 --enable-socket --enable-swoole-curl
```

## 添加Swoole到php.ini

最后，编译安装成功后，修改`php.ini`加入

```ini
extension=swoole.so
```

通过`php -m`来查看是否成功加载了`swoole.so`，如果没有可能是`php.ini`的路径不对。  
可以使用`php --ini`来定位到`php.ini`的绝对路径，`Loaded Configuration File`一项显示的是加载的php.ini文件，如果值为`none`证明根本没加载任何`php.ini`文件，需要自己创建。

!> 对`PHP`版本支持和`PHP`官方维护版本保持一致，参考[PHP版本支持时间表](http://php.net/supported-versions.php)

## 其他平台编译

ARM平台（树莓派Raspberry PI）

* 使用 `GCC` 交叉编译
* 在编译 `Swoole` 时，需要手动修改 `Makefile` 去掉 `-O2` 编译参数

MIPS平台（OpenWrt路由器）

* 使用 GCC 交叉编译

Windows WSL

`Windows 10` 系统增加了 `Linux` 子系统支持，`BashOnWindows` 环境下也可以使用 `Swoole`。安装命令

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> `WSL` 环境下必须关闭 `daemonize` 选项  
低于`17101`的`WSL`，源码安装`configure`后需要修改 `config.h` 关闭 `HAVE_SIGNALFD`

## Docker官方镜像

- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)  
- dockerhub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)

## 编译选项

这里是`./configure`编译配置的额外参数，用于开启某些特性

### 通用参数

#### --enable-openssl

启用`SSL`支持

> 使用操作系统提供的`libssl.so`动态连接库

#### --with-openssl-dir

启用`SSL`支持并指定`openssl`库的路径, 需跟上路径参数，如: `--with-openssl-dir=/opt/openssl/`

#### --enable-http2

开启对`HTTP2`的支持

> 依赖`nghttp2`库。在`V4.3.0`版本后不再需要安装依赖, 改为内置, 但仍需要增加该编译参数来开启`http2`支持，`Swoole5`默认启用该参数。

#### --enable-swoole-json

启用对[swoole_substr_json_decode](/functions?id=swoole_substr_json_decode)的支持，`Swoole5`开始默认启用该参数

> 依赖`json`扩展，`v4.5.7`版本可用

#### --enable-swoole-curl

启用对[SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl)的支持，开启这个需要确保`php`和`Swoole`使用相同的`libcurl`的共享库和头文件，否则会出现一些无法预知的问题。

> `v4.6.0`版本可用。如果编译报错`curl/curl.h: No such file or directory`，请查看[安装问题](/question/install?id=libcurl)

#### --enable-cares

启用对 `c-ares` 的支持

> 依赖`c-ares`库，`v4.7.0`版本可用。如果编译报错`ares.h: No such file or directory`，请查看[安装问题](/question/install?id=libcares)

#### --with-jemalloc-dir

启用对 `jemalloc` 的支持

#### --enable-brotli

启用对 `libbrotli` 压缩支持

#### --with-brotli-dir

启用`libbrotli`压缩支持并指定`libbrotli`库的路径, 需跟上路径参数，如: `--with-brotli-dir=/opt/brotli/`

#### --enable-swoole-pgsql

启用`PostgreSQL`数据库协程化。

> `Swoole5.0`之前是使用协程客户端进行对`PostgreSQL`进行协程化，`Swoole5.1`之后，除了使用协程客户端进行协程化，也能够使用原生的`pdo_pgsql`协程化`PostgreSQL`了。

#### --with-swoole-odbc

启动对`pdo_odbc`协程化，该参数启用之后，所有支持`odbc`接口的数据库都能够协程化了。



>`v5.1.0`版本后可用,需依赖unixodbc-dev

示例配置

```
with-swoole-odbc="unixODBC,/usr"
```

#### --with-swoole-oracle

启用对`pdo_oci`的协程化，该参数启用之后，`oracle`数据库的增删改查都会触发协程操作。

>`v5.1.0`版本后可用

#### --enable-swoole-sqlite

启用对`pdo_sqlite`的协程化，该参数启用之后，`sqlite`数据库的增删改查都会触发协程操作。

>`v5.1.0`版本后可用

#### --enable-swoole-thread

开启`swoole`多线程模式，添加这个编译选项后，`Swoole`将会由多进程单线程模型变成单进程多线程模型。

>`v6.0`版本后可用，且`PHP`必须是`ZTS`模式

#### --enable-iouring

添加这个编译选项后，`swoole`的文件异步处理将会由线程池模拟实现变成`iouring`。
`iouring`是`Linux`系统独有的特性，可大幅提升文件系统`IO`性能。但需要高版本内核，使用前请检查当前`Linux`内核版本是否支持`iouring`。

> `v6.0`版本后可用，而且需要安装`liburing`依赖来支持此特性，如果磁盘性能够好的情况下两种模式性能相差不大，只有`I/O`压力较大的情况下，`iouring`模式性能会优于异步线程模式。

在`docker`容器中使用`iouring`时，出现`Create io_uring failed, the error code is 38`错误。请尝试下面的解决方法：

1. 将`docker`容器的内核版本升级到`5.1.0`以上
2. 使用`--privileged`参数来运行容器
3. 运行时增加`--security-opt seccomp:unconfined`参数，允许`docker`容器使用`io_uring`特性


#### --enable-zstd

添加这个编译选项后，`http`服务端和客户端之间可以使用高性能压缩工具`Zstd`压缩响应。

>`v6.0`版本后可用，而且需要安装`libzstd`依赖来支持此特性。

### 特殊参数

!> **如无历史原因不建议启用**

#### --enable-mysqlnd

启用`mysqlnd`支持，启用`Coroutine\MySQL::escapse`方法。启用此参数后，`PHP`必须有`mysqlnd`模块，否则会导致`Swoole`无法运行。

> 依赖`mysqlnd`扩展

#### --enable-sockets

增加对PHP的`sockets`资源的支持。开启此参数，[Swoole\Event::add](/event?id=add)就可以添加`sockets`扩展创建的连接到`Swoole`的[事件循环](/learn?id=什么是eventloop)中。  
`Server`和`Client`的 [getSocket()](/server/methods?id=getsocket)方法也需要依赖此编译参数。

> 依赖`sockets`扩展, `v4.3.2`版本后该参数的作用被削弱了, 因为Swoole内置的[Coroutine\Socket](/coroutine_client/socket)可以完成大部分事情

### Debug参数

!> **生产环境不可以启用**

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
