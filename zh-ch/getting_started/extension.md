# 扩展冲突

由于某些跟踪调试的`PHP`扩展大量使用了全局变量，可能会导致`Swoole`协程发生崩溃。请关闭以下相关扩展：

* phptrace
* aop
* molten
* xhprof
* phalcon（`Swoole`协程无法运行在 `phalcon` 框架中）
