# 서버(비동기 스타일)

`TCP`, `UDP`, [unixSocket](/learn?id= 什么是IPC)의 3가지 소켓 유형을 지원하는 편리한 비동기 서버 프로그램을 쉽게 만들 수 있습니다. `IPv4`와 `IPv6`을 지원하며, `SSL/TLS` 일방/양방향 인증서의 터널 암호화도 지원합니다. 사용자는 기본적인 구현 세부 사항에 신경 쓸 필요가 없으며, 네트워크 [이벤트](/server/events)의 콜백 함수를 설정하기만 하면 됩니다. 예시로는 [빠른 시작](/start/start_tcp_server)를 참고하세요.

!> 단지 `Server` 측은 비동기 스타일(즉 모든 이벤트에 대한 콜백 함수를 설정해야 함)이지만, 동시에 코루틴을 지원합니다. [enable_coroutine](/server/setting?id=enable_coroutine)를 활성화하면 코루틴을 지원합니다(기본적으로 활성화되어 있습니다). [코루틴](/coroutine) 하의 모든 비즈니스 코드는 동기식으로 작성됩니다.

더 알아보기:

[서버의 세 가지 운영 모드 소개](/learn?id=server的三种运行模式介绍 ':target=_blank')  
[Process, ProcessPool, UserProcess의 차이점은 무엇인가요](/learn?id=process-diff ':target=_blank')  
[Master 프로세스, Reactor 스레드, Worker 프로세스, Task 프로세스, Manager 프로세스의 차이점과 연관성은 무엇인가요](/learn?id=diff-process ':target=_blank')  


### 운영 흐름도 <!-- {docsify-ignore} --> 

![running_process](https://wiki.swoole.com/_images/server/running_process.png ':size=800xauto')

### 프로세스/스레드 구조도 <!-- {docsify-ignore} --> 

![process_structure](https://wiki.swoole.com/_images/server/process_structure.png ':size=800xauto')

![process_structure_2](https://wiki.swoole.com/_images/server/process_structure_2.png)
