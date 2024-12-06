# 확장 충돌

일부 트레이스 디버그를 위한 `PHP` 확장이 전역 변수를 대량으로 사용하기 때문에 `Swoole` 코루outine이 충돌할 수 있습니다. 다음 관련 확장을 비활성화해 주십시오:

* phptrace
* aop
* molten
* xhprof
* phalcon(Swoole 코루outine은 phalcon 프레임워크에서 실행할 수 없습니다)

## Xdebug 지원
5.1 버전부터는 `xdebug` 확장을 사용하여 `Swoole` 프로그램을 디버그할 수 있습니다. 커맨드라인 매개변수나 `php.ini` 수정으로 활성화할 수 있습니다.

```ini
swoole.enable_fiber_mock=On
```

또는 

```shell
php -d swoole.enable_fiber_mock=On your_file.php
```
