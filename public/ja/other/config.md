```ini
# ini設定

設定 | デフォルト値 | 効果
---|---|---
swoole.enable_coroutine | On | `On`, `Off`で内蔵コーンルーチンをON/OFFします。[詳細](/server/setting?id=enable_coroutine)。
swoole.display_errors | On | `Swoole`のエラー情報を表示/非表示します。
swoole.unixsock_buffer_size | 8M |プロセス間通信の`Socket`バッファサイズを設定します。これは[socket_buffer_size](/server/setting?id=socket_buffer_size)と同等です。
swoole.use_shortname | On | 短アッシュネームを有効/無効にします。[詳細](/other/alias?id=协程短名称)。
swoole.enable_preemptive_scheduler | Off | コーンルーチンのデッドロックによるCPU時間の長時間の占有（10msのCPU時間）を防ぎ、他のコーンルーチンが[スケジュール](/coroutine?id=协程调度)されないようにします。[例](https://github.com/swoole/swoole-src/tree/master/tests/swoole_coroutine_scheduler/preemptive)。
swoole.enable_library | On | 拡張内蔵のlibraryをON/OFFします。
```
