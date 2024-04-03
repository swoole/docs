# Other Knowledge

## Setting DNS Resolution Timeout and Retry

In network programming, `gethostbyname` and `getaddrinfo` are often used to achieve domain name resolution. These two `C` functions do not provide a timeout parameter. In fact, you can modify `/etc/resolv.conf` to set timeout and retry logic.

!> Refer to the `man resolv.conf` document

### Multiple NameServers <!-- {docsify-ignore} -->

```
nameserver 192.168.1.3
nameserver 192.168.1.5
option rotate
```

You can configure multiple `nameservers`, and the underlying system will automatically rotate among them. When the query to the first `nameserver` fails, it will automatically switch to the second `nameserver` for retry.

The `option rotate` configuration enables load balancing for `nameservers` using a round-robin mode.

### Timeout Control <!-- {docsify-ignore} -->

```
option timeout:1 attempts:2
```

- `timeout`: Controls the timeout for UDP reception, in seconds, default is `5` seconds.
- `attempts`: Controls the number of attempts. When configured as `2`, it means a maximum of `2` attempts will be made, default is `5` attempts.

For example, with `2` `nameservers`, `attempts` set to `2`, and a timeout of `1` second, if all DNS servers do not respond, the longest waiting time would be `4` seconds (`2x2x1`).

### Call Trace <!-- {docsify-ignore} -->

You can trace the call using [strace](/other/tools?id=strace).

Set the `nameservers` to two non-existent IPs, and run the following `PHP` code: `var_dump(gethostbyname('www.baidu.com'));` to resolve the domain name.

It shows a sequence of socket and poll calls with timeouts and retries.
