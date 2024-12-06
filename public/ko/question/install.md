# 설치 문제


## Swoole 버전 업그레이드

pecl을 이용하여 설치 및 업그레이드가 가능합니다.

```shell
pecl upgrade swoole
```

또는 github/gitee/pecl에서 새로운 버전을 다운로드하여 재 설치编译를 수행할 수 있습니다.

* Swoole 버전을 업그레이드할 경우, 이전 버전을 제거하지 않고 설치하면 최신 버전이 덮어씌워집니다.
* Swoole는 컴파일 설치 후 추가 파일이 없으며, 다른 기계에서 컴파일된 이진 버전의 경우에는 swoole.so만 있을 뿐입니다. 이를 서로 덮어씌우면 버전 전환이 가능합니다.
* git clone으로 코드를 끌고 오면, git pull로 코드를 업데이트한 후에는 반드시 `phpize`, `./configure`, `make clean`, `make install`를 다시 수행해야 합니다.
* 또한 해당 docker를 이용하여 Swoole 버전을 업그레이드할 수도 있습니다.


## phpinfo에서 php -m에서 확인되지 않는 경우

먼저 CLI 모드에서 확인해 보세요. 명령어로 `php --ri swoole`를 입력합니다.

Swoole 확장 정보가 출력된다면 성공적으로 설치했다는 것을 의미합니다!

**99.999%의 사람들이 이 단계에서 성공하면 swoole를 바로 사용할 수 있습니다.**

`php -m`나 `phpinfo` 웹 페이지에서 swoole가 출력되는지 여부는 신경 쓰지 마세요.

왜냐하면 Swoole는 CLI 모드에서 실행되며, 전통적인 fpm 모드에서는 기능이 매우 제한적이기 때문입니다.

fpm 모드에서는 비동기/코루outine 등 주요 기능이 모두 **사용이 불가능하며**, 99.999%의 사람들이 fpm 모드에서 원하는 것을 얻을 수 없지만, 왜 fpm 모드에서 확장 정보가 없는지 고민합니다.

**먼저 Swoole의 운영 모드를 진정으로 이해하고 나서야 설치 정보 문제를 계속 추구하세요!**


### 이유

Swoole를 컴파일 설치한 후, `php-fpm/apache`의 `phpinfo` 페이지에는 있지만, 명령어줄의 `php -m`에서는 나타나지 않는 이유는 `cli/php-fpm/apache`가 다른 php.ini 설정을 사용하기 때문일 수 있습니다.


### 해결책

1. php.ini 위치 확인

`cli` 명령어줄에서 `php -i | grep php.ini` 또는 `php --ini`를 실행하여 php.ini의 절대 경로를 찾습니다.

`php-fpm/apache`의 경우에는 `phpinfo` 페이지에서 php.ini의 절대 경로를 찾습니다.

2. 해당 php.ini에 `extension=swoole.so`가 있는지 확인

```shell
cat /path/to/php.ini | grep swoole.so
```


## pcre.h: No such file or directory

Swoole 확장을 컴파일할 때 발생합니다.

```bash
fatal error: pcre.h: No such file or directory
```

원인은 pcre가 부족함으로, libpcre를 설치해야 합니다.


### ubuntu/debian

```shell
sudo apt-get install libpcre3 libpcre3-dev
```

### centos/redhat

```shell
sudo yum install pcre-devel
```


### 기타 Linux

