# IO-Uring

`iouring` 是 `linux` 内核提供一种全新的并发`IO`调用机制，与`epoll`多路复用机制不同，`iouring`同时支持磁盘`IO`、网络`IO`。

请注意`io_uring != epoll`，这是两个完全不同的概念，`iouring`不是事件轮训机制，而是一种异步系统调用的实现。在`Swoole`底层实现中，
`iouring`与协程是强依赖关系，仅在`Swoole`协程中可用。`Swoole`使用`iouring`实现了用户态系统调用，搭配用户态线程（协程）模型，
取代传统基于`epoll/kqueue`的`reactor/proactor`模型，实现了全新一代的高效并发架构。

## 同步阻塞`IO`

一个经典的`IO`调用流程如下：

```c
int sock = socket(AF_INET, SOCK_STREAM, 0);
struct sockaddr_in addr;
addr.sin_family = AF_INET;
addr.sin_port = htons(80);
addr.sin_addr.s_addr = inet_addr(dns_lookup("www.qq.com"));
connect(sock, (struct sockaddr *)&addr, sizeof(addr));
send(sock, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n", 49, 0);
recv(sock, buf, sizeof(buf), 0);
close(sock);
```

`socket`、`connect`、`send`、`recv`、`close`是`C`语言的`POSIX`标准库函数，它们是同步阻塞的，会阻塞当前线程，直到调用成功。
因此需要配合多线程、多进程才能实现并发。

## `epoll` 异步非阻塞`IO`

基于`epoll`的异步非阻塞`IO`调用流程如下：
```cpp
auto epoll = new EpollReactor();
set_nonblocking(sock);
connect(sock, (struct sockaddr *)&addr, sizeof(addr));
epoll->add(sock, EPOLLOUT, []() {
    getsockopt(sock, SOL_SOCKET, SO_ERROR, &err, sizeof(err));
    if (err != 0) {
        // 连接失败
        return;
    }
    send(sock, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n", 49, 0);
    epoll->add(sock, EPOLLIN, []() {
        // 接收到响应的数据
        recv(sock, buf, sizeof(buf), 0);
        close(sock);
    });
});
```

需要注册`EPOLLIN/EPOLLOUT`事件监听，将`socket`设置为非阻塞模式，然后在`epoll`事件回调中处理`IO`事件。

## `iouring`

```c
Coroutine::create([&]() { 
  Iouring::connect(sock, (struct sockaddr *)&addr, sizeof(addr));
  Iouring::send(sock, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n", 49, 0);
  Iouring::recv(sock, buf, sizeof(buf), 0);
  Iouring::close(sock);
});
```

使用`iouring`发起`connect`、`send`、`recv`、`close`等系统调用，系统调用不再是同步的，而是异步的，发起系统调用将分为两个步骤：
1. 提交系统调用请求到`SQE`队列，挂起当前协程；
2. 等待操作系统完成系统调用后，收割`CQE`队列，获得`ioring`通知，唤醒挂起的协程，继续执行。

传统的`epoll`异步非阻塞`IO`模型的程序，需要在用户态和内核态之间频繁切换，穿插运行。
而`iouring`系统调用的提交和收割是批量进行的。系统调用的行为将转为异步模式，不需要切换至内核态，完全在用户态运行。并发的性能得到了大幅提高。
`iouring`支持许多系统调用，包括下列系统调用，均支持`iouring`：

| 分类   | 系统调用                                                                           | 描述     |
|------|--------------------------------------------------------------------------------|--------|
| 文件读写 | open、read、write、close                                                          | 文件系统操作 |
| 网络   | socket、connect、accept、send、recv、sendmsg、recvmsg、sendto、recvfrom、shutdown、close | 网络操作   |
| 互斥锁  | futex_wait、futex_wakeup                                                        | 锁操作    |
| 文件系统 | rename、fstat、mkdir、unlink、rmdir、fsync、rmdir、fsync、fdatasync、ftruncate          | 文件系统操作 | 
| 进程   | sleep、wait、waitpid                                                             | 进程操作   | 

