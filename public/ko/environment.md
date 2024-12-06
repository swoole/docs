# Swoole 설치

`Swoole` 확장은 `PHP` 표준 확장으로 구축되었습니다. `phpize`를 사용하여 컴파일 검출 스크립트를 생성하고, `./configure`를 사용하여 컴파일 구성 검사를 수행하며, `make`를 사용하여 컴파일을 하고, `make install`를 사용하여 설치합니다.

* 특별한 요구가 없는 경우, 최신 [Swoole](https://github.com/swoole/swoole-src/releases/) 버전을 컴파일하여 설치하는 것을 권장합니다.
* 현재 사용자가 `root`가 아닐 경우, `PHP` 설치 디렉리의 쓰기 권한이 없을 수 있으며, 설치 시 `sudo` 또는 `su`가 필요할 수 있습니다.
* `git` 브랜치에서 직접 `git pull`하여 코드를 업데이트하려면, 재컴파일하기 전에 반드시 `make clean`를 실행해야 합니다.
* 지원되는 운영 체제는 `Linux`(2.3.32 이상 커널), `FreeBSD`, `MacOS`입니다.
* 저가용도 Linux 시스템(예: `CentOS 6`)은 `RedHat`이 제공하는 `devtools`로 컴파일할 수 있으며, [참조 문서](https://blog.csdn.net/ppdouble/article/details/52894271)를 참고하세요.
* `Windows` 플랫폼에서는 `WSL(Windows Subsystem for Linux)` 또는 `CygWin`을 사용할 수 있습니다.
* 일부 확장은 `Swoole` 확장과 호환되지 않으며, [확장 충돌](/getting_started/extension)을 참고하세요.


## 설치 준비

설치 전에 시스템에서 다음 소프트웨어를 이미 설치해야 합니다.



- `4.8` 버전은 `PHP-7.2` 이상의 버전을 필요로 합니다.

- `5.0` 버전은 `PHP-8.0` 이상의 버전을 필요로 합니다.

- `6.0` 버전은 `PHP-8.1` 이상의 버전을 필요로 합니다.

- `gcc-4.8` 이상의 버전을 필요로 합니다.

- `make`

- `autoconf`


## 간단 설치

> 1. swoole 소스 코드 다운로드

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2. 소스에서 컴파일하여 설치

소스 코드 팩을 다운로드한 후, 터미널에서 소스 디렉토리로 이동하여 다음 명령을 실행하여 컴파일하고 설치합니다.

!> ubuntu에서 phpize 명령이 설치되지 않은 경우: `sudo apt-get install php-dev`로 phpize를 설치합니다.

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3. 확장을 활성화

시스템에 컴파일하여 성공적으로 설치한 후, `php.ini` 파일에 한 줄의 `extension=swoole.so`를 추가하여 Swoole 확장을 활성화합니다.


## 고급 전체 컴파일 예제

!> Swoole를 처음 접하는 개발자는 상단의 간단한 컴파일을 시도하고, 더 필요한 경우에는 구체적인 요구 사항과 버전을 기준으로 다음 예제의 컴파일 매개변수를 조정할 수 있습니다. [컴파일 옵션 참조](/environment?id=compilation_options)

다음 명령은 `master` 브랜치의 소스 코드를 다운로드하고 컴파일하며, 모든 의존성을 이미 설치해야 합니다. 그렇지 않으면 각종 의존성 오류를 만나게 됩니다.

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

> 주의: PECL 출시 시간은 GitHub 출시 시간보다 늦습니다.

Swoole 프로젝트는 PHP 공식 확장 라이브러리에 포함되어 있으며, 수동으로 다운로드하여 컴파일하는 것 외에도, PHP 공식이 제공하는 `pecl` 명령을 사용하여 한 번에 설치할 수 있습니다.

```shell
pecl install swoole
```

PECL를 사용하여 Swoole를 설치할 때, 설치 과정에서 특정 기능을 활성화할지 여부를 묻습니다. 이는 실행 전에 제공할 수도 있습니다. 예를 들어:

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#또는
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```


## swoole를 php.ini에 추가

마지막으로, 컴파일 및 설치가 성공한 후, `php.ini`을 수정하여 다음을 추가합니다.

```ini
extension=swoole.so
```

`php -m`를 사용하여 `swoole.so`가 성공적으로 로딩되었는지 확인합니다. 만약 로딩되지 않았다면 `php.ini`의 경로가 잘못된 것입니다.  
`php --ini`를 사용하여 `php.ini`의 절대 경로를 위치시킬 수 있으며, `Loaded Configuration File` 항목은 로딩된 php.ini 파일을 나타냅니다. 만약 값이 `none`인 경우, 아무런 php.ini 파일도 로딩되지 않았음을 의미하며, 스스로 생성해야 합니다.

!> PHP 버전 지원은 PHP 공식 유지 버전과 일치합니다. 자세한 내용은 [PHP 버전 지원 시간표](http://php.net/supported-versions.php)를 참고하세요.


## 기타 플랫폼 컴파일

ARM 플랫폼(레이어드 피스 아티팩트)

* `GCC` 크로스 컴파일 사용
* Swoole를 컴파일할 때, `-O2` 컴파일 매개변수를 수동으로 제거해야 합니다.

MIPS 플랫폼(OpenWrt 라우터)

* `GCC` 크로스 컴파일 사용

Windows WSL

`Windows 10` 시스템은 `Linux` 서브시스템 지원을 추가했으며, `BashOnWindows` 환경에서도 Swoole를 사용할 수 있습니다. 설치 명령

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> WSL 환경에서는 `daemonize` 옵션을 반드시 비활성화해야 합니다.  
`17101` 미만의 WSL에서, 소스 설치 후 configure를 실행해야 합니다. 그리고 config.h를 수정하여 HAVE_SIGNALFD를 비활성화해야 합니다.


## Docker 공식 이미지



- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)  
- dockerhub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)


## 컴파일 옵션

이곳은 `./configure` 컴파일 구성의 추가 매개변수로, 특정 기능을 활성화하는 데 사용됩니다.


### 일반 매개변수

#### --enable-openssl

`SSL` 지원을 활성화합니다.

> 운영 체제가 제공하는 `libssl.so` 동적 연결 라이브러리를 사용합니다.

#### --with-openssl-dir

`SSL` 지원을 활성화하고 `openssl` 라이브러리의 경로를 지정합니다. 경로 매개변수를 따라야 합니다. 예: `--with-openssl-dir=/opt/openssl/`

#### --enable-http2

`HTTP2` 지원을 활성화합니다.

> `nghttp2` 라이브러리를 의존합니다. `V4.3.0` 버전 이후에는 의존 설치가 필요 없으며, 내장되어 있지만 여전히 이 컴파일 매개변수를 추가하여 `http2` 지원을 활성화해야 합니다. `Swoole5`는 기본적으로 이 매개변수를 활성화합니다.

#### --enable-swoole-json

[swoole_substr_json_decode](/functions?id=swoole_substr_json_decode)의 지원을 활성화합니다. `Swoole5`부터 기본적으로 이 매개변수를 활성화합니다.

> `json` 확장을 의존합니다. `v4.5.7` 버전부터 사용할 수 있습니다.

#### --enable-swoole-curl

[SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl)의 지원을 활성화합니다. 이 기능을 활성화하려면 `php`와 `Swoole`이 동일한 `libcurl`의 공유 라이브러리와 헤더 파일을 사용해야 합니다. 그렇지 않으면 예측할 수 없는 문제가 발생할 수 있습니다.

> `v4.6.0` 버전부터 사용할 수 있습니다. 컴파일 시 `curl/curl.h: No such file or directory` 오류가 발생하면 [설치 문제](/question/install?id=libcurl)를 확인하세요.

#### --enable-cares

`c-ares`의 지원을 활성화합니다.

> `c-ares` 라이브러리를 의존합니다. `v4.7.0` 버전부터 사용할 수 있습니다. 컴파일 시 `ares.h: No such file or directory` 오류가 발생하면 [설치 문제](/question/install?id=libcares)를 확인하세요.

#### --with-jemalloc-dir

`jemalloc`의 지원을 활성화합니다.

#### --enable-brotli

`libbrotli` 압축 지원을 활성화합니다.

#### --with-brotli-dir

`libbrotli` 압축 지원을 활성화하고 `libbrotli` 라이브러리의 경로를 지정합니다. 경로 매개변수를 따라야 합니다. 예: `--with-brotli-dir=/opt/brotli/`

#### --enable-swoole-pgsql

`PostgreSQL` 데이터베이스의 코루outine화를 활성화합니다.

> `Swoole5.0` 이전에는 코루outine 클라이언트를 사용하여 `PostgreSQL`에 코루outine화를 진행했으며, `Swoole5.1` 이후에는 코루outine 클라이언트를 사용하는 것 외에도 원본의 `pdo_pgsql`을 사용하여 `PostgreSQL`에 코루outine화할 수 있습니다.

#### --with-swoole-odbc

`pdo_odbc`의 코루outine화를 시작합니다. 이 매개변수를 활성화하면 모든 `odbc` 인터페이스를 지원하는 데이터베이스가 코루outine화됩니다.



>`v5.1.0` 버전 이후 사용 가능하며, unixodbc-dev 의존이 필요합니다.

예시 구성

```
with-swoole-odbc="unixODBC,/usr"
```

#### --with-swoole-oracle

`pdo_oci`의 코루outine화를 활성화합니다. 이 매개변수를 활성화하면 `oracle` 데이터베이스의 INSERT, UPDATE, DELETE, SELECT 작업이 모두 코루outine 작업을 트리거합니다.

>`v5.1.0` 버전 이후 사용 가능합니다.

#### --enable-swoole-sqlite

`pdo_sqlite`의 코루outine화를 활성화합니다. 이 매개변수를 활성화하면 `sqlite` 데이터베이스의 INSERT, UPDATE, DELETE, SELECT 작업이 모두 코루outine 작업을 트리거합니다.

>`v5.1.0` 버전 이후 사용 가능합니다.

#### --enable-swoole-thread

Swoole의 멀티스레드 모드를 활성화합니다. 이 컴파일 옵션을 추가하면 Swoole가 멀티 프로세스 단일 스레드 모델에서 단일 프로세스 멀티스레드 모델로 변경됩니다.

>`v6.0` 버전 이후 사용 가능하며, PHP는 반드시 ZTS 모드여야 합니다.

#### --enable-iouring

이 컴파일 옵션을 추가하면 Swoole의 파일 비동기 처리기가 비동기 스레드에서 `iouring` 모드로 변경됩니다.

>`v6.0` 버전 이후 사용 가능하며, 이 기능을 지원하기 위해서는 `liburing` 의존을 설치해야 합니다. 디스크 성능이 좋을 경우 두 가지 모드 간의 성능 차이가 크지 않지만, I/O 압박이 큰 경우 `iouring` 모드의 성능이 비동기 스레드 모드보다 우수합니다.
### 특별 매개변수

!> **역사적 이유가 없다면 사용하지 않는 것이 좋습니다**

#### --enable-mysqlnd

`mysqlnd` 지원을 활성화합니다. `Coroutine\MySQL::escape` 메서드가 활성화됩니다. 이 매개변수를 활성화하면 PHP에 `mysqlnd` 모듈이 반드시 필요하며, 그렇지 않으면 Swoole가 실행될 수 없습니다.

> `mysqlnd` 확장에 의존

#### --enable-sockets

PHP의 `sockets` 자원을 지원합니다. 이 매개변수를 활성화하면 [Swoole\Event::add](/event?id=add)를 통해 `sockets` 확장이 만든 연결을 Swoole의 [이벤트 루프](/learn?id=무엇이eventloop인지)에 등록할 수 있습니다.  
`Server`와 `Client`의 [getSocket()](/server/methods?id=getsocket) 메서드도 이 컴파일 매개변수에 의존합니다.

> `sockets` 확장에 의존하며, `v4.3.2` 버전 이후 이 매개변수의 역할은 약화되었습니다. 왜냐하면 Swoole가 내장한 [Coroutine\Socket](/coroutine_client/socket)가 대부분의 일을 처리할 수 있기 때문입니다.

### Debug 매개변수

!> **생산 환경에서는 활성화하지 않는 것이 좋습니다**

#### --enable-debug

디버그 모드를 활성화합니다. `gdb`를 사용하여 추적하려면 Swoole를 컴파일할 때 이 매개변수를 추가해야 합니다.

#### --enable-debug-log

코어 DEBUG 로그를 활성화합니다. **(Swoole 버전 >= 4.2.0)**

#### --enable-trace-log

트레이스 로그를 활성화합니다. 이 옵션을 활성화하면 Swoole는 각종 세부 사항의 디버그 로그를 인쇄합니다. 이 로지는 코어 개발 시에만 사용합니다.

#### --enable-swoole-coro-time

코어 실행 시간 계산을 활성화합니다. 이 옵션을 활성화하면 Swoole\Coroutine::getExecuteTime()를 사용하여 코어 실행 시간을 계산할 수 있습니다. 이 시간은 I/O 대기 시간을 포함하지 않습니다.

### PHP 컴파일 매개변수

#### --enable-swoole

Swoole를 PHP에 정적으로 컴파일합니다. 다음의 명령을 통해 `--enable-swoole` 옵션을 사용할 수 있습니다.

```shell
cp -r /home/swoole-src /home/php-src/ext
cd /home/php-src
./buildconf --force
./configure --help | grep swoole
```

!> 이 옵션은 PHP를 컴파일할 때 사용하는 것이지 Swoole를 컴파일할 때는 아닙니다.

## 자주 묻는 질문

* [Swoole 설치 자주 묻는 질문](/question/install)
