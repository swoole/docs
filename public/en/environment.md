# Installing Swoole

The `Swoole` extension is built according to the standard PHP extension. Use `phpize` to generate compilation detection script, `./configure` to do compilation configuration detection, `make` for compilation, and `make install` for installation.

* If there are no special requirements, be sure to compile and install the latest version of `Swoole` from [Swoole](https://github.com/swoole/swoole-src/releases/).
* If the current user is not `root`, there may not be write permission for the PHP installation directory, so you need to use `sudo` or `su` during installation.
* If updating the code directly on a `git` branch, make sure to execute `make clean` before recompiling.
* Only supports three operating systems: `Linux` (kernel 2.3.32 or higher), `FreeBSD`, and `MacOS`.
* Older Linux systems (such as `CentOS 6`) can use `RedHat` provided `devtools` for compilation, see [reference document](https://blog.csdn.net/ppdouble/article/details/52894271).
* On the `Windows` platform, you can use `WSL (Windows Subsystem for Linux)` or `CygWin`.
* Some extensions may not be compatible with the `Swoole` extension, refer to [extension conflicts](/getting_started/extension).
## Installation Preparation

Before installing, make sure that the following software is already installed on your system:

- Version `4.8` requires `php-7.2` or higher
- Version `5.0` requires `php-8.0` or higher
- `gcc-4.8` or higher
- `make`
- `autoconf`
## Quick Installation

> 1. Download Swoole source code

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2. Compile and install from source code

After downloading the source code package, enter the source code directory in the terminal and execute the following commands for compilation and installation

!> If `phpize` is not installed on Ubuntu, you can install it by running the command: `sudo apt-get install php-dev`

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3. Enable the extension

After successfully compiling and installing to the system, you need to add `extension=swoole.so` in `php.ini` to enable the Swoole extension
## Advanced Complete Compilation Example

!> Developers who are new to Swoole should first try the simple compilation above. If further customization is needed, adjust the compilation parameters in the example below according to specific requirements and versions. [Compilation parameter reference](/environment?id=编译选项)

The following script will download and compile the source code from the `master` branch. Make sure you have installed all dependencies, otherwise you will encounter various dependency errors.

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

> Note: The PECL release time is later than the GitHub release time.

The Swoole project has been included in the PHP official extension library. Besides manually downloading and compiling, you can also use the `pecl` command provided by PHP official to download and install it in one go.

```shell
pecl install swoole
```

When installing Swoole via PECL, during the installation process, it will ask whether to enable certain features. You can also provide this information before running the installation, for example:

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#or
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```
## Adding Swoole to php.ini

After successfully compiling and installing, modify the `php.ini` file to add:

```ini
extension=swoole.so
```

Check if `swoole.so` was successfully loaded by running `php -m`. If it's not loaded, it could be due to an incorrect `php.ini` file path.  
You can use `php --ini` to locate the absolute path of `php.ini`. The `Loaded Configuration File` line will show the loaded php.ini file; if it shows `none`, it means that no `php.ini` file was loaded, and you need to create one.

!> Keep consistent with the supported `PHP` versions and PHP's official maintenance versions. Refer to [PHP Supported Versions](http://php.net/supported-versions.php) for more details.
## Compilation on Other Platforms

ARM Platform (Raspberry Pi)

* Cross-compilation using `GCC`
* When compiling `Swoole`, manually modify the `Makefile` to remove the `-O2` compilation flag

MIPS Platform (OpenWrt Router)

* Cross-compilation using GCC

Windows WSL

With Windows 10, the system has added support for a Linux subsystem, enabling the use of `Swoole` in the `BashOnWindows` environment. Installation command:

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> The `daemonize` option must be disabled in the WSL environment  
For WSL versions below `17101`, after configuring the source installation, it is necessary to modify the `config.h` to disable `HAVE_SIGNALFD`
## Official Docker Image

- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)
- Docker Hub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)
## Compilation options

Here are the additional parameters for `./configure` compilation configuration, used to enable certain features.
### General Parameters
#### --enable-openssl

Enable `SSL` support

> Use the `libssl.so` shared library provided by the operating system
#### --with-openssl-dir

Enable `SSL` support and specify the path to the `openssl` library, followed by the path parameter, for example: `--with-openssl-dir=/opt/openssl/`
#### --enable-http2

Enable support for `HTTP2`

> Depends on the `nghttp2` library. After version `V4.3.0`, the dependency is no longer required to be installed separately, it is now built-in. However, this compilation parameter still needs to be added to enable `http2` support. `Swoole5` enables this parameter by default.
#### --enable-swoole-json

Enable support for [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode), which is enabled by default starting from `Swoole 5`.

> Depends on the `json` extension, available from version `v4.5.7`.
#### --enable-swoole-curl

Enable support for [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl)

> Available in `v4.6.0`. If you encounter the error `curl/curl.h: No such file or directory` during compilation, please refer to the [installation troubleshooting](/question/install?id=libcurl) section.
#### --enable-cares

Enable support for `c-ares`.

> Depends on the `c-ares` library, version `v4.7.0` is available. If you encounter the error `ares.h: No such file or directory` during compilation, please refer to the [installation problem](/question/install?id=libcares).
#### --with-jemalloc-dir

Enable support for `jemalloc`
#### --enable-brotli

Enable compression support for `libbrotli`
#### --with-brotli-dir

Enable `libbrotli` compression support and specify the path of the `libbrotli` library. You should follow it with the path parameter, for example: `--with-brotli-dir=/opt/brotli/`
#### --enable-swoole-pgsql

Enable coroutine support for `PostgreSQL` database.

> Before `Swoole5.0`, coroutine clients were used for coroutine support of `PostgreSQL`. After `Swoole5.1`, in addition to using coroutine clients, you can also use native `pdo_pgsql` for coroutine support of `PostgreSQL`.
#### --with-swoole-odbc

Enable coroutine for `pdo_odbc`. After enabling this parameter, all databases that support the `odbc` interface can be used with coroutine.

> Available since version `v5.1.0`
#### --with-swoole-oracle

Enable coroutine support for `pdo_oci`. After enabling this parameter, all operations such as insert, delete, update, and select on the `oracle` database will trigger coroutine operations.

> Available since version `v5.1.0`
#### --enable-swoole-sqlite

Enable coroutine support for `pdo_sqlite`. Once this parameter is enabled, coroutine operations will be triggered for inserts, updates, and queries on SQLite databases.

>Available after version `v5.1.0`
### Special Parameters

!> **Not recommended to enable unless for historical reasons**
#### --enable-mysqlnd

Enables `mysqlnd` support and `Coroutine\MySQL::escape` method. After enabling this option, PHP must have the `mysqlnd` module, otherwise Swoole will not be able to run.

> Depends on the `mysqlnd` extension
#### --enable-sockets

Adds support for the `sockets` resource in PHP. Enabling this parameter allows [Swoole\Event::add](/event?id=add) to add connections created by the `sockets` extension to the [event loop](/learn?id=what-is-an-eventloop) of Swoole.  
The [getSocket()](/server/methods?id=getsocket) methods of `Server` and `Client` also depend on this compilation parameter.

> Depending on the `sockets` extension, the role of this parameter has been reduced after version `v4.3.2`, as most tasks can be completed using the built-in [Coroutine\Socket](/coroutine_client/socket) in Swoole.
### Debug Parameters

!> **Do not enable in production environment**
#### --enable-debug

Enable debug mode. Use `gdb` to trace when compiling `Swoole`.
#### --enable-debug-log

Enable the kernel DEBUG log. **(Swoole version >= 4.2.0)**
#### --enable-trace-log

Enable trace logging. After enabling this option, Swoole will print various detailed debug logs. Only used for internal kernel development.
#### --enable-swoole-coro-time

Enable calculation of coroutine running time. After enabling this option, you can use Swoole\Coroutine::getExecuteTime() to calculate the execution time of coroutines, excluding I/O wait time.
### PHP Compilation Parameters
#### --enable-swoole

To statically compile the Swoole extension into PHP, follow the instructions below to enable the `--enable-swoole` option.

```shell
cp -r /home/swoole-src /home/php-src/ext
cd /home/php-src
./buildconf --force
./configure --help | grep swoole
```

!> This option is used when compiling PHP, not Swoole.
## Common Questions

* [Common Issues with Installing Swoole](/question/install)
