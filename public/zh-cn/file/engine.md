# 异步文件操作

`Swoole`的[一键协程化](/runtime)可以很方便的将`PHP`的文件操作由同步阻塞变为异步执行，`Swoole`一共内置了两种不同的文件异步策略。

## 线程池

* `线程池`是`Swoole`默认的文件异步操作，用户发起文件操作时，`Swoole`会将这个操作直接投递到`线程池`中，由子线程负责完成文件操作，执行完毕后再将协程切换回来。
* `PHP`的文件操作函数都可以通过`线程池`实现异步操作，例如`file_get_contents`，`fopen`等。
* 无需任何依赖库，兼容性高，可直接上手使用。


## io_uring

* `io_uring`是`Swoole v6.0`之后内置的策略，基于`io_uring`和`epoll`实现异步。
* 吞吐量高，可以处理大量文件异步操作。
* 对`linux`版本有要求，也需要依赖`liburing`这个共享库，有些操作系统没法使用这个特性。
* 由于是基于`文件描述符`实现文件异步，只支持少数`PHP`的文件操作函数。


!> 当系统安装了`liburing`和编译`Swoole`开启了`--enable-iouring`之后才能使用。

!> 开启了`io_uring`之后并不会替换掉`线程池`模式，有些`io_uring`没法操作的函数还是会让`线程池`处理。

!> `io_uring`只能处理`file_get_contents`，`file_put_contents`，`fopen`，`fclose`，`fread`，`fwrite`，`mkdir`，`fsync`，`fdatasync`，`rename`，`fstat`，`lstat`，`filesize`函数。

!> `ftruncate`函数需要`liburing`版本`>=`2.6
