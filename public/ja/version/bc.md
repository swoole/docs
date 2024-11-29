```markdown
# 下位互換性の変更
## v5.0.0
* `Server`のデフォルト運用モードを`SWOOLE_BASE`に変更
* 最小PHPバージョン要件を`8.0`に引き上げ
* 全クラスの方法と関数に型制限を追加し、強い型モードに変更
* アンダーバー`PSR-0`のクラス名を削除し、命名空間スタイルのクラス名のみを残す。例えば`swoole_server`は`Swoole\Server`に変更する必要がある
* `Swoole\Coroutine\Redis`と`Swoole\Coroutine\MySQL`を廃止し、`Runtime Hook`と原生の`Redis`/`MySQL`クライアントを使用してください

## v4.8.0
- `BASE`モードでは、`onStart`回调は常に最初のワークプロセス（`workerId`が`0`）が起動した時に回调され、`onWorkerStart`よりも先に実行されます。`onStart`関数では常にコラoutine `API`を使用できます。`Worker-0`が致命的なエラーで再起動した場合、再び`onStart`が回调されます。
以前のバージョンでは、`onStart`は単一のワークプロセスがある場合に`Worker-0`で回调され、複数のワークプロセスがある場合は`Manager`プロセスで実行されました。
## v4.7.0
- `Table\Row`を削除し、`Table`はもはや配列での読み書きをサポートしません
## v4.6.0
- `session id`の最大制限を移除し、繰り返さないようにしました
- コラoutineを使用する際は、不安全な機能（`pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait`）を無効にしました
- コラoutine hookをデフォルトで有効にしました
- PHP7.1のサポートを停止しました
- `Event::rshutdown()`を廃止し、Coroutine\runに置き換えるようしてください
## v4.5.4
- `SWOOLE_HOOK_ALL`には`SWOOLE_HOOK_CURL`が含まれます
- `ssl_method`を移除し、`ssl_protocols`をサポートしました
## v4.4.12
- このバージョンではWebSocketフレームの圧縮がサポートされ、pushメソッドの3番目のパラメータをflagsに変更しました。strict_typesが設定されていない場合でも、コードの互換性には影響しません。そうでなければ、boolがintに暗黙で変換できないタイプエラーが発生します。この問題はv4.4.13で修正されます
## v4.4.1
- 注册されたシグナルは、イベントループを維持する条件としては使用されなくなりました。**プログラムがシグナルのみを注册し、その他の作業を行わなかった場合、それは空闲と見なされ、すぐに終了します**（この時、タイマーの登録を通じてプロセスの終了を防ぐことができます）
## v4.4.0
- PHP公式と一致するため、PHP7.0のサポートを停止しました(@matyhtf)
- `Serialize`モジュールを移除し、[ext-serialize](https://github.com/swoole/ext-serialize)拡張で单独に維持されています
- `PostgreSQL`モジュールを移除し、[ext-postgresql](https://github.com/swoole/ext-postgresql)拡張で单独に維持されています
- `Runtime::enableCoroutine`はもはや自動的にコラoutine内外環境に互換性を持たなくなり、一度有効にすると、すべてのブロック操作はコラoutine内で呼び出さなければなりません(@matyhtf)
- 全新的なコラoutine `MySQL`クライアントドライバが導入されたため、底辺の設計がより規範的になりましたが、いくつかの小さな下位互換性の変更があります（[4.4.0更新日志](https://wiki.swoole.com/wiki/page/p-4.4.0.html)を参照）
## v4.3.0
- すべての非同期モジュールを移除しました。詳細は[独立非同期拡張](https://wiki.swoole.com/wiki/page/p-async_ext.html)または[4.3.0更新日志](https://wiki.swoole.com/wiki/page/p-4.3.0.html)を参照
## v4.2.13

> 歴史的なAPI設計上の問題による避けられない非互換性の変更

* コラoutine Redisクライアントのサブスクリプションモードの操作が変更されました。詳細は[サブスクリプションモード](https://wiki.swoole.com/#/coroutine_client/redis?id=%e8%ae%a2%e9%98%85%e6%a8%a1%e5%bc%8f)を参照
## v4.2.12

>実験的特性 + 歴史的なAPI設計上の問題による避けられない非互換性の変更
- `task_async`設定項目を移除し、[task_enable_coroutine](https://wiki.swoole.com/#/server/setting?id=task_enable_coroutine)に置き換えられました
## v4.2.5
- `onReceive`と`Server::getClientInfo`のUDPクライアントへのサポートを移除しました
## v4.2.0
- 非同期の`swoole_http2_client`を完全に見取り、コラoutine HTTP2クライアントを使用してください
## v4.0.4

このバージョンから、非同期の`Http2\Client`は `E_DEPRECATED`ヒントをトリガーし、次のバージョンで削除されます。`Coroutine\Http2\Client`を代わりに使用してください。

`Http2\Response`の`body`属性は`data`と名前が変更されました。この変更は、`request`と`response`の両方を統一し、HTTP2プロトコルのフレームタイプ名称により一致するためです。

このバージョンから、`Coroutine\Http2\Client`は相対的に完全なHTTP2プロトコルサポートを持ち、企業級の生産環境のアプリケーションニーズ（例えば`grpc`、`etcd`など）を満たすことができます。したがって、HTTP2に関する一連の変更は非常に必要です。
## v4.0.3

`swoole_http2_response`と`swoole_http2_request`を一致させ、すべての属性名を複数形に変更しました。関連する属性は以下の通りです。
- `headers`
- `cookies`
## v4.0.2

> 底辺の実装が複雑すぎてメンテナンスが困難であり、またユーザーがよく誤解しているため、一時的に以下のAPIを削除します:
- `Coroutine\Channel::select`

同時に、開発ニーズを満たすために`Coroutine\Channel->pop`メソッドの2番目のパラメータに`timeout`を追加しました
## v4.0

> コラoutineカーネルのアップグレードにより、任意の関数や場所でコラoutineを呼び出すことができ、特別な処理を行う必要がなくなりました。したがって、以下のAPIを削除しました
- `Coroutine::call_user_func`
- `Coroutine::call_user_func_array`
```
