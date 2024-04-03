# Error Report Submission

## Information

When you believe you have found a bug in the Swoole core, please report it. The core developers of Swoole may not be aware of the issue unless you report it. Without actively reporting the bug, it might be challenging to discover and fix. You can submit error reports on the [GitHub issue area](https://github.com/swoole/swoole-src/issues) by clicking the `New issue` button in the upper right corner. Error reports submitted here will be given the highest priority for resolution.

Please avoid sending error reports via email lists or private messages. You can also make any requests or suggestions regarding Swoole in the GitHub issue area.

Before submitting an error report, please read the **How to Submit an Error Report** section below.

## Creating a New Issue

When creating an issue, a template will be provided. Please fill it out carefully to ensure that the issue is not ignored due to lack of information:

```markdown

Please answer these questions before submitting your issue. Thanks!
> Please answer these questions before submitting your issue:

1. What did you do? If possible, provide a simple script for reproducing the error.
> Please describe in detail the process that caused the issue, paste relevant code, and ideally provide a stable script to reproduce the issue.

2. What did you expect to see?
> What were your expected results?

3. What did you see instead?
> What were the actual results?

4. What version of Swoole are you using (`php --ri swoole`)?
> Provide your version. Paste the output of `php --ri swoole`.

5. What is your machine environment (including the version of kernel, PHP, and GCC) used?
> What is the system environment you are using (including kernel, PHP, and gcc compiler versions)?
> You can use commands like `uname -a`, `php -v`, `gcc -v` to display this information.

```

The most critical aspect is to provide a **simple code script that can reproduce the issue stably**. Otherwise, you must provide as much other information as possible to help the developers identify the cause of the error.

## Memory Analysis (Highly Recommended)

Often, Valgrind is more effective in detecting memory issues than gdb. Run your program with the following command until the bug is triggered:

```shell
USE_ZEND_ALLOC=0 valgrind --log-file=/tmp/valgrind.log php your_file.php
```

* When an error occurs, exit by typing `ctrl+c`, then upload the `/tmp/valgrind.log` file to help the development team locate the bug.

## About Segmentation Fault (Core Dump)

Additionally, in a special case, you can use debugging tools to assist developers in locating the issue.

```shell
WARNING	swManager_check_exit_status: worker#1 abnormal exit, status=0, signal=11
```

If the above warning appears in the Swoole log (signal 11), indicating a `core dump`, you need to use a debugging tool to determine the location of the issue.

> Before using `gdb` to trace `swoole`, make sure to compile with the `--enable-debug` parameter to retain more information.

Enable core dump files:
```shell
ulimit -c unlimited
```

To debug the program, trigger the bug, and the core dump file will be generated in the program's directory, system root directory, or `/cores` directory (depending on your system configuration).

Enter the gdb debugging program with the following command:

```
gdb php core
gdb php /tmp/core.1234
```

Then, type `bt` and press Enter to see the problematic call stack:

```
(gdb) bt
```

You can view a specific stack frame by typing `f number`:

```
(gdb) f 1
(gdb) f 0
```

Include all the above information in the issue.
