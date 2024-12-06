# 리눅스 신호 목록

## 전체 대조표

| 신호      | 값     | 기본 행동 | 의미(신호 발신 이유)                  |
| --------- | -------- | -------- | --------------------------------------- |
| SIGHUP    | 1        | Term     | 터미널의 종료 또는 프로세스 사망      |
| SIGINT    | 2        | Term     | 키보드에서 온 중단 신호                |
| SIGQUIT   | 3        | Core     | 키보드에서 온 종료 신호                |
| SIGILL    | 4        | Core     | 불법 명령어                              |
| SIGABRT   | 6        | Core     | abort에서 온 예외 신호                 |
| SIGFPE    | 8        | Core     | 부동소수 예외                            |
| SIGKILL   | 9        | Term     | 살해                                    |
| SIGSEGV   | 11       | Core     | 세그먼트 잘못 사용(메모리 참조 무효)      |
| SIGPIPE   | 13       | Term     | 파이프가损坏: 읽는 프로세스가 없는 파이프에 데이터 쓰기 |
| SIGALRM   | 14       | Term     | alarm에서 온 타이머 만료 신호             |
| SIGTERM   | 15       | Term     | 종료                                    |
| SIGUSR1   | 30,10,16 | Term     | 사용자 정의 신호 1                        |
| SIGUSR2   | 31,12,17 | Term     | 사용자 정의 신호 2                        |
| SIGCHLD   | 20,17,18 | Ign      | 자식 프로세스 정지 또는 종료              |
| SIGCONT   | 19,18,25 | Cont     | 정지했다면 계속 실행                      |
| SIGSTOP   | 17,19,23 | Stop     | 터미널이 아닌 정지 신호                  |
| SIGTSTP   | 18,20,24 | Stop     | 터미널에서 온 정지 신호                  |
| SIGTTIN   | 21,21,26 | Stop     | 백그라운드 프로세스가 터미널을 읽는 중    |
| SIGTTOU   | 22,22,27 | Stop     | 백그라운드 프로세스가 터미널을 쓰는 중    |
|           |          |          |                                         |
| SIGBUS    | 10,7,10  | Core     | 버스 오류(메모리 액세스 오류)            |
| SIGPOLL   |          | Term     | Pollable 이벤트 발생(Sys V), SIGIO와 동의어 |
| SIGPROF   | 27,27,29 | Term     | 통계 분포도 시간 초과                   |
| SIGSYS    | 12,-,12  | Core     | 불법 시스템 호출(SVr4)                  |
| SIGTRAP   | 5        | Core     | 트레이스/브레이크 포인트 자취             |
| SIGURG    | 16,23,21 | Ign      | 소켓 긴급 신호(4.2BSD)                 |
| SIGVTALRM | 26,26,28 | Term     | 가상 타이머 만료(4.2BSD)                |
| SIGXCPU   | 24,24,30 | Core     | CPU 제한 초과(4.2BSD)                   |
| SIGXFSZ   | 25,25,31 | Core     | 파일 길이 제한 초과(4.2BSD)              |
|           |          |          |                                         |
| SIGIOT    | 6        | Core     | IOT 자취, SIGABRT와 동의어               |
| SIGEMT    | 7,-,7    |          | Term                                    |
| SIGSTKFLT | -,16,-   | Term     | 코어러 스택 오류(사용하지 않음)         |
| SIGIO     | 23,29,22 | Term     | 설명자가 I/O 작업을 할 수 있음            |
| SIGCLD    | -,-,18   | Ign      | SIGCHLD와 동의어                         |
| SIGPWR    | 29,30,19 | Term     | 전원 장애/재시작                        |
| SIGINFO   | 29,-,-   |          | SIGPWR와 동의어                          |
| SIGLOST   | -,-,-    | Term     | 파일 잠금 상실                              |
| SIGWINCH  | 28,28,20 | Ign      | 터미널 창 크기 변경(4.3BSD, Sun)         |
| SIGUNUSED | -,31,-   | Term     | 미사용 신호(SIGSYS가 될 예정)            |

## 신뢰할 수 없는 신호

| 이름      | 설명                        |
| --------- | --------------------------- |
| SIGHUP    | 연결 끊김                    |
| SIGINT    | 터미널 중단 기호                  |
| SIGQUIT   | 터미널 종료 기호                  |
| SIGILL    | 불법 하드웨어 명령어                |
| SIGTRAP   | 하드웨어 장애                    |
| SIGABRT   | 예외 종료(abort)             |
| SIGBUS    | 하드웨어 장애                    |
| SIGFPE    | 수학적 예외                    |
| SIGKILL   | 종료                        |
| SIGUSR1   | 사용자 정의 신호                |
| SIGUSR2   | 사용자 정의 신호                |
| SIGSEGV   | 무효한 메모리 참조                |
| SIGPIPE   | 읽는 프로세스가 없는 파이프에 쓰기          |
| SIGALRM   | 타이머 초과(alarm)           |
| SIGTERM   | 종료                        |
| SIGCHLD   | 자식 프로세스 상태 변경              |
| SIGCONT   | 정지 중인 프로세스를 계속 실행              |
| SIGSTOP   | 정지                        |
| SIGTSTP   | 터미널 정지 기호                  |
| SIGTTIN   | 백그라운드 프로세스가 터미널을 읽는 중    |
| SIGTTOU   | 백그라운드 프로세스가 터미널을 쓰는 중    |
| SIGURG    | 긴급 상황(소켓)            |
| SIGXCPU   | CPU 제한 초과(setrlimit)    |
| SIGXFSZ   | 파일 길이 제한 초과(setrlimit) |
| SIGVTALRM | 가상 시간 알람(setitimer)     |
| SIGPROF   | 개요 시간 초과(setitimer)     |
| SIGWINCH  | 터미널 창 크기 변경            |
| SIGIO     | 비동기 I/O                    |
| SIGPWR    | 전원 실패/재시작             |
| SIGSYS    | 무효한 시스템 호출                |

## 신뢰할 수 있는 신호

| 이름        | 사용자 정의 |
| ----------- | ---------- |
| SIGRTMIN    |            |
| SIGRTMIN+1  |            |
| SIGRTMIN+2  |            |
| SIGRTMIN+3  |            |
| SIGRTMIN+4  |            |
| SIGRTMIN+5  |            |
| SIGRTMIN+6  |            |
| SIGRTMIN+7  |            |
| SIGRTMIN+8  |            |
| SIGRTMIN+9  |            |
| SIGRTMIN+10 |            |
| SIGRTMIN+11 |            |
| SIGRTMIN+12 |            |
| SIGRTMIN+13 |            |
| SIGRTMIN+14 |            |
| SIGRTMIN+15 |            |
| SIGRTMAX-14 |            |
| SIGRTMAX-13 |            |
| SIGRTMAX-12 |            |
| SIGRTMAX-11 |            |
| SIGRTMAX-10 |            |
| SIGRTMAX-9  |            |
| SIGRTMAX-8  |            |
| SIGRTMAX-7  |            |
| SIGRTMAX-6  |            |
| SIGRTMAX-5  |            |
| SIGRTMAX-4  |            |
| SIGRTMAX-3  |            |
| SIGRTMAX-2  |            |
| SIGRTMAX-1  |            |
| SIGRTMAX    |            |
