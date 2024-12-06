# Installation Issues


## Upgrading Swoole Version

You can use `pecl` to install and upgrade

```shell
pecl upgrade swoole
```

Alternatively, you can download a new version directly from GitHub/Gitee/PECL, and then recompile it.

* To update the Swoole version, there is no need to uninstall or delete the old version of Swoole. The installation process will overwrite the old version.
* After compiling and installing Swoole, there are no additional files; only a `swoole.so` file, which is compiled on another machine. Simply overwrite the `swoole.so` files to switch versions.
* If you clone the code using `git clone`, after pulling the latest updates with `git pull`, make sure to run `phpize`, `./configure`, `make clean`, and `make install` again.
* You can also use the corresponding Docker to upgrade the Swoole version.


## Checking in phpinfo() and not in php -m

First, confirm whether it is available in CLI mode. Type `php --ri swoole` at the command line.

If the extension information for Swoole is output, it means you have installed it successfully!

**99.999% of people can use Swoole directly at this step!**

Do not worry about whether `php -m` or the `phpinfo` web page prints out the presence of Swoole.

Because Swoole runs in CLI mode, its functionality is very limited in traditional FPM mode.

In FPM mode, all main features such as asynchronous/coroutine **cannot be used**, and 99.999% of people cannot get what they want in FPM mode, yet they wonder why there is no extension information in FPM mode.

**First, make sure you truly understand the operating mode of Swoole before continuing to investigate installation issues!**


### Reasons

After compiling and installing Swoole, it appears in the `php-fpm/apache` `phpinfo` page, but not in the command line `php -m`. This may be because `cli/php-fpm/apache` uses different php.ini configurations.


### Solutions

1. Confirm the location of php.ini

Execute `php -i | grep php.ini` or `php --ini` in the `cli` command line to find the absolute path of php.ini.

For `php-fpm/apache`, find the absolute path of php.ini by viewing the `phpinfo` page.

2. Check if the corresponding php.ini has `extension=swoole.so`

```shell
cat /path/to/php.ini | grep swoole.so
```


## pcre.h: No such file or directory

Compile error when installing Swoole extension

```bash
fatal error: pcre.h: No such file or directory
```

The reason is the lack of pcre, and you need to install libpcre.


### Ubuntu/Debian

```shell
sudo apt-get install libpcre3 libpcre3-dev
```

### CentOS/Red Hat

```shell
sudo yum install pcre-devel
```

### Other Linux

Download the source package from the [PCRE official website](http://www.pcre.org/), compile and install the `pcre` library.

After installing the `PCRE` library, you need to recompile and install `swoole`, and then use `php --ri swoole` to check if the `swoole` extension information includes `pcre => enabled`.


## '__builtin_saddl_overflow' was not declared in this scope

```
error: '__builtin_saddl_overflow' was not declared in this scope
  if (UNEXPECTED(__builtin_saddl_overflow(Z_LVAL_P(op1), 1, &lresult))) {

note: in definition of macro 'UNEXPECTED'
 # define UNEXPECTED(condition) __builtin_expect(!!(condition), 0)
```

This is a known issue. The problem is that the default gcc on CentOS lacks the necessary definitions, and even after upgrading gcc, PECL may still find the old compiler.

To install the driver, you must first upgrade gcc by installing the devtoolset collection, as shown below:

```shell
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```


## fatal error: 'openssl/ssl.h' file not found

Please add the [--with-openssl-dir](/environment?id=通用参数) parameter to the compilation to specify the path to the openssl library

!> When installing Swoole using [pecl](/environment?id=pecl), if you want to enable openssl support, you can also add the [--with-openssl-dir](/environment?id=通用参数) parameter, like this: `enable openssl support? [no] : yes --with-openssl-dir=/opt/openssl/`


## make or make install cannot be executed or compilation error

NOTICE: PHP message: PHP Warning:  PHP Startup: swoole: Unable to initialize module  
Module compiled with module API=20090626  
PHP    compiled with module API=20121212  
These options need to match  
in Unknown on line 0  
   
The PHP version and the `phpize` and `php-config` used during compilation do not match. You need to use absolute paths for compilation and to execute PHP.

```shell
/usr/local/php-5.4.17/bin/phpize
./configure --with-php-config=/usr/local/php-5.4.17/bin/php-config

/usr/local/php-5.4.17/bin/php server.php
```


## Installing xdebug

```shell
git clone git@github.com:swoole/sdebug.git -b sdebug_2_9 --depth=1

cd sdebug

phpize
./configure
make clean
make
make install

# If your phpize, php-config, and other configuration files are all default, then you can directly execute
./rebuild.sh
```

Modify the php.ini to load the extension, adding the following information

```ini
zend_extension=xdebug.so

xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=localhost
xdebug.remote_port=8000
xdebug.idekey="xdebug"
```

Check if it is loaded successfully

```shell
php --ri sdebug
```


## configure: error: C preprocessor "/lib/cpp" fails sanity check

If an error occurs during installation

```shell
configure: error: C preprocessor "/lib/cpp" fails sanity check
```

It indicates a lack of necessary dependency libraries. You can install them using the following commands

```shell
yum install glibc-headers
yum install gcc-c++
```


## PHP7.4.11+ compiling a new version of Swoole reports an error asm goto :id=asm_goto

When using PHP7.4.11+ on MacOS to compile a new version of Swoole, the following error is found:

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

Solution: Modify the `/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h` source code, noting that you should modify it to your corresponding header file path;

Change `ZEND_USE_ASM_ARITHMETIC` to a constant of `0`, that is, retain the content of the `else` clause in the following code

```c
#if defined(HAVE_ASM_GOTO) && !__has_feature(memory_sanitizer)
# define ZEND_USE_ASM_ARITHMETIC 1
#else
# define ZEND_USE_ASM_ARITHMETIC 0
#endif
```


## fatal error: curl/curl.h: No such file or directory :id=libcurl

When the `--enable-swoole-curl` option is enabled, compiling the Swoole extension encounters an error

```bash
fatal error: curl/curl.h: No such file or directory
```

The reason is a lack of curl dependency, and you need to install libcurl.


### Ubuntu/Debian

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

## erreur fatale : ares.h : Aucun fichier ou dossier de ce type : id=libcares

Lorsqu'on active l'option `--enable-cares`, la compilation de l'extension Swoole rencontre une

```bash
fatal error: ares.h: No such file or directory
```

La raison en est le manque de dépendance c-ares, il est nécessaire d'installer libcares

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
