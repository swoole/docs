# Server-side (asynchronous style)

Conveniently create an asynchronous server program that supports three types of sockets: `TCP`, `UDP`, and [unixSocket](/learn?id=what-is-ipc), with support for both `IPv4` and `IPv6`. It also supports SSL/TLS tunnel encryption with one-way or two-way certificates. Users do not need to pay attention to the underlying implementation details, just set the callback functions for network [events](/server/events), as shown in the example in [Quick Start](/start/start_tcp_server).

!> The server-side style is asynchronous (meaning all events require setting callback functions), but it also supports coroutines. By enabling [enable_coroutine](/server/setting?id=enable_coroutine) (which is enabled by default), coroutines can be used. All business logic under [coroutines](/coroutine) is written synchronously.

Learn more:

[Introduction to two running modes of Server](/learn?id=introduction-to-two-running-modes-of-server ':target=_blank')  
[What are the differences between Process, ProcessPool, and UserProcess](/learn?id=what-are-the-differences ':target=_blank')  
[Differences and relations between Master Process, Reactor Thread, Worker Process, Task Process, and Manager Process](/learn?id=differences-and-relationships ':target=_blank')

### Running Flowchart <!-- {docsify-ignore} -->

![running_process](https://cdn.jsdelivr.net/gh/sy-records/staticfile/images/swoole/running_process.png ':size=800xauto')

### Process/Thread Structure Diagram <!-- {docsify-ignore} -->

![process_structure](https://cdn.jsdelivr.net/gh/sy-records/staticfile/images/swoole/process_structure.png ':size=800xauto')

![process_structure_2](../_images/server/process_structure_2.png)
