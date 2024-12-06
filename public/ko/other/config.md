# ini 설정

설정 | 기본값 | 역할
---|---|---
swoole.enable_coroutine | On | `On`, `Off` 내장 코루틴 스위치, [상세](/server/setting?id=enable_coroutine)。
swoole.display_errors | On | `Swoole` 오류 정보를 켜기/끄기
swoole.unixsock_buffer_size | 8M | 프로세스 간 통신의 `Socket` 버퍼 크기를 설정하며, 이는 [socket_buffer_size](/server/setting?id=socket_buffer_size)와 동일합니다.
swoole.use_shortname | On | 짧은 별명을 사용하는지 여부, [상세](/other/alias?id=코루틴 짧은 이름)。
swoole.enable_preemptive_scheduler | Off | 일부 코루틴이 CPU 시간을 너무 오래(10ms의 CPU 시간) 차지하여 다른 코루틴이 [스케줄러](/coroutine?id=코루틴 스케줄러)에 도달하지 못하는 것을 방지할 수 있습니다. [예제](https://github.com/swoole/swoole-src/tree/master/tests/swoole_coroutine_scheduler/preemptive)。
swoole.enable_library | On | 확장 내장된 library를 켜기/끄기
