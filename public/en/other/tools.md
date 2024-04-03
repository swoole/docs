# Tool Usage
## yasd

[yasd](https://github.com/swoole/yasd)

Step debugging tool, usable in the `Swoole` coroutine environment, supports debugging mode for both `IDE` and command line.
## tcpdump

tcpdump is an essential tool when debugging network communication programs. It is very powerful and can show every detail of network communication. For example, in TCP, you can see the 3-way handshake, PUSH/ACK data pushes, the 4-way close handshake, and all the details. This includes details such as the number of bytes received in each network packet and the time.
### Instructions for Use

The simplest example of use:

```shell
sudo tcpdump -i any tcp port 9501
```

* The `-i` parameter specifies the network card, where `any` represents all network cards.
* `tcp` specifies to only listen for TCP protocol.
* `port` specifies the port to listen on.

!> tcpdump requires root privileges; to view the content of the communication data, you can add the `-Xnlps0` parameter. For more parameters, please refer to online articles.
### Running Result

```
13:29:07.788802 IP localhost.42333 > localhost.9501: Flags [S], seq 828582357, win 43690, options [mss 65495,sackOK,TS val 2207513 ecr 0,nop,wscale 7], length 0
13:29:07.788815 IP localhost.9501 > localhost.42333: Flags [S.], seq 1242884615, ack 828582358, win 43690, options [mss 65495,sackOK,TS val 2207513 ecr 2207513,nop,wscale 7], length 0
13:29:07.788830 IP localhost.42333 > localhost.9501: Flags [.], ack 1, win 342, options [nop,nop,TS val 2207513 ecr 2207513], length 0
13:29:10.298686 IP localhost.42333 > localhost.9501: Flags [P.], seq 1:5, ack 1, win 342, options [nop,nop,TS val 2208141 ecr 2207513], length 4
13:29:10.298708 IP localhost.9501 > localhost.42333: Flags [.], ack 5, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 0
13:29:10.298795 IP localhost.9501 > localhost.42333: Flags [P.], seq 1:13, ack 5, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 12
13:29:10.298803 IP localhost.42333 > localhost.9501: Flags [.], ack 13, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 0
13:29:11.563361 IP localhost.42333 > localhost.9501: Flags [F.], seq 5, ack 13, win 342, options [nop,nop,TS val 2208457 ecr 2208141], length 0
13:29:11.563450 IP localhost.9501 > localhost.42333: Flags [F.], seq 13, ack 6, win 342, options [nop,nop,TS val 2208457 ecr 2208457], length 0
13:29:11.563473 IP localhost.42333 > localhost.9501: Flags [.], ack 14, win 342, options [nop,nop,TS val 2208457 ecr 2208457], length 0
```
* `13:29:11.563473` The time is precise to microseconds.
* localhost.42333 > localhost.9501 indicates the direction of communication, where 42333 is the client and 9501 is the server.
* [S] indicates a SYN request.
* [.] indicates an ACK acknowledgment packet. (client)SYN->(server)SYN->(client)ACK represents the 3-way handshake process.
* [P] indicates a data push, which can be from the server to the client or from the client to the server.
* [F] indicates a FIN packet for closing the connection. Either the client or server can initiate this.
* [R] indicates a RST packet, which serves the same purpose as an F packet, but RST signifies that when the connection is closed, there is still unprocessed data. It can be understood as forcefully cutting off the connection.
* win 342 indicates the size of the sliding window.
* length 12 indicates the size of the data packet.
## strace

strace can trace the execution of system calls. It can be used to analyze and trace issues in the program when problems occur.

!> On FreeBSD/MacOS, you can use truss.
### Instructions

```shell
strace -o /tmp/strace.log -f -p $PID
```

* -f option traces multiple threads and processes. Without this option, sub-processes and threads cannot be captured.
* -o option directs the output to a file.
* -p $PID specifies the process ID to trace, which can be found using `ps aux`.
* -tt prints the time of system call occurrence with microsecond precision.
* -s specifies the length of strings to print, such as the data received by the recvfrom system call. By default, only 32 bytes are printed.
* -c provides real-time statistics on the duration of each system call.
* -T prints the duration of each system call.
## gdb

GDB is a powerful program debugging tool released by the GNU Open Source Organization for UNIX systems. It can be used to debug programs developed in C/C++. Since PHP and Swoole are developed using the C language, GDB can be used to debug PHP + Swoole programs.

GDB debugging is command line interactive and requires familiarity with common commands.
### Instructions

```shell
gdb -p processID
gdb php
gdb php core
```

There are 3 ways to use gdb:

* To trace a running PHP program, use `gdb -p processID`.
* To run and debug a PHP program using gdb, use `gdb php -> run server.php` for debugging.
* To debug a PHP program after a core dump, load the core dump memory image with gdb using `gdb php core`.

!> If the php binary is not in the PATH environment variable, you need to specify the absolute path when using gdb, for example, `gdb /usr/local/bin/php`.
### Common Commands

* `p`: print, prints the value of a C variable
* `c`: continue, resumes execution of the stopped program
* `b`: breakpoint, sets a breakpoint, can be set by function name like `b zif_php_function`, or by specifying the breakpoint according to the line number in the source code like `b src/networker/Server.c:1000`
* `t`: thread, switches threads, can be used when a process has multiple threads to switch between them
* `ctrl + c`: interrupts the currently running program, used in conjunction with `c` command
* `n`: next, executes the next line, used for stepping through the code
* `info threads`: shows all running threads
* `l`: list, views the source code, can be used with `l function_name` or `l line_number`
* `bt`: backtrace, views the runtime function call stack
* `finish`: completes the current function
* `f`: frame, used with `bt` to switch to a certain level of the function call stack
* `r`: run, runs the program
### zbacktrace

zbacktrace is a custom gdb command provided in the PHP source code package. Its function is similar to the bt command, but unlike bt, zbacktrace shows the PHP function call stack, not the C function call stack.

To use zbacktrace, download the php-src, extract the files, and locate a `.gdbinit` file in the root directory. Then, in the gdb shell, enter:

```shell
source .gdbinit
zbacktrace
```

`.gdbinit` also provides other additional commands. You can explore the source code for more detailed information.
#### Tracing the Infinite Loop Problem Using gdb+zbacktrace

```shell
gdb -p processID
```

* Use `ps aux` command to find the Worker process ID where the infinite loop is occurring.
* Use `gdb -p` to trace the specified process.
* Continuously press `ctrl + c`, `zbacktrace`, `c` to check at which part of the PHP code the loop is happening.
* Identify the corresponding PHP code and resolve the issue.
## lsof

On the Linux platform, the `lsof` tool is provided to view the file handles opened by a process. It can be used to trace all the sockets, files, and resources opened by Swoole's worker processes.
### Instructions

```shell
lsof -p [Process ID]
```
### Running results

```shell
lsof -p 26821
lsof: WARNING: can't stat() tracefs file system /sys/kernel/debug/tracing
      Output information may be incomplete.
COMMAND   PID USER   FD      TYPE             DEVICE SIZE/OFF    NODE NAME
php     26821  htf  cwd       DIR                8,4     4096 5375979 /home/htf/workspace/swoole/examples
php     26821  htf  rtd       DIR                8,4     4096       2 /
php     26821  htf  txt       REG                8,4 24192400 6160666 /opt/php/php-5.6/bin/php
php     26821  htf  DEL       REG                0,5          7204965 /dev/zero
php     26821  htf  DEL       REG                0,5          7204960 /dev/zero
php     26821  htf  DEL       REG                0,5          7204958 /dev/zero
php     26821  htf  DEL       REG                0,5          7204957 /dev/zero
php     26821  htf  DEL       REG                0,5          7204945 /dev/zero
php     26821  htf  mem       REG                8,4   761912 6160770 /opt/php/php-5.6/lib/php/extensions/debug-zts-20131226/gd.so
php     26821  htf  mem       REG                8,4  2769230 2757968 /usr/local/lib/libcrypto.so.1.1
php     26821  htf  mem       REG                8,4   162632 6322346 /lib/x86_64-linux-gnu/ld-2.23.so
php     26821  htf  DEL       REG                0,5          7204959 /dev/zero
php     26821  htf    0u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    1u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    2u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    3r      CHR                1,9      0t0      11 /dev/urandom
php     26821  htf    4u     IPv4            7204948      0t0     TCP *:9501 (LISTEN)
php     26821  htf    5u     IPv4            7204949      0t0     UDP *:9502 
php     26821  htf    6u     IPv6            7204950      0t0     TCP *:9503 (LISTEN)
php     26821  htf    7u     IPv6            7204951      0t0     UDP *:9504 
php     26821  htf    8u     IPv4            7204952      0t0     TCP localhost:8000 (LISTEN)
php     26821  htf    9u     unix 0x0000000000000000      0t0 7204953 type=DGRAM
php     26821  htf   10u     unix 0x0000000000000000      0t0 7204954 type=DGRAM
php     26821  htf   11u     unix 0x0000000000000000      0t0 7204955 type=DGRAM
php     26821  htf   12u     unix 0x0000000000000000      0t0 7204956 type=DGRAM
php     26821  htf   13u  a_inode               0,11        0    9043 [eventfd]
php     26821  htf   14u     unix 0x0000000000000000      0t0 7204961 type=DGRAM
php     26821  htf   15u     unix 0x0000000000000000      0t0 7204962 type=DGRAM
php     26821  htf   16u     unix 0x0000000000000000      0t0 7204963 type=DGRAM
php     26821  htf   17u     unix 0x0000000000000000      0t0 7204964 type=DGRAM
php     26821  htf   18u  a_inode               0,11        0    9043 [eventpoll]
php     26821  htf   19u  a_inode               0,11        0    9043 [signalfd]
php     26821  htf   20u  a_inode               0,11        0    9043 [eventpoll]
php     26821  htf   22u     IPv4            7452776      0t0     TCP localhost:9501->localhost:59056 (ESTABLISHED)
```

* The so file is the dynamic library loaded by the process.
* IPv4/IPv6 TCP (LISTEN) represents the server listening port.
* UDP represents the UDP port that the server is listening on.
* When `unix type=DGRAM` is shown, it indicates a [unix socket](/learn?id=What_is_IPC) created by the process.
* IPv4 (ESTABLISHED) indicates a TCP client connected to the server, including the client's IP and PORT, as well as the status (ESTABLISHED).
* 9u / 10u indicates the file descriptor (fd) value of the file handle.
* For more information, please refer to the manual of `lsof`.
## perf

`perf` tool is a powerful dynamic tracing tool provided by the Linux kernel. The `perf top` command can be used to analyze performance issues of a running program in real time. Unlike tools like `callgrind`, `xdebug`, `xhprof`, `perf` does not require code modification to export profile result files.
### Instructions

```shell
perf top -p [process ID]
```
### Output

![perf top output](../_images/other/perf.png)

The perf results clearly display the time consumption of various C functions during the execution of the current process, providing insights into which C function is utilizing more CPU resources.

If you are familiar with Zend VM, excessive Zend function calls may indicate that your program heavily relies on certain functions, resulting in high CPU consumption. In such cases, targeted optimization can be considered.
