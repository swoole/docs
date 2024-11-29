`Swoole\Server`类是Swoole扩展中用于创建和管理异步风格服务器的基类。它提供了一系列方法、属性、配置项以及事件，使得开发者能够构建高性能的网络服务器。以下是`Swoole\Server`类的相关信息：

### 方法

- **构造方法**：`__construct()`，用于初始化服务器。
- **事件回调**：`on()`，用于绑定事件处理函数。
- **启动服务器**：`start()`，用于启动服务器监听连接。
- **关闭服务器**：`close()`，用于关闭服务器。
- **发送数据**：`send()`，用于向客户端发送数据。
- **接收数据**：`recv()`，用于接收客户端数据。

### 属性

- `$host`：服务器监听的IP地址。
- `$port`：服务器监听的端口号。
- `$server`：服务器实例。
- `$fd`：文件描述符。
- `$reactor_id`：事件循环ID。
- `$data`：接收到的数据。

### 配置项

- `worker_num`：指定启动的worker进程数。
- `max_request`：每个worker进程允许处理的最大任务数。
- `max_conn`：服务器允许维持的最大TCP连接数。
- `ipc_mode`：进程间通信方式。
- `dispatch_mode`：数据包分发策略。
- `task_worker_num`：开启异步task功能时task进程数。
- `task_max_request`：每个task进程允许处理的最大任务数。
- `daemonize`：是否以守护进程模式运行。
- `log_file`：日志文件路径。
- `heartbeat_check_interval`：心跳检测间隔。
- `heartbeat_id_life_time`：连接允许的最大闲置时间。
- `open_eof_check`：打开eof检测功能。
- `eof`：设置eof字符串。
- `open_length_check`：打开包长检测。
- `length_field_offset`：包头中第几个字节开始存放了长度字段。
- `length_field_length`：指定包长字段的类型。
- `max_packet_size`：设置最大数据包尺寸。

### 事件

- **onRequest**：接收数据的回调。
- **onStart**：服务器启动的回调。
- **onWorkerStart**：worker进程启动的回调。
- **onConnect**：新连接接入时的回调。
- **onClose**：连接关闭时的回调。
- **onMessage**：接收消息的回调。
- **onTimer**：定时器触发的回调。
- **onTask**：任务完成的回调。
- **onFinish**：任务结束的回调。

通过上述信息，您可以更好地理解和使用`Swoole\Server`类来构建和管理异步服务器。
