# Linux 信號列表

## 完整對照表

| 信號      | 取值     | 默认動作 | 意義（發出信號的原因）                  |
| --------- | -------- | -------- | --------------------------------------- |
| SIGHUP    | 1        | Term     | 終端的掛斷或進程死亡                    |
| SIGINT    | 2        | Term     | 来自鍵盤的中斷信號                      |
| SIGQUIT   | 3        | Core     | 来自鍵盤的離開信號                      |
| SIGILL    | 4        | Core     | 非法指令                                |
| SIGABRT   | 6        | Core     | 来自 abort 的異常信號                   |
| SIGFPE    | 8        | Core     | 浮點例外                                |
| SIGKILL   | 9        | Term     | 杀死                                    |
| SIGSEGV   | 11       | Core     | 段非法錯誤(內存引用無效)                |
| SIGPIPE   | 13       | Term     | 管道損壞：向一個沒有讀進程的管道寫數據  |
| SIGALRM   | 14       | Term     | 来自 alarm 的計時器到时信號             |
| SIGTERM   | 15       | Term     | 終止                                    |
| SIGUSR1   | 30,10,16 | Term     | 用戶自定義信號 1                        |
| SIGUSR2   | 31,12,17 | Term     | 用戶自定義信號 2                        |
| SIGCHLD   | 20,17,18 | Ign      | 子進程停止或終止                        |
| SIGCONT   | 19,18,25 | Cont     | 如果停止，繼續執行                      |
| SIGSTOP   | 17,19,23 | Stop     | 非來自終端的停止信號                    |
| SIGTSTP   | 18,20,24 | Stop     | 来自終端的停止信號                      |
| SIGTTIN   | 21,21,26 | Stop     | 后台進程讀終端                          |
| SIGTTOU   | 22,22,27 | Stop     | 后台進程寫終端                          |
|           |          |          |                                         |
| SIGBUS    | 10,7,10  | Core     | 总线錯誤（內存訪問錯誤）                |
| SIGPOLL   |          | Term     | Pollable 事件發生(Sys V)，與 SIGIO 同義 |
| SIGPROF   | 27,27,29 | Term     | 統計分布圖用計時器到时                  |
| SIGSYS    | 12,-,12  | Core     | 非法系統調用(SVr4)                      |
| SIGTRAP   | 5        | Core     | 跟踪/断点自陷                           |
| SIGURG    | 16,23,21 | Ign      | socket 緊急信號(4.2BSD)                 |
| SIGVTALRM | 26,26,28 | Term     | 虛擬計時器到时(4.2BSD)                  |
| SIGXCPU   | 24,24,30 | Core     | 超過 CPU 時限(4.2BSD)                   |
| SIGXFSZ   | 25,25,31 | Core     | 超過文件長度限制(4.2BSD)                |
|           |          |          |                                         |
| SIGIOT    | 6        | Core     | IOT 自陷，與 SIGABRT 同義               |
| SIGEMT    | 7,-,7    |          | Term                                    |
| SIGSTKFLT | -,16,-   | Term     | 協處理器堆棧錯誤(不使用)                |
| SIGIO     | 23,29,22 | Term     | 描述符上可以進行 I/O 操作               |
| SIGCLD    | -,-,18   | Ign      | 與 SIGCHLD 同義                         |
| SIGPWR    | 29,30,19 | Term     | 電力故障(System V)                      |
| SIGINFO   | 29,-,-   |          | 與 SIGPWR 同義                          |
| SIGLOST   | -,-,-    | Term     | 文件鎖丟失                              |
| SIGWINCH  | 28,28,20 | Ign      | 窗口大小改變(4.3BSD, Sun)               |
| SIGUNUSED | -,31,-   | Term     | 未使用信號(will be SIGSYS)              |

## 不可靠信號

| 名稱      | 說明                        |
| --------- | --------------------------- |
| SIGHUP    | 連接斷開                    |
| SIGINT    | 終端中斷符                  |
| SIGQUIT   | 終端退出符                  |
| SIGILL    | 非法硬體指令                |
| SIGTRAP   | 硬體故障                    |
| SIGABRT   | 異常終止(abort)             |
| SIGBUS    | 硬體故障                    |
| SIGFPE    | 算術異常                    |
| SIGKILL   | 終止                        |
| SIGUSR1   | 用戶定義信號                |
| SIGUSR2   | 用戶定義信號                |
| SIGSEGV   | 無效內存引用                |
| SIGPIPE   | 寫至無讀進程的管道          |
| SIGALRM   | 定時器超時(alarm)           |
| SIGTERM   | 終止                        |
| SIGCHLD   | 子進程狀態改變              |
| SIGCONT   | 使暫停進程繼續              |
| SIGSTOP   | 停止                        |
| SIGTSTP   | 終端停止符                  |
| SIGTTIN   | 后台讀控制 tty              |
| SIGTTOU   | 后台寫向控制 tty            |
| SIGURG    | 緊急情況(套接字)            |
| SIGXCPU   | 超過 CPU 限制(setrlimit)    |
| SIGXFSZ   | 超過文件長度限制(setrlimit) |
| SIGVTALRM | 虛擬時間警報(setitimer)     |
| SIGPROF   | 梗概時間超時(setitimer)     |
| SIGWINCH  | 終端窗口大小改變            |
| SIGIO     | 异步 I/O                    |
| SIGPWR    | 電源失效/重啟動             |
| SIGSYS    | 無效系統調用                |

## 可靠信號

| 名稱        | 用戶自定義 |
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
