# Simple Example

Most of the features of `Swoole` can only be used in a `cli` command-line environment, so please make sure you have a `Linux Shell` environment set up. You can write your code using editors like `Vim`, `Emacs`, `PhpStorm`, or any other editor, and then execute the program in the command line with the following command.

```shell
php /path/to/your_file.php
```

After successfully running the `Swoole` server program, if your code does not contain any `echo` statements, there won't be any output on the screen. However, the underlying system will be actively listening on a network port, waiting for client connections. You can test this functionality by connecting using appropriate client tools and programs.

#### Process Management

By default, you can terminate the `Swoole` service by using `CTRL+C` in the terminal where it was started. However, if the terminal is closed at this point, there might be issues. To handle this, it's recommended to run the service in the background. For more details, refer to the [Daemonize setting](/server/setting?id=daemonize).

!> Most examples in the simple examples are in asynchronous programming style. You can also achieve the functionalities shown in the examples using coroutine style. For more details, refer to [Server (Coroutine Style)](coroutine/server.md).

!> The majority of modules provided by `Swoole` can only be used in the command-line terminal (`cli`). Currently, only the [Synchronous Blocking Client](/client) can be used in the `PHP-FPM` environment.
