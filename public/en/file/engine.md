# Asynchronous File Operations

Swoole's [one-click coroutineization](/runtime) can easily transform PHP's file operations from synchronous and blocking to asynchronous execution. Swoole has built-in support for two different types of file asynchronous strategies.

## Thread Pool

* The `Thread Pool` is Swoole's default file asynchronous operation. When a user initiates a file operation, Swoole will directly dispatch this operation to the `Thread Pool`, where a child thread is responsible for completing the file operation. After execution is complete, the coroutine is switched back.
* All PHP file operation functions can be implemented asynchronously through the `Thread Pool`, such as `file_get_contents`, `fopen`, etc.
* No dependencies are required, offering high compatibility and ease of use.

## io_uring

* `io_uring` is an internal strategy in Swoole v6.0 and later, based on `io_uring` and `epoll` to achieve asynchrony.
* It offers high throughput and can handle a large number of file asynchronous operations.
* It has requirements for the Linux version and also depends on the `liburing` shared library, making it unavailable on some operating systems.
* Since it is based on file descriptors, it only supports a few PHP file operation functions.
* There are high requirements for the Linux kernel version.

!> `liburing` must be installed on the system and Swoole must be compiled with `--enable-iouring` to use this feature.

!> Enabling `io_uring` does not replace the `Thread Pool` mode; some functions that cannot be coroutineized by `io_uring` will still be handled by the `Thread Pool`.

!> The `io_uring` strategy can only handle `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, and `filesize` functions.
