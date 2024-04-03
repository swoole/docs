# Installation issue
## Upgrading Swoole Version

You can use pecl for installation and upgrading.

```shell
pecl upgrade swoole
```

Alternatively, you can directly download a new version from GitHub/Gitee/Pecl and reinstall by compiling.

* When updating the Swoole version, there is no need to uninstall or remove the old version. The installation process will overwrite the old version.
* After compiling Swoole, there are no additional files, only a `swoole.so`. If you have a pre-compiled binary version from another machine, you can switch versions by simply replacing `swoole.so`.
* If you have cloned the code from a repository like git, after executing `git pull` to update the code, make sure to run `phpize`, `./configure`, `make clean`, and `make install` again.
* You can also use the corresponding Docker image to upgrade the Swoole version.
## There is in phpinfo but not in php -m

First, confirm if it exists in CLI mode, run the command `php --ri swoole`

If the output shows Swoole extension information, then your installation was successful!

**99.999% of people can start using Swoole directly after this step**

You don't need to worry about whether Swoole shows up in `php -m` or `phpinfo` web page output.

Because Swoole runs in CLI mode, its functionality is very limited in traditional FPM mode.

In FPM mode, any main features like asynchronous operations or coroutines **cannot be used**. 99.999% of people cannot achieve the desired results in FPM mode but get caught up in why the extension information is not showing.

**First, make sure you truly understand Swoole's running mode before investigating installation issues further!**
### Reason

After compiling and installing Swoole, it appears in the `phpinfo` page of `php-fpm/apache`, but not in the `php -m` command line. The reason may be that `cli/php-fpm/apache` is using different php.ini configurations.
### Solution

1. Confirm the location of php.ini

Run `php -i | grep php.ini` or `php --ini` in the `cli` command line to find the absolute path of php.ini.

For `php-fpm/apache`, view the `phpinfo` page to find the absolute path of php.ini.

2. Check if there is `extension=swoole.so` in the corresponding php.ini file

```shell
cat /path/to/php.ini | grep swoole.so
```
## pcre.h: No such file or directory

When compiling the Swoole extension, the following error occurs:

```bash
fatal error: pcre.h: No such file or directory
```

The reason is that the 'pcre' is missing, and you need to install `libpcre`.
### ubuntu/debian

```shell
sudo apt-get install libpcre3 libpcre3-dev
```
### centos/redhat

```shell
sudo yum install pcre-devel
```
### Other Linux

Download the source code package from the [PCRE official website](http://www.pcre.org/) and compile and install the `pcre` library.

After installing the `PCRE` library, you need to recompile and install `swoole`, then use `php --ri swoole` to check if there is `pcre => enabled` information in the `swoole` extension related information.
## '__builtin_saddl_overflow' was not declared in this scope

 ```
error: '__builtin_saddl_overflow' was not declared in this scope
  if (UNEXPECTED(__builtin_saddl_overflow(Z_LVAL_P(op1), 1, &lresult))) {

note: in definition of macro 'UNEXPECTED'
 # define UNEXPECTED(condition) __builtin_expect(!!(condition), 0)
```

This is a known issue. The problem is that the default gcc on CentOS lacks the necessary definition, and even after upgrading gcc, PECL will still find the old compiler.

To resolve this issue and install the driver, you must first upgrade gcc by installing the `devtoolset` collection as shown below:

```shell
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```
## fatal error: 'openssl/ssl.h' file not found

Please specify the path to the openssl library by adding the [--with-openssl-dir](/environment?id=common-parameters) parameter during compilation.

!> When installing Swoole using [pecl](/environment?id=pecl), if you want to enable openssl, you can also add the [--with-openssl-dir](/environment?id=common-parameters) parameter, like this: `enable openssl support? [no] : yes --with-openssl-dir=/opt/openssl/`
### Unable to execute `make` or `make install`

NOTICE: PHP message: PHP Warning: PHP Startup: swoole: Unable to initialize module  
Module compiled with module API=20090626  
PHP compiled with module API=20121212  
These options need to match  
in Unknown on line 0  

The PHP version does not match the versions used when compiling with `phpize` and `php-config`. You need to compile using absolute paths and execute PHP using absolute paths.

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

# If your phpize, php-config, and other configuration files are default, you can directly execute
./rebuild.sh
```

Edit php.ini to load the extension with the following information

```ini
zend_extension=xdebug.so

xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=localhost
xdebug.remote_port=8000
xdebug.idekey="xdebug"
```

Check if the extension is successfully loaded

```shell
php --ri sdebug
```
## configure: error: C preprocessor "/lib/cpp" fails sanity check

If you encounter the following error during installation:

```shell
configure: error: C preprocessor "/lib/cpp" fails sanity check
```

it means that necessary dependencies are missing. You can install them using the following commands:

```shell
yum install glibc-headers
yum install gcc-c++
```
## Error when Compiling New Version of Swoole with PHP 7.4.11+ on macOS

When compiling a new version of Swoole with PHP 7.4.11+ on MacOS, you may encounter an error similar to the following:

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

Solution: Modify the source code in `/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h` (make sure to adjust to your own corresponding header file path).

Change `ZEND_USE_ASM_ARITHMETIC` to always be `0`, essentially keeping the content in the `else` part intact:

```c
#if defined(HAVE_ASM_GOTO) && !__has_feature(memory_sanitizer)
```
This appears to be a line of code written in the C programming language.
This code is a preprocessor directive in C language that defines `ZEND_USE_ASM_ARITHMETIC` as `0` if it is not already defined.
## fatal error: curl/curl.h: No such file or directory :id=libcurl

When you open the `--enable-swoole-curl` option, compiling the Swoole extension produces the following error:

```bash
fatal error: curl/curl.h: No such file or directory
```

This is because the curl dependency is missing, and you need to install libcurl.
### ubuntu/debian

```shell
sudo apt-get install libcurl4-openssl-dev
```
### centos/redhat

```shell
sudo yum install libcurl-devel
`````
### alpine

```shell
apk add curl-dev
`````
## fatal error: ares.h: No such file or directory :id=libcares

When the `--enable-cares` option is enabled, compiling the Swoole extension gives the error:

```bash
fatal error: ares.h: No such file or directory
```

The reason is a missing c-ares dependency, which requires installing libcares.
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
