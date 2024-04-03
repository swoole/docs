# List of Linux signals
## Complete comparison table

| Signal    | Value    | Default Action | Meaning (reason for issuing the signal) |
| --------- | -------- | --------------- | --------------------------------------- |
| SIGHUP    | 1        | Term            | Hangup or process death from terminal    |
| SIGINT    | 2        | Term            | Interrupt signal from keyboard          |
| SIGQUIT   | 3        | Core            | Quit signal from keyboard               |
| SIGILL    | 4        | Core            | Illegal instruction                    |
| SIGABRT   | 6        | Core            | Abnormal signal from abort              |
| SIGFPE    | 8        | Core            | Floating-point exception                |
| SIGKILL   | 9        | Term            | Kill signal                            |
| SIGSEGV   | 11       | Core            | Invalid memory reference (segmentation fault) |
| SIGPIPE   | 13       | Term            | Broken pipe: writing to a pipe with no readers |
| SIGALRM   | 14       | Term            | Timer signal from alarm                |
| SIGTERM   | 15       | Term            | Termination signal                     |
| SIGUSR1   | 30,10,16 | Term            | User-defined signal 1                  |
| SIGUSR2   | 31,12,17 | Term            | User-defined signal 2                  |
| SIGCHLD   | 20,17,18 | Ign             | Child process has stopped or terminated |
| SIGCONT   | 19,18,25 | Cont            | Continue if stopped                    |
| SIGSTOP   | 17,19,23 | Stop            | Stop signal not from terminal           |
| SIGTSTP   | 18,20,24 | Stop            | Stop signal from terminal               |
| SIGTTIN   | 21,21,26 | Stop            | Background process attempt to read from terminal |
| SIGTTOU   | 22,22,27 | Stop            | Background process attempt to write to terminal |
|           |          |                 |                                         |
| SIGBUS    | 10,7,10  | Core            | Bus error (memory access error)        |
| SIGPOLL   |          | Term            | Pollable event occurred (Sys V), synonymous with SIGIO |
| SIGPROF   | 27,27,29 | Term            | Profiling timer expired                |
| SIGSYS    | 12,-,12  | Core            | Invalid system call (SVr4)             |
| SIGTRAP   | 5        | Core            | Trace/breakpoint trap                  |
| SIGURG    | 16,23,21 | Ign             | Socket urgent signal (4.2BSD)          |
| SIGVTALRM | 26,26,28 | Term            | Virtual timer expired (4.2BSD)         |
| SIGXCPU   | 24,24,30 | Core            | CPU time limit exceeded (4.2BSD)       |
| SIGXFSZ   | 25,25,31 | Core            | File size limit exceeded (4.2BSD)      |
|           |          |                 |                                         |
| SIGIOT    | 6        | Core            | IOT trap, synonymous with SIGABRT      |
| SIGEMT    | 7,-,7    |                 | Term                                    |
| SIGSTKFLT | -,16,-   | Term            | Stack fault on coprocessor (not used)  |
| SIGIO     | 23,29,22 | Term            | I/O possible on descriptor             |
| SIGCLD    | -,-,18   | Ign             | Synonymous with SIGCHLD                |
| SIGPWR    | 29,30,19 | Term            | Power failure (System V)               |
| SIGINFO   | 29,-,-   |                 | Synonymous with SIGPWR                 |
| SIGLOST   | -,-,-    | Term            | File lock lost                         |
| SIGWINCH  | 28,28,20 | Ign             | Window size change (4.3BSD, Sun)       |
| SIGUNUSED | -,31,-   | Term            | Unused signal (will be SIGSYS)         |
## Unreliable Signals

| Name      | Description                   |
| --------- | ----------------------------- |
| SIGHUP    | Hang up (disconnection)       |
| SIGINT    | Terminal interrupt signal     |
| SIGQUIT   | Terminal quit signal          |
| SIGILL    | Illegal hardware instruction  |
| SIGTRAP   | Trace/breakpoint trap         |
| SIGABRT   | Abnormal termination (abort)  |
| SIGBUS    | Bus error                     |
| SIGFPE    | Floating point exception      |
| SIGKILL   | Kill signal                   |
| SIGUSR1   | User-defined signal 1         |
| SIGUSR2   | User-defined signal 2         |
| SIGSEGV   | Segmentation fault            |
| SIGPIPE   | Broken pipe                   |
| SIGALRM   | Alarm clock signal            |
| SIGTERM   | Termination signal            |
| SIGCHLD   | Child process status changed  |
| SIGCONT   | Continue executing, if stopped|
| SIGSTOP   | Stop the process              |
| SIGTSTP   | Terminal stop signal          |
| SIGTTIN   | Background process attempting read |
| SIGTTOU   | Background process attempting write |
| SIGURG    | Urgent socket condition       |
| SIGXCPU   | CPU time limit exceeded       |
| SIGXFSZ   | File size limit exceeded      |
| SIGVTALRM | Virtual timer expired         |
| SIGPROF   | Profiling timer expired       |
| SIGWINCH  | Window size change            |
| SIGIO     | I/O is possible on a descriptor |
| SIGPWR    | Power failure/restart         |
| SIGSYS    | Bad system call               |
## Reliable Signals

| Name        | User-defined |
| ----------- | ------------ |
| SIGRTMIN    |              |
| SIGRTMIN+1  |              |
| SIGRTMIN+2  |              |
| SIGRTMIN+3  |              |
| SIGRTMIN+4  |              |
| SIGRTMIN+5  |              |
| SIGRTMIN+6  |              |
| SIGRTMIN+7  |              |
| SIGRTMIN+8  |              |
| SIGRTMIN+9  |              |
| SIGRTMIN+10 |              |
| SIGRTMIN+11 |              |
| SIGRTMIN+12 |              |
| SIGRTMIN+13 |              |
| SIGRTMIN+14 |              |
| SIGRTMIN+15 |              |
| SIGRTMAX-14 |              |
| SIGRTMAX-13 |              |
| SIGRTMAX-12 |              |
| SIGRTMAX-11 |              |
| SIGRTMAX-10 |              |
| SIGRTMAX-9  |              |
| SIGRTMAX-8  |              |
| SIGRTMAX-7  |              |
| SIGRTMAX-6  |              |
| SIGRTMAX-5  |              |
| SIGRTMAX-4  |              |
| SIGRTMAX-3  |              |
| SIGRTMAX-2  |              |
| SIGRTMAX-1  |              |
| SIGRTMAX    |              |
