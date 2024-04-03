# Kernel Parameter Adjustment

## ulimit Setting

Adjust `ulimit -n` to 100000 or even larger. Execute `ulimit -n 100000` in the command line to modify it. If unable to modify, you need to set `/etc/security/limits.conf` by adding:

```
* soft nofile 262140
* hard nofile 262140
root soft nofile 262140
root hard nofile 262140
* soft core unlimited
* hard core unlimited
root soft core unlimited
root hard core unlimited
```

Note that after modifying the `limits.conf` file, you need to restart the system for it to take effect.

## Kernel Configuration

There are 3 ways to modify kernel parameters on a `Linux` operating system:

- Modify the `/etc/sysctl.conf` file, add configuration options in the format `key = value`, save the changes, then use `sysctl -p` to load the new configuration.
- Use the `sysctl` command for temporary modifications, e.g., `sysctl -w net.ipv4.tcp_mem="379008 505344 758016"`.
- Directly modify files in the `/proc/sys/` directory, e.g., `echo "379008 505344 758016" > /proc/sys/net/ipv4/tcp_mem`.

> The first method will automatically take effect after the operating system restarts, while the second and third methods will not persist after a reboot.

### net.unix.max_dgram_qlen = 100

When using `swoole` with Unix socket dgram for inter-process communication, adjust this parameter if the request volume is high. The system default is 10, but it can be set to 100 or higher. Alternatively, increase the number of worker processes to reduce the request load on a single worker process.

### net.core.wmem_max

Adjust this parameter to increase the memory size of the socket buffer.

```
net.ipv4.tcp_mem  =   379008       505344  758016
net.ipv4.tcp_wmem = 4096        16384   4194304
net.ipv4.tcp_rmem = 4096          87380   4194304
net.core.wmem_default = 8388608
net.core.rmem_default = 8388608
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
```

### net.ipv4.tcp_tw_reuse

Determines if socket reuse is enabled. This function allows the server to quickly reuse listening ports after a restart. Failure to set this parameter may result in port not being released promptly, causing startup failure on server restart.

### net.ipv4.tcp_tw_recycle

For fast recycling of sockets, especially useful for short-lived connection servers. Enabling this parameter allows for quick recycling of TIME-WAIT sockets in TCP connections. By default, this parameter is set to 0 on Linux systems, indicating it is turned off. Opening this parameter may cause instability for NAT user connections, so conduct careful testing before enabling it.

## Message Queue Settings

Adjust these kernel parameters when using message queues for inter-process communication:

- kernel.msgmnb = 4203520 (maximum number of bytes for a message queue)
- kernel.msgmni = 64 (maximum number of message queues allowed)
- kernel.msgmax = 8192 (maximum length for a single message in a queue)

## FreeBSD/MacOS

- sysctl -w net.local.dgram.maxdgram=8192
- sysctl -w net.local.dgram.recvspace=200000
  Adjust buffer sizes for Unix Sockets.

## Enabling Core Dump

Set kernel parameter:

```
kernel.core_pattern = /data/core_files/core-%e-%p-%t
```

Check the current coredump file limit with the `ulimit -c` command.

```shell
ulimit -c
```

If it is set to 0, modification is needed in `/etc/security/limits.conf` to set the limit.

> Enabling core dump will export the process to a file upon program exceptions, greatly aiding in debugging program issues.

## Other Important Configurations

- net.ipv4.tcp_syncookies=1
- net.ipv4.tcp_max_syn_backlog=81920
- net.ipv4.tcp_synack_retries=3
- net.ipv4.tcp_syn_retries=3
- net.ipv4.tcp_fin_timeout = 30
- net.ipv4.tcp_keepalive_time = 300
- net.ipv4.tcp_tw_reuse = 1
- net.ipv4.tcp_tw_recycle = 1
- net.ipv4.ip_local_port_range = 20000 65000
- net.ipv4.tcp_max_tw_buckets = 200000
- net.ipv4.route.max_size = 5242880

## Checking if the Configuration is in Effect

After modifying `net.unix.max_dgram_qlen = 100`, you can check by running:

```shell
cat /proc/sys/net/unix/max_dgram_qlen
```

If the modification was successful, the displayed value here will be the new setting.