[PCRE 공식 홈페이지](http://www.pcre.org/)에서 소스 팩을 다운로드하여 pcre 라이브러리를 컴파일 설치합니다.

PCRE 라이브러리가 설치된 후에는 swoole를 다시 컴파일하고, `php --ri swoole`를 통해 swoole 확장의 관련 정보를 확인하여 `pcre => enabled`가 나타나는지 확인합니다.


## '__builtin_saddl_overflow' was not declared in this scope

 ```
error: '__builtin_saddl_overflow' was not declared in this scope
  if (UNEXPECTED(__builtin_saddl_overflow(Z_LVAL_P(op1), 1, &lresult))) {

note: in definition of macro 'UNEXPECTED'
 # define UNEXPECTED(condition) __builtin_expect(!!(condition), 0)
```

이것은 알려진 문제입니다. 문제는 CentOS의 기본 gcc가 필요한 정의가 부족하여, gcc를 업그레이드한 후에도 PECL이 여전히 구编译기를 찾는 것입니다.

드라이버를 설치하려면 먼저 devtoolset 세트를 설치하여 gcc를 업그레이드해야 합니다. 다음과 같이 수행합니다:

```shell
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```


## fatal error: 'openssl/ssl.h' file not found

编译時に [--with-openssl-dir](/environment?id=通用参数) 매개변수를 추가하여 openssl 라이브러리의 경로를 지정하세요.

!> pecl를 이용하여 Swoole를 설치할 때 openssl을 사용하고자 한다면 [--with-openssl-dir](/environment?id=通用参数) 매개변수를 추가하여, 예를 들어: `enable openssl support? [no] : yes --with-openssl-dir=/opt/openssl/`


## make 또는 make install이 실행하거나 컴파일 오류가 발생하는 경우

알림: PHP 메시지: PHP 경고: PHP 시작: swoole: 모듈 초기화가 실패했습니다  
모듈은 module API=20090626으로 컴파일되었습니다  
PHP은 module API=20121212로 컴파일되었습니다  
이러한 옵션은 일치해야 합니다  
미지에서 0행

PHP 버전과 컴파일 시 사용된 `phpize`와 `php-config`가 일치하지 않으므로, 컴파일은 절대 경로를 사용하고 PHP는 절대 경로를 사용해야 합니다.

```shell
/usr/local/php-5.4.17/bin/phpize
./configure --with-php-config=/usr/local/php-5.4.17/bin/php-config

/usr/local/php-5.4.17/bin/php server.php
```


## xdebug 설치

```shell
git clone git@github.com:swoole/sdebug.git -b sdebug_2_9 --depth=1

cd sdebug

phpize
./configure
make clean
make
make install

# 만약 당신의 phpize, php-config 등의 구성 파일이 모두 기본값이라면, 다음을 직접 실행할 수 있습니다.
./rebuild.sh
```

php.ini에서 확장을 로딩하도록 변경하고 다음 정보를 추가합니다.

```ini
zend_extension=xdebug.so

xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=localhost
xdebug.remote_port=8000
xdebug.idekey="xdebug"
```

로딩 여부를 확인합니다.

```shell
php --ri sdebug
```


## configure: error: C preprocessor "/lib/cpp" fails sanity check

설치 중에 발생하는 오류입니다.

```shell
configure: error: C preprocessor "/lib/cpp" fails sanity check
```

필요한 의존 라이브러리가 부족하다는 것을 의미하며, 다음 명령어를 사용하여 설치할 수 있습니다.

```shell
yum install glibc-headers
yum install gcc-c++
```


## PHP7.4.11+로 새로운 버전의 Swoole를 컴파일할 때 오류 발생: asm goto :id=asm_goto

MacOS에서 PHP7.4.11+로 새로운 버전의 Swoole를 컴파일할 때 다음과 같은 오류가 발생합니다:

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

해결 방법: `/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h` 소스 코드를 수정하여 자신의 해당 헤드 파일 경로로 변경합니다;

`ZEND_USE_ASM_ARITHMETIC`를 항상 `0`로 유지하도록 변경합니다. 즉, 다음 코드의 `else` 내용을 유지합니다.

```c
#if defined(HAVE_ASM_GOTO) && !__has_feature(memory_sanitizer)
# define ZEND_USE_ASM_ARITHMETIC 1
#else
# define ZEND_USE_ASM_ARITHMETIC 0
#endif
```


## fatal error: curl/curl.h: No such file or directory :id=libcurl

'swoole-curl' 옵션을 활성화하면, Swoole 확장을 컴파일할 때 다음과 같은 오류가 발생합니다.

```bash
fatal error: curl/curl.h: No such file or directory
```

원인은 curl 의존성이 부족하기 때문입니다. libcurl를 설치해야 합니다.


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

'swoole' 확장을编译할 때 '--enable-cares' 옵션을 선택하면 다음과 같은 fatal error가 발생합니다.

```bash
fatal error: ares.h: No such file or directory
```

이 문제는 c-ares 의존성이 부족하기 때문입니다. libcares를 설치해야 합니다.

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
