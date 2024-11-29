# ライブラリ

Swooleはv4バージョンから[ライブラリ](https://github.com/swoole/library)モジュールを組み込んでおり、**PHPコードで内核機能を書く**ことで、下層のインフラをより安定して信頼できるものにしています。

> このモジュールはまた、composerを通じて個別にインストールすることもできます。個別にインストールする場合は、`php.ini`で`swoole.enable_library=Off`を設定して、拡張内置のライブラリを閉じる必要があります。

現在以下のツールコンポーネントを提供しています：- [Coroutine\WaitGroup](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php) は、並行コンテキストスレッドを待つために使用され、[ドキュメント](/coroutine/wait_group)- [Coroutine\FastCGI](https://github.com/swoole/library/tree/master/src/core/Coroutine/FastCGI)はFastCGIクライアントで、[ドキュメント](/coroutine_client/fastcgi)- [Coroutine\Server](https://github.com/swoole/library/blob/master/src/core/Coroutine/Server.php)は、[ドキュメント](/coroutine/server)
- [Coroutine\Barrier](https://github.com/swoole/library/blob/master/src/core/Coroutine/Barrier.php)は、[ドキュメント](/coroutine/barrier)- [CURL hook](https://github.com/swoole/library/tree/master/src/core/Curl)はCURLのコンテキスト化で、[ドキュメント](/runtime?id=swoole_hook_curl)- [Database](https://github.com/swoole/library/tree/master/src/core/Database)は、様々なデータベース接続プールとオブジェクトアドミターの高度な封装で、[ドキュメント](/coroutine/conn_pool?id=database)- [ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php)は、原始的な接続プールで、[ドキュメント](/coroutine/conn_pool?id=connectionpool)
- [Process\Manager](https://github.com/swoole/library/blob/master/src/core/Process/Manager.php)は、[ドキュメント](/process/process_manager)
- [StringObject](https://github.com/swoole/library/blob/master/src/core/StringObject.php)、[ArrayObject](https://github.com/swoole/library/blob/master/src/core/ArrayObject.php)、[MultibyteStringObject](https://github.com/swoole/library/blob/master/src/core/MultibyteStringObject.php)は、オブジェクト指向のArrayとStringプログラミング- [functions](https://github.com/swoole/library/blob/master/src/core/Coroutine/functions.php)は、提供されるいくつかのコンテキスト関数で、[ドキュメント](/coroutine/coroutine?id=函数)- [Constant](https://github.com/swoole/library/tree/master/src/core/Constant.php)は、一般的な設定常量です。
- [HTTP Status](https://github.com/swoole/library/blob/master/src/core/Http/Status.php)はHTTP状態コードです。
## 示例コード

[例](https://github.com/swoole/library/tree/master/examples)
