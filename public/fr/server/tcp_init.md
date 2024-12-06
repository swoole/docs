`Swoole\Server`类是Swoole框架中用于创建和管理异步风格服务器的基类。它提供了一系列方法、属性、配置项以及事件，使得开发者能够构建高性能的网络服务器。以下是`Swoole\Server`类的相关信息：

### 方法

- **构造方法**：`__construct()`，用于初始化服务器对象。
- **事件回调**：`on()`，用于绑定事件回调函数。
- **启动服务器**：`start()`，用于启动服务器，开始监听连接请求。

### 属性

- `$host`：服务器监听的IP地址。
- `$port`：服务器监听的端口号。
- `$server`：服务器对象实例。
- `$settings`：服务器运行时配置参数。
- `$connections`：当前所有连接的数组。

### 配置项

- `worker_num`：指定启动的worker进程数。
- `max_request`：每个worker进程允许处理的最大任务数。
- `max_connect`：服务器允许维持的最大TCP连接数。
- `dispatch_mode`：数据包分发策略，包括轮循模式、固定模式和抢占模式。
- `task_worker_num`：服务器开启的task worker进程数。
- `daemonize`：是否以守护进程模式运行。
- `log_file`：指定日志文件路径。
- `log_level`：日志输出级别。
- `heartbeat_check_interval`：心跳检测间隔。
- `heartbeat_id`：连接允许的最大闲置时间。
- `open_eof_check`：打开eof检测功能。
- `eof`：设置eof字符串。
- `package_length_type`：指定包长字段的类型。
- `package_length_offset`：从第几个字节开始计算长度。
- `package_body_offset`：从第几个字节开始存放了长度字段。
- `max_package_length`：设置最大数据包尺寸。

### 事件

- **onStart**：服务器启动时触发。
- **onWorkerStart**：Worker进程启动时触发。
- **onConnect**：有新连接接入时触发。
- **onReceive**：接收到数据时触发。
- **onClose**：连接关闭时触发。
- **onTask**：Task进程处理任务时触发。
- **onFinish**：Task进程处理任务结束或失败时触发。

通过上述信息，开发者可以更好地理解和使用`Swoole\Server`类来构建高性能的网络服务器。