## `Iouring` 封装
`Iouring` `C++` 类封装了`iouring`系统调用，这些函数的参数与`glibc`的`POSIX`标准库函数参数和返回值相同。在`Swoole`协程中，
可以直接调用这些函数，无需切换至内核态。

```cpp
using namespace swoole;
Coroutine::create([&]() {
  // Swoole Iouring 系统调用，仅挂起当前协程，其他协程依然可以运行
  Iouring::sleep(1);
  // POSIX 系统调用，会切换至内核态，阻塞当前线程/进程，其他协程无法运行
  sleep(1);
});
```

查看此类的源代码，可以窥探`iouring`系统调用的实现细节。
- `Iouring::dispatch()`：提交系统调用请求到`SQE`队列
- `Iouring::execute()`：提交系统调用请求到`SQE`队列，并挂起当前协程
- `Iouring::wakeup()`: 收割`CQE`队列，获得`ioring`通知，唤醒挂起的协程

### 超时机制
一般同步阻塞`socket`需要调用`setsockopt`设置超时时间，例如：

```cpp
int timeout = 1000;
setsockopt(sock, SOL_SOCKET, SO_RCVTIMEO, &timeout, sizeof(timeout));
```

`epoll`异步非阻塞`IO`模型则使用定时器来实现超时。而`iouring`则是自带超时机制的，不需要额外的定时器或设置`socket`参数。
```cpp
ssize_t Iouring::recv(int fd, void *buf, size_t len, int flags, double timeout) {
    INIT_EVENT(IORING_OP_RECV);
    io_uring_prep_recv(&event.data, fd, buf, len, flags);
    event.set_timeout(timeout);
    return execute(&event);
}

void Iouring::dispatch(IouringEvent *event) {
    // ...
    io_uring_sqe *sqe = alloc_sqe();
    memcpy(sqe, &event->data, sizeof(event->data));
    io_uring_sqe_set_data(sqe, (void *) event);

    auto timeout_sqe = alloc_sqe();
    memset(timeout_sqe, 0, sizeof(*timeout_sqe));
    io_uring_prep_link_timeout(timeout_sqe, reinterpret_cast<__kernel_timespec *>(&event->timeout), 0);
    io_uring_sqe_set_data(timeout_sqe, reinterpret_cast<void *>(TIMEOUT_EVENT));
    sqe->flags |= IOSQE_IO_LINK;
    
    // ...
}
```

在`Iouring`的`Facde API`中许多接口的最后一个参数是超时时间。例如：`Iouring::recv()`。
```cpp
ssize_t Iouring::recv(int fd, void *buf, size_t len, int flags, double timeout = -1);
```
超时参数的类型为浮点型，单位为秒。默认值为`-1`，表示不设置超时。`iouring`超时的最小粒度为纳秒。这要比`epoll`的定时器精度高。

## `UringSocket` 封装
`UringSocket` 封装了`iouring`系统调用，作为`Swoole\Coroutine\Socket`的子类，替换了`connect`、`send`、`recv`、`close`等方法，
原本使用`epoll`异步非阻塞`IO`实现的网络`IO`函数，现在将使用`iouring`。例如：`read()`方法将使用`Iouring::read()`。

```cpp
ssize_t UringSocket::read(void *_buf, size_t _n) {
    if (sw_unlikely(!is_available(SW_EVENT_READ))) {
        return -1;
    }
    read_co = Coroutine::get_current_safe();
    ssize_t retval = Iouring::read(socket->get_fd(), _buf, _n, socket->read_timeout);
    read_co = nullptr;
    check_return_value(retval);
    return retval;
}
```

`UringSocket`支持`SSL`，与`Swoole\Coroutine\Socket`相同。底层使用`openssl BIO`实现。
