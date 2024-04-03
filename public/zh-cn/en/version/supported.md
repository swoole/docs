# Support Plan

| Branch                                                          | PHP Version | Start Date | End of Active Support | End of Security Maintenance |
| --------------------------------------------------------------- | ----------- | ---------- | --------------------- | -------------------------- |
| [v4.8.x](https://github.com/swoole/swoole-src/tree/4.8.x) (LTS) | 7.2 - 8.2   | 2021-10-14 | 2023-10-14            | 2024-06-30                 |
| [v5.0.x](https://github.com/swoole/swoole-src/tree/5.0.x)      | 8.0 - 8.2   | 2022-01-20 | 2023-01-20            | 2023-07-20                 |
| [v5.1.x](https://github.com/swoole/swoole-src/tree/master)      | 8.0 - 8.2   | 2023-09-30 | 2025-09-30            | 2026-09-30                 |

| Active Support | Actively supported by the official development team, reported bugs and security issues will be fixed immediately and formal versions will be released according to the regular process. |
| -------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Security Maintenance | Only supports fixing critical security issues and releases formal versions only when necessary.                                                      |

## Unsupported Branches

!> These versions are no longer supported by the official team. Users still using the following versions should upgrade as soon as possible, as they may encounter unpatched security vulnerabilities.

- `v1.x` (2012-7-1 ~ 2018-05-14)
- `v2.x` (2016-12-30 ~ 2018-05-23)
- `v3.x` (deprecated)
- `v4.0.x`, `v4.1.x`, `v4.2.x`, `v4.3.x` (2018-06-14 ~ 2019-12-31)
- `v4.4.x` (2019-04-15 ~ 2020-04-30)
- `v4.5.x` (2019-12-20 ~ 2021-01-06)
- `v4.6.x`, `v4.7.x` (2021-01-06 ~ 2021-12-31)

## Version Features
- `v1.x` : Asynchronous callback mode
- `v2.x` : Coroutine based on `setjmp/longjmp`, single-stack coroutine implementation, still based on asynchronous callback implementation, switching `PHP` call stack after a callback event is triggered
- `v4.0-v4.3` : Dual-stack coroutine based on `boost context asm`, comprehensive coroutine kernel implementation, coroutine scheduler based on `EventLoop`
- `v4.4-v4.8` : Implemented `runtime coroutine hook`, automatically replaces synchronous blocking functions of PHP with asynchronous non-blocking mode for coroutines, making Swoole coroutines compatible with the vast majority of PHP libraries
- `v5.0` : Fully coroutine-enabled, removes non-coroutine modules; strongly typed, removes a lot of historical baggage; provides a new `swoole-cli` running mode
- `v5.1` : Coroutine support for `pdo_pgsql`, `pdo_oci`, `pdo_odbc`, `pdo_sqlite`; enhanced performance of `Http\Server`
