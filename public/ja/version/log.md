# バージョン更新記録

`v1.5`バージョンから厳格なバージョン更新記録が始まりました。現在の平均イテレーション時間は半年ごとに大きなバージョン、2〜4週間に1つの小さなバージョンです。
## 推奨されるPHPバージョン

* 8.0
* 8.1
* 8.2
* 8.3
## 推奨されるSwooleバージョン
`Swoole6.x`と`Swoole5.x`

両者の違いは、`v6.x`はアクティブなイテレーションブレンチで、`v5.x`は**非**アクティブなイテレーションブレンチで、主に`BUG`の修正のみです。

!> `v4.x`以上のバージョンでは、[enable_coroutine](/server/setting?id=enable_coroutine)を設定することで协程機能をオフにし、非协程バージョンに変えることができます。
## バージョンタイプ

* `alpha` 特性のプレビューバージョンで、開発計画中のタスクが完了し、公開プレビューが行われていますが、多くの`BUG`が存在する可能性があります。
* `beta` テストバージョンで、開発環境でのテストに使用できるようになりましたが、`BUG`が存在する可能性があります。
* `rc[1-n]` 候选リリースバージョンで、リリースサイクルに入り、大規模なテストが行われており、この期間中に`BUG`が発見される可能性があります。
* 后缀がないものは安定版を意味し、このバージョンは開発が完了し、正式に使用できるようになりました。
## 現在のバージョン情報を確認する

```shell
php --ri swoole
```
## v6.0.0
### 新機能
- `Swoole`はマルチスレッドモードをサポートし、PHPが`zts`モードで、Swooleを编译する際に`--enable-swoole-thread`をオンにすると、マルチスレッドモードを使用できます。- 新しいスレッド管理クラス`Swoole\Thread`が追加されました。 @matyhtf- 新しいスレッドロック`Swoole\Thread\Lock`が追加されました。 @matyhtf- 新しいスレッド原子計数`Swoole\Thread\Atomic`、`Swoole\Thread\Atomic\Long`が追加されました。 @matyhtf- 新しい安全な並行コンテナ`Swoole\Thread\Map`、`Swoole\Thread\ArrayList`、`Swoole\Thread\Queue`が追加されました。 @matyhtf- ファイルの非同期操作は`iouring`を底层エンジンとしてサポートし、`liburing`をインストールし、Swooleを编译する際に`--enable-iouring`をオンにすると、`file_get_contents`、`file_put_contents`、`fopen`、`fclose`、`fread`、`fwrite`、`mkdir`、`unlink`、`fsync`、`fdatasync`、`rename`、`fstat`、`lstat`、`filesize`これらの関数の非同期操作は`iouring`によって実現されます。 @matyhtf @NathanFreeman
- Boost Contextバージョンが1.84にアップグレードされました。今では、龍芯CPUでも协程が使用できるようになりました。 @NathanFreeman
### Bug修正
- `pecl`でのインストールができませんでした。 @remicollet- `Swoole\Coroutine\FastCGI\Client`クライアントのkeepaliveを設定できませんでした。 @NathanFreeman- 请求パラメータが`max_input_vars`を超えると、プロセスが絶えず再起動する错误抛出されました。 @NathanFreeman- 协程内で`Swoole\Event::wait()`を使用すると、未知の問題が発生しました。 @matyhtf- `proc_open`が协程化された時にptyをサポートしなくなりました。 @matyhtf- PHP8.3での`pdo_sqlite`はセグメント違反が発生しました。 @NathanFreeman- Swooleを编译する時の無意味な警告を修正しました。 @Appla @NathanFreeman- `STDOUT/STDERR`が既に閉じられている時に、zend_fetch_resource2_exが错误抛出されました。 @Appla @matyhtf- 無効な`set_tcp_nodelay`設定が修正されました。 @matyhtf- ファイルアップロード時に偶発的に到達不可能な分岐問題が発生しました。 @NathanFreeman- `dispatch_func`を設定すると、PHP底层が错误抛出されました。 @NathanFreeman- autoconf >= 2.70バージョンではAC_PROG_CC_C99が古く过时になりました。 @petk- スレッド作成に失敗した時に、その例外をキャッチしました。 @matyhtf- `_tsrm_ls_cache`が未定義の問題が修正されました。 @jingjingxyk
- GCC 14での编译が致命的な错误を引き起こすことが修正されました。 @remicollet
###カーネル最適化- `socket structs`の無駄なチェックを削除しました。 @petk- swoole Libraryをアップグレードしました。 @deminy- `Swoole\Http\Response`は451ステータスコードのサポートを増加させました。 @abnegate- PHPの異なるバージョン間の`file`操作コードを同期しました。 @NathanFreeman- PHPの異なるバージョン間の`pdo`操作コードを同期しました。 @NathanFreeman- `Socket::ssl_recv()`のコードを最適化しました。 @matyhtf- config.m4を最適化し、いくつかの設定は`pkg-config`を使用して依存ライブラリの位置を設定できます。 @NathanFreeman- 请求頭を解析する際に動的配列を使用する問題を最適化しました。 @NathanFreeman- マルチスレッドモードで、ファイル記述子`fd`のライフサイクル問題を最適化しました。 @matyhtf
- 协程の基本的な論理を最適化しました。 @matyhtf
### 廃止
- PHP 8.0のサポートを停止しました。- `Swoole\Coroutine\MySQL`协程クライアントのサポートを停止しました。- `Swoole\Coroutine\Redis`协程クライアントのサポートを停止しました。
- `Swoole\Coroutine\PostgreSQL`协程クライアントのサポートを停止しました。
## v5.1.3

### Bug修正：
- `pecl`でのインストールができませんでした。- `Swoole\Coroutine\FastCGI\Client`クライアントのkeepaliveを設定できませんでした。- 请求パラメータが`max_input_vars`を超えると、プロセスが絶えず再起動する错误抛出されました。- 协程内で`Swoole\Event::wait()`を使用すると、未知の問題が発生しました。- `proc_open`が协程化された時にptyをサポートしなくなりました。- PHP8.3での`pdo_sqlite`はセグメント違反が発生しました。- Swooleを编译する時の無意味な警告を修正しました。- `STDOUT/STDERR`が既に閉じられている時に、zend_fetch_resource2_exが错误抛出されました。- 無効な`set_tcp_nodelay`設定が修正されました。- ファイルアップロード時に偶発的に到達不可能な分岐問題が発生しました。- `dispatch_func`を設定すると、PHP底层が错误抛出されました。
- autoconf >= 2.70バージョンではAC_PROG_CC_C99が古く过时になりました。
###カーネル最適化：- `socket structs`の無駄なチェックを削除しました。- swoole Libraryをアップグレードしました。- `Swoole\Http\Response`は451ステータスコードのサポートを増加させました。- PHPの異なるバージョン間の`file`操作コードを同期しました。- PHPの異なるバージョン間の`pdo`操作コードを同期しました。- `Socket::ssl_recv()`のコードを最適化しました。- config.m4を最適化し、いくつかの設定は`pkg-config`を使用して依存ライブラリの位置を設定できます。 
- 请求頭を解析する際に動的配列を使用する問題を最適化しました。 
## v5.1.2
### Bug修正-嵌入式sapiのサポートが追加されました。- PHP 8.3でのZEND_CHECK_STACK_LIMITの互換性問題が修正されました。-範囲リクエストでファイル全体の内容を返却した時にContent-Rangeレスポンスヘッダがない错误が修正されました。- 切断されたcookieが修正されました。- PHP 8.3でのnative-curlのクラッシュ問題が修正されました。- Server::Manager::wait()後の無効なerrno错误が修正されました。
- HTTP2のスペルが修正されました。
  ###最適化- HTTPサーバーのパフォーマンスが最適化されました。
- websocketの有効なクローズ理由としてCLOSE_SERVICE_RESTART、CLOSE_TRY_AGAIN_LATER、CLOSE_BAD_GATEWAYが追加されました
## v5.1.1### Bug 修正- `http协程客户端`の内存泄漏問題が修正されました。- `pdo_odbc`の协程化ができませんでした。- `socket_import_stream()`の実行错误が修正されました。- `Context::parse_multipart_data()`が空のリクエストボディを処理できない問題が修正されました。- `PostgreSQL协程客户端`のパラメータが機能しない問題が修正されました。- `curl`の析構時にクラッシュするbugが修正されました。- `Swoole5.x`と新しいバージョンの`xdebug`との互換性問題が修正されました。- クラスの自動読み込み中に协程切り替えが発生し、`クラスが存在しない`という提示が出る問題を修正しました。
- OpenBSDでswooleを编译できない問題が修正されました。
## v5.1.0
### 新機能- `pdo_pgsql`の协程化サポートが追加されました- `pdo_odbc`の协程化サポートが追加されました- `pdo_oci`の协程化サポートが追加されました- `pdo_sqlite`の协程化サポートが追加されました
- `pdo_pgsql`、`pdo_odbc`、`pdo_oci`、`pdo_sqlite`の接続プール設定が追加されました
### 增强
- `Http\Server`のパフォーマンスが向上し、極限状況では最大60%向上しました
### 修复- `WebSocket`协程客户端の各リクエストによる内存泄漏が修正されました- `http协程服务端`のgraceful exitが客户端を強制的に退出させないように修正されました- 编译時に`--enable-thread-context`オプションを加えると、`Process::signal()`が機能しなくなりました- `SWOOLE_BASE`モードで、プロセスが非正常に退出した時に、接続数の統計が間違ってしまいます- `stream_select()`関数のシグネチャーが間違っていました- ファイルMIME情報の大小写が敏感であるerrorが修正されました- `Http2\Request::$usePipelineRead`のスペルが間違っており、PHP8.2の環境で警告が抛出されるようになりました- `SWOOLE_BASE`モードでの内存泄漏が修正されました- `Http\Response::cookie()`がcookieの有効期限を設定すると、内存泄漏が発生する問題が修正されました
- `SWOOLE_BASE`モードでの接続漏れが修正されました
### 内核- swooleがphp8.3でphp_url_encodeの関数シグネチャーが間違っていました- 单元测试オプションの問題が修正されました- コードを最適化し、再構築しました- PHP8.3との互換性
- 32位オペレーティングシステムでの编译をサポートしていません
##  v5.0.3
### 增强- `--with-nghttp2_dir`オプションが追加され、システム内の`nghttp2`ライブラリを使用できます- byte lengthまたはsizeに関連するオプションがサポートされました- `Process\Pool::sendMessage()`関数が追加されました
- `Http\Response:cookie()`は`max-age`をサポートします
### 修复
- `Server task/pipemessage/finish`イベントが内存泄漏を引き起こすことが修正されました
### 内核- `http`レスポンスヘッダの衝突ではerrorを抛出しません
- `Server`の接続閉鎖ではerrorを抛出しません
## v5.0.2
### 增强- `http2`のデフォルト設定を構成可能にしました- 8.1またはそれ以上のバージョンの`xdebug`をサポートしました- 原生のcurlを再構築し、複数のsocketを持つcurlハンドラ（例えばcurl FTPプロトコル）をサポートしました- `Process::setPriority/getPriority`に`who`パラメータが追加されました- `Coroutine\Socket::getBoundCid()`メソッドが追加されました- `Coroutine\Socket::recvLine/recvWithBuffer`メソッドの`length`パラメータのデフォルト値を`65536`に調整しました- 跨协程退出特性を再構築し、内存解放がより安全になり、致命的なerrorが発生した時のクラッシュ問題を解決しました- `Coroutine\Client`、`Coroutine\Http\Client`、`Coroutine\Http2\Client`に`socket`属性が追加され、直接socketリソースを操作できるようになりました- `Http\Server`が`http2`クライアントに空のファイルを送信可能にしました- `Coroutine\Http\Server`のgraceful restartをサポートします。サーバーが閉鎖された時、クライアントの接続は強制的に閉じられず、新しいリクエストの受信を停止するだけです- `pcntl_rfork`と`pcntl_sigwaitinfo`を不安全関数リストに追加し、协程コンテナが起動される時に閉じられます
- `SWOOLE_BASE`モードのプロセスマネージャーを再構築し、クローズとリロードの行動が`SWOOLE_PROCESS`と一致しました
##  v5.0.1
### 增强- PHP-8.2のサポートが追加され、协程の例外処理が改善され、`ext-soap`との互換性が高まりました- `pgsql`协程クライアントのLOBサポートが追加されました- `websocket`クライアントが改良され、HTTP頭に`websocket`が含まれるようになり、等号を使用しなくなりました- `http client`が最適化され、サーバーが`connection close`を送信した時に`keep-alive`を無効にしました- 圧縮ライブラリがない場合、`Accept-Encoding`頭を追加することが禁止されました- 调试情報が最適化され、PHP-8.2ではパスワードを敏感なパラメータとして設定しました- `Server::taskWaitMulti()`が強化され、协程環境下でのブロックがなくなりました
- ログ関数が最適化され、ログファイルに書き込む失败時に画面に印刷されなくなりました

### 修复- `Coroutine::printBackTrace()`と`debug_print_backtrace()`のパラメータ互換性问题が修正されました- `Event::add()`がsocketリソースをサポートするように修正されました- `zlib`がない時の编译错误が修正されました- 解析が予期しない文字列になった時にサーバータスクを解包するとクラッシュする問題が修正されました- 1ms未満のタイマーを追加すると強制的に0に設定される問題が修正されました- 列を追加する前に`Table::getMemorySize()`を使用するとクラッシュする問題が修正されました
- `Http\Response::setCookie()`メソッドの过期パラメータ名を`expires`に変更しました
## v5.0.0
### 新機能- `Server`に`max_concurrency`オプションが追加されました- `Coroutine\Http\Client`に`max_retries`オプションが追加されました- `name_resolver`グローバルオプションが追加されました。 `Server`に`upload_max_filesize`オプションが追加されました- `Coroutine::getExecuteTime()`メソッドが追加されました- `Server`に`SWOOLE_DISPATCH_CONCURRENT_LB`の`dispatch_mode`が追加されました- 型システムが強化され、すべての関数のパラメータと戻り値に型が追加されました- 错误処理が最適化され、すべてのコンストラクタが失敗した時に例外が抛出されました- `Server`のデフォルトモードが調整され、デフォルトで`SWOOLE_BASE`モードになります
- `pgsql`协程クライアントをコアライブラリに移行しました。 4.8.xブランチのすべてのbug修正が含まれています
### 移除- `PSR-0`スタイルのクラス名が移除されました- 関数を閉じる時に自動的に`Event::wait()`を追加する機能が移除されました- `Server::tick/after/clearTimer/defer`のアリガイが移除されました
- `--enable-http2/--enable-swoole-json`を移除し、デフォルトで有効にしました
### 废弃
- 协程クライアント`Coroutine\Redis`と`Coroutine\MySQL`はデフォルトで廃止されました
## v4.8.13
### 增强- 原生のcurlを再構築し、複数のsocketを持つcurlハンドラ（例えばcurl FTPプロトコル）をサポートしました- `http2`設定を手動で設定可能にしました- `WebSocket客户端`を改良し、HTTP頭に`websocket`が含まれるようになり、等号を使用しなくなりました- HTTPクライアントを最適化し、サーバーが接続閉鎖を送信した時に`keep-alive`を無効にしました- HTTPクライアントを最適化し、圧縮ライブラリがない場合、`Accept-Encoding`頭を追加することが禁止されました
### 修复
- `Server task/pipemessage/finish`イベントが内存泄漏を引き起こすことが修正されました
### 内核- `http`レスポンスヘッダの衝突ではerrorを抛出しません
- `Server`の接続閉鎖ではerrorを抛出しません
- PHP-8.2 下でパスワードを sensitivなパラメータとして設定するように改善
- HTTP Range Requests をサポート
### 修复
- Coroutine::printBackTrace() および debug_print_backtrace() のパラメータ互換性の問題を修正
- WebSocket サーバーで HTTP2 および WebSocketプロトコルを同時に有効にしたときに長さを解析する際のエラーを修正
- Server::send()、Http\Response::end()、Http\Response::write()、WebSocket/Server::push() で send_yieldが発生したときにメモリ漏洩の問題を修正
- 列を追加する前に Table::getMemorySize() を使用するとクラッシュする問題を修正
## v4.8.12
### 增强
- PHP8.2 支持 - Event::add() 函数が sockets resources をサポート
- Http\Client::sendfile()が4Gを超えるファイルをサポート
- Server::taskWaitMulti()が协程環境をサポート
### 修复
- 誤った multipart body を受信するとエラー情報を抛出する問題を修正
- 定時器のタイムアウト時間が 1ms 未満の場合に引き起こされるエラーを修正
- ディスクが満ちているために死锁が発生する問題を修正
## v4.8.11
### 增强
- Intel CET 安全防御機構をサポート
- Server::$ssl 属性を追加
- peclでswooleをコンパイルする際に enable-cares 属性を追加
- multipart_parser 解釈器を再構築
### 修复
- pdo 永続接続が例外を投げたときにセグメントフォルトが発生する問題を修正
- 解構関数が协程を使用するとセグメントフォルトが発生する問題を修正
- Server::close()の誤ったエラー情報を修正
## v4.8.10
### 修复
- stream_selectのタイムアウトパラメータが1ms未満の場合、それを0にリセットする
- compile時に-Werror=format-securityを加えるとコンパイルに失敗する問題を修正
- curlを使用するとSwoole\Coroutine\Http\Serverでセグメントフォルトが発生する問題を修正
## v4.8.9
### 增强
- Http2 サーバーでの http_auto_index オプションをサポート
### 修复
- Cookie 解析器を最適化し、HttpOnlyオプションを受け入れることをサポート
- #4657を修正し、socket_create 方法の戻り値タイプの問題を解決
- stream_selectのメモリ漏洩を修正
### CLI 更新
- CygWin 下で SSL 証明書チェーンを携帯し、SSL 認証エラーを解決
- PHP-8.1.5に更新
## v4.8.8
### 优化
- SW_IPC_BUFFER_MAX_SIZE を 64kに減少させる
- http2のheader_table_size設定を最適化
### 修复
- enable_static_handlerを使用して静的なファイルをダウンロードすると多くのソケットエラーが発生する問題を修正
- http2 server NPNエラーを修正
## v4.8.7
### 增强
- curl_share 支持を追加
### 修复
- arm32アーキテクチャでの未定義記号エラーを修正
- clock_gettime()の互換性问题を修正
- 内核が大規模なメモリを欠いている場合、PROCESSモードのサーバーがデータを送信失败する問題を修正
## v4.8.6
### 修复
- boost/context API名にプレフィックスを追加
- 配置オプションを最適化
## v4.8.5
### 修复
- Tableのパラメータタイプを元に戻す
- Websocketプロトコルを使用して誤ったデータを受信するとクラッシュする問題を修正
## v4.8.4
### 修复
- sockets hookとPHP-8.1の互換性问题を修正
- TableとPHP-8.1の互換性问题を修正
- コルテックススタイルのHTTPサーバーがContent-Typeをapplication/x-www-form-urlencodedとして解析するPOSTパラメータが期待通りにならない問題を修正
## v4.8.3
### 新增 API
- Coroutine\Socket::isClosed() 方法を追加
### 修复
- curl native hookがphp8.1バージョンでの互換性问题
- sockets hookがphp8での互換性问题
- sockets hookの関数戻り値が間違っている問題
- Http2Server sendfileでcontent-typeを設定できない問題
- HttpServer date headerのパフォーマンスを最適化し、cacheを追加
## v4.8.2
### 修复
- proc_open hookのメモリ漏洩問題を修正
- curl native hookがPHP-8.0、PHP-8.1での互換性问题
- Managerプロセスで接続を正常に閉じることができない問題を修正
- ManagerプロセスがsendMessageを使用できない問題を修正
- Coroutine\Http\Serverが超大POSTデータの解析に異常が発生する問題を修正
- PHP8環境下での致命的なエラー時に直接退出できない問題を修正
- coroutine `max_concurrency`設定項を調整し、Co::set()でのみ使用することを許可
- Coroutine::join()で存在しないコルテックスを無視するように調整
## v4.8.1
### 新增 API
- swoole_error_log_ex()とswoole_ignore_error()関数を追加 (#4440) (@matyhtf)
### 增强
- ext-swoole_plusのadmin apiをext-swooleに移行 (#4441) (@matyhtf)
- admin serverにget_composer_packagesコマンドを追加 (swoole/library@07763f46) (swoole/library@8805dc05) (swoole/library@175f1797) (@sy-records) (@yunbaoi)
- write操作のPOST方法のrequest limitを追加 (swoole/library@ac16927c) (@yunbaoi)
- admin serverがクラスの方法情報を取得することをサポート (swoole/library@690a1952) (@djw1028769140) (@sy-records)
- admin serverのコードを最適化 (swoole/library#128) (swoole/library#131) (@sy-records)
- admin serverが複数のターゲットに同時并发请求し、複数のAPIに同時并发请求することをサポート (swoole/library#124) (@sy-records)
- admin serverがインターフェース情報を取得することをサポート (swoole/library#130) (@sy-records)
- SWOOLE_HOOK_CURLがCURLOPT_HTTPPROXYTUNNELをサポート (swoole/library#126) (@sy-records)
### 修复
- join方法で同じコルテックスを同時に并发して呼び出さないように (#4442) (@matyhtf)
- Table原子ロックが意図せずに解放される問題を修正 (#4446) (@Txhua) (@matyhtf)
- 丢失したhelper options (swoole/library#123) (@sy-records)
- get_static_property_valueコマンドのパラメータが間違っている問題を修正 (swoole/library#129) (@sy-records)
## v4.8.0
### 向下不兼容改动
- baseモードでは、onStart回调が常に最初のワークプロセス(worker idが0)の起動時に回调し、onWorkerStartよりも先に実行される (#4389) (@matyhtf)
### 新增 API
- Co::getStackUsage() 方法 (#4398) (@matyhtf) (@twose)
- Coroutine\RedisのいくつかのAPI (#4390) (@chrysanthemum)
- Table::stats() 方法 (#4405) (@matyhtf)
- Coroutine::join() 方法 (#4406) (@matyhtf)
### 新增功能
- server command 支持 (#4389) (@matyhtf)
- Server::onBeforeShutdownイベント回调 (#4415) (@matyhtf)
### 增强
- Websocket packが失敗したときにエラーコードを設定 (#4394) (@twose) (@matyhtf)
- Timer::exec_countフィールドを追加 (#4402) (@matyhtf)
- hook mkdirがopen_basedir ini設定を使用することをサポート (#4407) (@NathanFreeman)
- libraryにvendor_init.phpスクリプトを追加 (swoole/library@6c40b02) (@matyhtf)
- SWOOLE_HOOK_CURLがCURLOPT_UNIX_SOCKET_PATHをサポート (swoole/library#121) (@sy-records)
- Clientがssl_ciphers設定項目を設定することをサポート (#4432) (@amuluowin)
- Server::stats()にいくつかの新しい情報追加 (#4410) (#4412) (@matyhtf)
### 修复
- ファイルアップロード時に、ファイル名に対して不要なURLデコードを行う問題を修正 (swoole/swoole-src@a73780e) (@matyhtf)
- HTTP2 max_frame_size問題 (#4394) (@twose)
- curl_multi_select bug #4393 (#4418) (@matyhtf)
- 丢失したcoroutine options (#4425) (@sy-records)
- 送信バッファが満タンになったときに、接続をcloseできない問題を修正 (swoole/swoole-src@2198378) (@matyhtf)
## v4.7.1
### 增强
- System::dnsLookupが/etc/hostsの照会をサポート (#4341) (#4349) (@zmyWL) (@NathanFreeman)
- mips64のboost context支持を追加 (#4358) (@dixyes)
- SWOOLE_HOOK_CURLがCURLOPT_RESOLVEオプションをサポート (swoole/library#107) (@sy-records)
- SWOOLE_HOOK_CURLがCURLOPT_NOPROGRESSオプションをサポート (swoole/library#117) (@sy-records)
- riscv64のboost context支持を追加 (#4375) (@dixyes)
### 修复
- PHP-8.1のon shutdown時に発生するメモリエラーを修正 (#4325) (@twose)
- 8.1.0beta1の非序列化クラスを修正 (#4335) (@remicollet)
- 複数のコルテックスが再帰的にディレクトリを作成失败する問題を修正 (#4337) (@NathanFreeman)
- native curlが外网で大ファイルを送信する際に偶発的にタイムアウトし、CURL WRITEFUNCTIONの中でコルテックスファイルAPIを使用するとクラッシュする問題を修正 (#4360) (@matyhtf)
- PDOStatement::bindParam()で期待されるパラメータ1が文字列である問題 (swoole/library#116) (@sy-records)
## v4.7.0
### 新增 API
- Process\Pool::detach() 方法 (#4221) (@matyhtf)
- ServerがonDisconnect回调関数をサポート (#4230) (@matyhtf)
- Coroutine::cancel()とCoroutine::isCanceled()方法を追加 (#4247) (#4249) (@matyhtf)
- Http\Clientがhttp_compressionとbody_decompressionオプションをサポート (#4299) (@matyhtf)
### 增强
- コルテックスMySQLクライアントがprepare時にフィールドの厳格なタイプをサポート (#4238) (@Yurunsoft)
- DNSがc-aresライブラリをサポート (#4275) (@matyhtf)
- Serverが多ポート监听時に異なるポートにheartbeats检测時間を設定することをサポート (#4290) (@matyhtf)
- Serverのdispatch_modeがSWOOLE_DISPATCH_CO_CONN_LBとSWOOLE_DISPATCH_CO_REQ_LBモードをサポート (#4318) (@matyhtf)
- ConnectionPool::get()がtimeoutパラメータをサポート (swoole/library#108) (@leocavalcante)
- Hook CurlがCURLOPT_PRIVATEオプションをサポート (swoole/library#112) (@sy-records)
- PDOStatementProxy::setFetchMode()の関数宣言を最適化 (swoole/library#109) (@yespire)
### 修复
- 线程上下文を使用しているときに、大量のコルテックスを作成すると、スレッドを生成できない例外が発生する問題を修正 (8ce5041) (@matyhtf)
- Swooleをインストールする際にphp_swoole.hヘッダーファイルが丢失する問題を修正 (#4239) (@sy-records)
- EVENT_HANDSHAKEが向下互換性がない問題を修正 (#4248) (@sy-records)
- SW_LOCK_CHECK_RETURNマクロが関数を2回呼び出す可能性がある問題を修正 (#4302) (@zmyWL)
- M1チップ下でのAtomic\Longの問題を修正 (e6fae2e) (@matyhtf)
- Coroutine\go()が戻り値を丢失する問題を修正 (swoole/library@1ed49db) (@matyhtf)
- StringObjectの戻り値タイプの問題を修正 (swoole/library#111) (swoole/library#113) (@leocavalcante) (@sy-records)
### 内核
- PHPで既に禁用されている関数へのHookを禁止 (#4283) (@twose)
### 测试
- Cygwin環境でのビルドを追加 (#4222) (@sy-records)
- alpine 3.13と3.14のコンパイルテストを追加 (#4309) (@limingxinleo)
## v4.6.7
### 增强
- ManagerプロセスとTask同期プロセスがProcess::signal()関数を呼び出すことをサポート (#4190) (@matyhtf)
### 修复
-シグナルが重複して登録できない問題を修正 (#4170) (@matyhtf)
- OpenBSD/NetBSDでのコンパイルに失敗する問題を修正 (#4188) (#4194) (@devnexen)
- 可写イベントを监听しているときに、onCloseイベントが丢失する特殊な状況を修正 (#4204) (@matyhtf)
- Symfony HttpClientがnative curlを使用する問題 (#4204) (@matyhtf)
- Http\Response::end()方法が常にtrueを返す問題を修正 (swoole/swoole-src@66fcc35) (@matyhtf)
- PDOStatementProxyが生成するPDOExceptionを修正 (swoole/library#104) (@twose)
### 内核
- worker bufferを再構築し、event dataにmsg idフラグを追加 (#4163) (@matyhtf)
- Request Entity Too Largeのログレベルをwarningに変更 (#4175) (@sy-records)
- inet_ntoaとinet_aton関数を置き換える (#4199) (@remicollet)
- output_buffer_sizeのデフォルト値をUINT_MAXに変更 (swoole/swoole-src@46ab345) (@matyhtf)
## v4.6.6
### 增强
- FreeBSD下でのMasterプロセスが退出した後、ManagerプロセスにSIGTERMシグナルを送信することをサポート (#4150) (@devnexen)
- SwooleをPHPに静的にコンパイルすることをサポート (#4153) (@matyhtf)
- SNIがHTTPプロキシを使用することをサポート (#4158) (@matyhtf)
### 修复
- 同期クライアントの非同期接続の誤りを修正 (#4152) (@matyhtf)
- Hook原生curl multiによるメモリ漏洩を修正 (swoole/swoole-src@91bf243) (@matyhtf)
## v4.6.5
### 新增 API
- WaitGroupにcount方法を追加 (swoole/library#100) (@sy-records) (@deminy)
### 增强
- native curl multiをサポート (#4093) (#4099) (#4101) (#4105) (#4113) (#4121) (#4147) (swoole/swoole-src@cd7f51c) (@matyhtf) (@sy-records) (@huanghantao)
- HTTP/2のResponseで配列を使用してheadersを設定することを許可
### 修复
- NetBSDの構築を修正 (#4080) (@devnexen)
- OpenBSDの構築を修正 (#4108) (@devnexen)
- illumos/solarisの構築で、メンバー別名のみ (#4109) (@devnexen)
- Handshakeが完了していないときに、SSL接続のheartbeats检测が機能しない問題を修正 (#4114) (@matyhtf)
- Http\Clientがプロキシを使用する際にhost中存在host:portが原因でエラーが発生する問題を修正 (#4124) (@Yurunsoft)
- Swoole\Coroutine\Http::requestでheaderとcookieの設定を修正 (swoole/library#103) (@leocavalcante) (@deminy)
### 内核
- BSD上のasm contextをサポート (#4082) (@devnexen)
- FreeBSD下でarc4random_bufを使用してgetrandomを実現 (#4096) (@devnexen)
- darwin arm64 contextの最適化: workaround labelの使用を削除 (#4127) (@devnexen)
### テスト
- alpine 用のビルドスクリプトを追加 (#4104) (@limingxinleo)
## v4.6.4
### 新しい API
- Coroutine\Http::request, Coroutine\Http::post, Coroutine\Http::get 関数を追加 (swoole/library#97) (@matyhtf)
### 增强- ARM 64 ビルドをサポート (#4057) (@devnexen)- Swoole TCP サーバーで open_http_protocol を設定可能に (#4063) (@matyhtf)- ssl クライアントで certificate をのみ設定可能に (91704ac) (@matyhtf)
- FreeBSD 用の tcp_defer_accept オプションをサポート (#4049) (@devnexen)
### 修正- Coroutine\Http\Client 使用時のプロキシ認可問題 (edc0552) (@matyhtf)- Swoole\Table のメモリ割り当て問題 (3e7770f) (@matyhtf)- Coroutine\Http2\Client 并発接続時のクラッシュ (630536d) (@matyhtf)- DTLS の enable_ssl_encrypt 问题 (842733b) (@matyhtf)- Coroutine\Barrier メモリ漏洩 (swoole/library#94) (@Appla) (@FMiS)- CURLOPT_PORT 和 CURLOPT_URL の順序によるオフセットエラー (swoole/library#96) (@sy-records)- Table::get($key, $field) が field 类型が float 時の問題 (08ea20c) (@matyhtf)
- Swoole\Table メモリ漏洩 (d78ca8c) (@matyhtf)
## v4.4.24
### 修正
- http2 クライアント 并発接続時のクラッシュを修正 (#4079)
## v4.6.3
### 新しい API- Swoole\Coroutine\go 関数を追加 (swoole/library@82f63be) (@matyhtf)
- Swoole\Coroutine\defer 関数を追加 (swoole/library@92fd0de) (@matyhtf)
### 增强- HTTP サーバーに compression_min_length オプションを追加 (#4033) (@matyhtf)
- 应用層で Content-Length HTTP 头を設定可能に (#4041) (@doubaokun)
### 修正- ファイルオープン制限に達した時の coredump (swoole/swoole-src@709813f) (@matyhtf)- JIT を無効にする問題 (#4029) (@twose)- Response::create() 引数エラー (swoole/swoole-src@a630b5b) (@matyhtf)- ARMプラットフォームでの task 投放時の task_worker_id 誤報 (#4040) (@doubaokun)- PHP8でnative curl hookを有効にした時のcoredump (#4042)(#4045) (@Yurunsoft) (@matyhtf)
- fatal error 时の shutdown 段階のメモリ越界エラーを修正 (#4050) (@matyhtf)
### コア- ssl_connect/ssl_shutdown を最適化 (#4030) (@matyhtf)
- fatal error 时にプロセスを直接終了 (#4053) (@matyhtf)
## v4.6.2
### 新しい API- Http\Request\getMethod() 方法を追加 (#3987) (@luolaifa000)- Coroutine\Socket->recvLine() 方法を追加 (#4014) (@matyhtf)
- Coroutine\Socket->readWithBuffer() 方法を追加 (#4017) (@matyhtf)
### 增强- Response\create() 方法を強化し、Server 以外で独立して使用可能に (#3998) (@matyhtf)- Coroutine\Redis->hExistsがcompatibility_modeを設定後に bool 型を戻すように (swoole/swoole-src@b8cce7c) (@matyhtf)
- socket_readで PHP_NORMAL_READ 选项を設定可能に (swoole/swoole-src@b1a0dcc) (@matyhtf)
### 修正- Coroutine::deferが PHP8 下で coredump する問題を修正 (#3997) (@huanghantao)- thread context 使用時に Coroutine\Socket::errCode を誤って設定する問題を修正 (swoole/swoole-src@004d08a) (@matyhtf)- 最新の macos 下での Swoole 编译失败問題を修正 (#4007) (@matyhtf)
- md5_file 引数に url 被入れた時に php stream context 为 null 指针の問題を修正 (#4016) (@ZhiyangLeeCN)
### コア- AIO スレッドプール hook stdio (以前は stdio を socket として扱ってしまい、多くの协程 read/write 问题を引き起こしていた) (#4002) (@matyhtf)- HttpContext を再構築 (#3998) (@matyhtf)
- Process::wait() を再構築 (#4019) (@matyhtf)
## v4.6.1
### 增强- --enable-thread-context 编译オプションを追加 (#3970) (@matyhtf)- session_id 操作時に接続が存在するかを確認 (#3993) (@matyhtf)
- CURLOPT_PROXY を強化 (swoole/library#87) (@sy-records)
### 修正- pecl 安装時の最小 PHP 版本 (#3979) (@remicollet)- pecl 安装時に --enable-swoole-json 和 --enable-swoole-curl オプションがない (#3980) (@sy-records)- openssl 线程安全問題 (b516d69f) (@matyhtf)
- enableSSL coredump を修正 (#3990) (@huanghantao)
### コア
- ipc writev を最適化し、イベントデータ为空の場合でも coredump 生成しないように (9647678) (@matyhtf)
## v4.5.11
### 增强- Swoole\Table を最適化 (#3959) (@matyhtf)
- CURLOPT_PROXY を強化 (swoole/library#87) (@sy-records)
### 修正- Table 递增/递减時にすべての列をクリアできない問題 (#3956) (@matyhtf) (@sy-records)- 编译時に発生する `clock_id_t` 错误 (49fea171) (@matyhtf)- fread bugs を修正 (#3972) (@matyhtf)- ssl 多线程 crash を修正 (7ee2c1a0) (@matyhtf)- uri 格式错误导致报错 Invalid argument supplied for foreach (swoole/library#80) (@sy-records)
- trigger_error 引数错误を修正 (swoole/library#86) (@sy-records)
## v4.6.0
### 向下不兼容改动- `session id`の最大制限を移除し、もう重複しない (#3879) (@matyhtf)- 协程を使用时不安全機能禁用、包括`pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait` (#3880) (@matyhtf)
- 默认で coroutine hook を有効にする (#3903) (@matyhtf)
### 移除
- PHP7.1 支持を不再支持 (#3879) (9de8d9e) (@matyhtf)
### 废弃
- Event::rshutdown() を廃止し、Coroutine\run を使うよう変更 (#3881) (@matyhtf)
### 新增 API- setPriority/getPriority を支持 (#3876) (@matyhtf)- native-curl hook を支持 (#3863) (@matyhtf) (@huanghantao)- Server 事件回调函数で object style 引数を传递可能に、默认は object style 引数を传递しない (#3888) (@matyhtf)- sockets 扩展 hook を支持 (#3898) (@matyhtf)- 重复 header を支持 (#3905) (@matyhtf)- SSL sni を支持 (#3908) (@matyhtf)- stdio hook を支持 (#3924) (@matyhtf)- stream_socket の capture_peer_cert 选项を支持 (#3930) (@matyhtf)- Http\Request::create/parse/isCompleted を追加 (#3938) (@matyhtf)
- Http\Response::isWritable (db56827) (@matyhtf)
### 增强- Server のすべての時間精度を int から double に変更 (#3882) (@matyhtf)- swoole_client_select 函数内で poll 函数的 EINTR 状況をチェック (#3909) (@shiguangqi)- 协程死锁检测を追加 (#3911) (@matyhtf)- SWOOLE_BASE modeで別のプロセスで接続を閉じることを支持 (#3916) (@matyhtf)
- Server master 进程と worker 进程の通信性能を最適化し、メモリコピーを減らす (#3910) (@huanghantao) (@matyhtf)
### 修复- Coroutine\Channelが閉じられた時に、中のデータを全部pop出来なくなりました (960431d) (@matyhtf)- JIT 使用時のメモリ错误を修复 (#3907) (@twose)- `port->set()` dtls 编译错误を修复 (#3947) (@Yurunsoft)- connection_list 错误を修复 (#3948) (@sy-records)- ssl verify 错误を修复 (#3954) (@matyhtf)- Table 递增和递减时不能清除所有列问题 (#3956) (@matyhtf) (@sy-records)- LibreSSL 2.7.5 编译失败を修复 (#3962) (@matyhtf)
- 未定义の常量 CURLOPT_HEADEROPT 和 CURLOPT_PROXYHEADER (swoole/library#77) (@sy-records)
### 内核- 默认で SIGPIPE 信号を無視 (#9647678) (@matyhtf)- PHP 协程と C 协程を同時に実行することを支持 (c94bfd8) (@matyhtf)- get_elapsed 测试を追加 (#3961) (@luolaifa000)
- get_init_msec 测试を追加 (#3964) (@luffluo)
## v4.5.10
### 修复- Event::cycle 使用時に発生する coredump (93901dc) (@matyhtf)- PHP8 兼容 (f0dc6d3) (@matyhtf)
- connection_list 错误を修复 (#3948) (@sy-records)
## v4.4.23
### 修复- Swoole\Table 自减时数据错误 (bcd4f60d)(0d5e72e7) (@matyhtf)- 同步客户端错误信息 (#3784)- 表单数据边界解析時の内存溢出错误修复 (#3858)
- channelのbugを修复し、关闭後に既にpopされたデータがpopされない问题
## v4.5.9
### 增强
- Coroutine\Http\ClientにSWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED 常量を追加 (#3873) (@sy-records)
### 修复- PHP8 兼容 (#3868) (#3869) (#3872) (@twose) (@huanghantao) (@doubaokun)- 未定义の常量 CURLOPT_HEADEROPT 和 CURLOPT_PROXYHEADER (swoole/library#77) (@sy-records)
- CURLOPT_USERPWD (swoole/library@7952a7b) (@twose)
## v4.5.8
### 新增 API- swoole_error_log 函数を追加し、log_rotationを优化 (swoole/swoole-src@67d2bff) (@matyhtf)- readVector 和 writeVectorが SSL を支持 (#3857) (@huanghantao)
### 增强- 子进程退出後、System::waitが阻塞するよう修正 (#3832) (@matyhtf)- DTLSが16Kの包を支持 (#3849) (@matyhtf)- Response::cookie 方法に priority 参数を支持 (#3854) (@matyhtf)-より多くの CURL 选项を支持 (swoole/library#71) (@sy-records)- CURL HTTP headerの名前大小写を区別しない問題修复 (swoole/library#76) (@filakhtov) (@twose) (@sy-records)
- readv_all 和 writev_all 错误处理 EAGAIN 的问题を修复 (#3830) (@huanghantao)- PHP8 编译警告问题修复 (swoole/swoole-src@03f3fb0) (@matyhtf)- Swoole\Table 二进制安全问题修复 (#3842) (@twose)- MacOS 下 System::writeFile 追加文件覆盖问题修复 (swoole/swoole-src@a71956d) (@matyhtf)- CURLの CURLOPT_WRITEFUNCTION (swoole/library#74) (swoole/library#75) (@sy-records)- HTTP form-data 解析時の内存溢出修复 (#3858) (@twose)
- PHP8 中 `is_callable()`が类私有方法にアクセスできない问题修复 (#3859) (@twose)
### 内核- 内存分配関数を再構築し、SwooleG.std_allocatorを使用 (#3853) (@matyhtf)- pipeを再構築 (#3841) (@matyhtf)
## v4.5.7
### 新增 API
- Coroutine\Socket 客户端に writeVector, writeVectorAll, readVector, readVectorAll 方法を追加 (#3764) (@huanghantao)
### 增强- server->statsに task_worker_num 和 dispatch_countを追加 (#3771) (#3806) (@sy-records) (@matyhtf)- json, mysqlnd, sockets 扩展依赖项を追加 (#3789) (@remicollet)- server->bindのuid最小値を INT32_MINに制限 (#3785) (@sy-records)- swoole_substr_json_decodeに编译选项を追加し、负偏移量を支持 (#3809) (@matyhtf)
- CURLの CURLOPT_TCP_NODELAY 选项を支持 (swoole/library#65) (@sy-records) (@deminy)
### 修复- 同步客户端连接信息错误 (#3784) (@twose)- hook scandir 函数问题修复 (#3793) (@twose)
- 协程屏障 barrier 中の错误修复 (swoole/library#68) (@sy-records)
### 内核
- boost.stacktraceを使用して print-backtraceを优化 (#3788) (@matyhtf)
## v4.5.6
### 新增 API
- [swoole_substr_unserialize](/functions?id=swoole_substr_unserialize) 和 [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode) を追加 (#3762) (@matyhtf)
### 增强
- Coroutine\Http\Serverの `onAccept` 方法を私有に変更 (dfcc83b) (@matyhtf)
### 修复- coverity 问题修复 (#3737) (#3740) (@matyhtf)- Alpine 环境下の問題修复 (#3738) (@matyhtf)- swMutex_lockwait (0fc5665) (@matyhtf)
- PHP-8.1 安装失败修复 (#3757) (@twose)
### 内核- Socket::read/write/shutdownに活性检测を追加 (#3735) (@matyhtf)- session_id 和 task_idの类型を int64に変更 (#3756) (@matyhtf)
## v4.5.5

!> 此版本增加了[配置项](/server/setting)检测功能，如果设置了不是Swoole提供的选项，会产生一个Warning。

```shell
PHP Warning:  unsupported option [foo] in @swoole-src/library/core/Server/Helper.php 
```

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->set(['foo' => 'bar']);

$http->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});

$http->start();
```
### 新增 API- Process\Managerを追加し、Process\ProcessManagerを别名に変更 (swoole/library#eac1ac5) (@matyhtf)- HTTP2 サーバー GOAWAY 支持 (#3710) (@doubaokun)
- Co\map() 函数を追加 (swoole/library#57) (@leocavalcante)
### 增强 - HTTP/2 Unixソケットクライアントをサポート (#3668) (@sy-records) - Workerプロセスが終了した後、Workerプロセスの状態をSW_WORKER_EXITに設定 (#3724) (@matyhtf) - `Server::getClientInfo()`の戻り値にsend_queued_bytesとrecv_queued_bytesを追加 (#3721) (#3731) (@matyhtf) (@Yurunsoft)
- Serverはstats_file設定オプションをサポート (#3725) (@matyhtf) (@Yurunsoft)
### 修复 - PHP8でのビル드問題を修正 (zend_compile_string変更) (#3670) (@twose) - PHP8でのビル드問題を修正 (ext/sockets互換性) (#3684) (@twose) - PHP8でのビル드問題を修正 (php_url_encode_hash_ex変更) (#3713) (@remicollet) - 'const char*'から'char*'への型変換エラーを修正 (#3686) (@remicollet) - HTTP/2クライアントがHTTPプロキシ下で機能しない問題を修正 (#3677) (@matyhtf) (@twose) - PDOの切断再接続時にデータが混乱する問題を修正 (swoole/library#54) (@sy-records) - UDP ServerがIPv6を使用した时的なポート解析エラーを修正
- Lock::lockwaitのタイムアウト無効の問題を修正
## v4.5.4
### 下向きの互換性のない変更 - SWOOLE_HOOK_ALLにはSWOOLE_HOOK_CURLを含める (#3606) (@matyhtf)
- ssl_methodを廃止し、ssl_protocolsを追加 (#3639) (@Yurunsoft)
### 新しいAPI
- 配列のfirstKeyとlastKeyメソッドを追加 (swoole/library#51) (@sy-records)
### 增强
- Websocketサーバーにopen_websocket_ping_frame, open_websocket_pong_frame設定項目を追加 (#3600) (@Yurunsoft)
### 修复 - ファイルが2Gを超える場合にfseek, ftellが正しく機能しない問題を修正 (#3619) (@Yurunsoft) - Socket barrierの問題を修正 (#3627) (@matyhtf) - HTTPプロキシハンドシェイクの問題を修正 (#3630) (@matyhtf) - 相手側がチャンクデータを送信した時にHTTPヘッダ解析エラーが発生する問題を修正 (#3633) (@matyhtf) - zend_hash_cleanの断言失敗問題を修正 (#3634) (@twose) - イベントループから壊れたfdを移除できない問題を修正 (#3650) (@matyhtf) - 无效なパケットを受け取った時にcoredumpが発生する問題を修正 (#3653) (@matyhtf)
- array_key_lastのbugを修正 (swoole/library#46) (@sy-records)
###カーネル - コード最適化 (#3615) (#3617) (#3622) (#3635) (#3640) (#3641) (#3642) (#3645) (#3658) (@matyhtf) - Swoole Tableにデータを書き込む時に不要なメモリ操作を減らす (#3620) (@matyhtf) - AIOを再構築 (#3624) (@Yurunsoft) - readlink/opendir/readdir/closedirのhookをサポート (#3628) (@matyhtf)
- swMutex_createを最適化し、SW_MUTEX_ROBUSTをサポート (#3646) (@matyhtf)
## v4.5.3
### 新しいAPI
- `Swoole\Process\ProcessManager`を追加 (swoole/library#88f147b) (@huanghantao) - ArrayObject::append, StringObject::equalsを追加 (swoole/library#f28556f) (@matyhtf) - [Coroutine::parallel](/coroutine/coroutine?id=parallel)を追加 (swoole/library#6aa89a9) (@matyhtf)
- [Coroutine\Barrier](/coroutine/barrier)を追加 (swoole/library#2988b2a) (@matyhtf)
### 增强
- http2クライアントのストリームをサポートするためにusePipelineReadを追加 (#3354) (@twose) - httpクライアントがファイルを受信する前にファイルを作成しない (#3381) (@twose) - httpクライアントが`bind_address`と`bind_port`の設定をサポート (#3390) (@huanghantao) - httpクライアントが`lowercase_header`の設定をサポート (#3399) (@matyhtf) - `Swoole\Server`が`tcp_user_timeout`の設定をサポート (#3404) (@huanghantao) - `Coroutine\Socket`にイベントバリアを追加してコーンキューを減少させる (#3409) (@matyhtf) - 特定のswStringに`memory allocator`を追加 (#3418) (@matyhtf) - cURLが`__toString`をサポート (swoole/library#38) (@twose) - WaitGroupのコンストラクタで直接`wait count`を設定をサポート (swoole/library#2fb228b8) (@matyhtf) - `CURLOPT_REDIR_PROTOCOLS`を追加 (swoole/library#46) (@sy-records) - http1.1サーバーがtrailerをサポート (#3485) (@huanghantao) - コーoutineのsleep時間が1ms未満の場合、現在のコーンをyieldする (#3487) (@Yurunsoft) - http static handlerがシンボリックリンクのファイルをサポート (#3569) (@LeiZhang-Hunter) - Serverがclose方法を実行した後、すぐにWebSocket接続を閉じる (#3570) (@matyhtf) - stream_set_blockingのhookをサポート (#3585) (@Yurunsoft) - 异步HTTP2サーバーがストリーム制御をサポート (#3486) (@huanghantao) (@matyhtf)
- onPackage回调関数が実行された後でsocket bufferを解放 (#3551) (@huanghantao) (@matyhtf)
### 修复 - WebSocketのcoredumpを修复し、プロトコルエラーの状態を処理 (#3359) (@twose) - swSignalfd_setup関数およびwait_signal関数内のnullポインタエラーを修复 (#3360) (@twose) - dispatch_funcを設定した後、Swoole\Server::closeを呼び出すときにエラーが発生する問題を修复 (#3365) (@twose) - swoole\Redis\Server::format関数中のformat_buffer初期化問題を修复 (#3369) (@matyhtf) (@twose) - MacOSでMACアドレスを取得できない問題を修复 (#3372) (@twose) - MySQLテストケースを修复 (#3374) (@qiqizjl) - 多々のPHP8互換性問題 (#3384) (#3458) (#3578) (#3598) (@twose) - hookのsocket write中にphp_error_docref, timeout_event、および戻り値が丢失する問題を修复 (#3383) (@twose) - 异步ServerがWorkerStart回调関数内でServerを閉じることができない問題を修复 (#3382) (@huanghantao) - ハートビート线程がconn->socketを操作している時にcoredumpが発生する可能性のある問題を修复 (#3396) (@huanghantao) - send_yieldの論理問題を修复 (#3397) (@twose) (@matyhtf) - Cygwin64上のビル드問題を修复 (#3400) (@twose) - WebSocketのfinish属性が無効である問題を修复 (#3410) (@matyhtf) - MySQL transactionエラー状態が丢失している問題を修复 (#3429) (@twose) - hook後のstream_selectとhook前の戻り値の挙動が一致しない問題を修复 (#3440) (@Yurunsoft) - Coroutine\Systemを使用して子プロセスを作成した時にSIGCHLD信号丢失する問題を修复 (#3446) (@huanghantao) - sendwaitがSSLをサポートしていない問題を修复 (#3459) (@huanghantao) - ArrayObjectとStringObjectのいくつかの問題を修复 (swoole/library#44) (@matyhtf) - mysqliの例外情報が間違っている問題を修复 (swoole/library#45) (@sy-records) - open_eof_checkを設定した後、Swoole\Clientが正しいerrCodeを得られない問題を修复 (#3478) (@huanghantao) - MacOSでのatomic->wait() / wakeup()のいくつかの問題を修复 (#3476) (@Yurunsoft) - Client::connectが接続を拒否した時に成功状態を返す問題を修复 (#3484) (@matyhtf) - Alpine環境下でのnullptr_tが宣言されていない問題を修复 (#3488) (@limingxinleo) - HTTP Clientがファイルをダウンロードする時にdouble-freeの問題を修复 (#3489) (@Yurunsoft) - Serverが破壊された時にServer\Portが解放されずにメモリ漏洩が発生する問題を修复 (#3507) (@twose) - MQTTプロトコルの解析問題を修复 (318e33a) (84d8214) (80327b3) (efe6c63) (@GXhua) (@sy-records) - Coroutine\Http\Client->getHeaderOut方法によるcoredump問題を修复 (#3534) (@matyhtf) - SSL検証に失敗した後、エラー情報が丢失される問題を修复 (#3535) (@twose) - README中のSwoole benchmarkリンクが間違っている問題を修复 (#3536) (@sy-records) (@santalex) - HTTP header/cookieでCRLFを使用した後にheader注入問題が発生する問題を修复 (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao) - issue #3463で指摘された変数の問題を修复 (#3547) (chromium1337) (@huanghantao) - pr #3463で指摘された誤字問題を修复 (#3547) (@deminy) - コーoutine WebSocketサーバーでframe->fd为空の問題を修复 (#3549) (@huanghantao) - ハートビート线程が接続状態を誤って判断し、接続漏洩を引き起こす問題を修复 (#3534) (@matyhtf) - Process\Poolで信号がブロックされる問題を修复 (#3582) (@huanghantao) (@matyhtf) - SAPIでsend headersを使用する問題を修复 (#3571) (@twose) (@sshymko) - CURLが実行に失敗した時にerrCodeとerrMsgが設定されない問題を修复 (swoole/library#1b6c65e) (@sy-records)
- setProtocol方法が呼び出された後、swoole_socket_coro acceptがcoredumpする問題を修复 (#3591) (@matyhtf)
### 内核 - C++スタイルを使用 (#3349) (#3351) (#3454) (#3479) (#3490) (@huanghantao) (@matyhtf) - PHPオブジェクトのread属性の性能を向上させるためにSwoole known stringsを追加 (#3363) (@huanghantao) - 多处のコード最適化 (#3350) (#3356) (#3357) (#3423) (#3426) (#3461) (#3463) (#3472) (#3557) (#3583) (@huanghantao) (@twose) (@matyhtf) - 多处のテストコードの最適化 (#3416) (#3481) (#3558) (@matyhtf) - Swoole\Tableのintタイプを単純化 (#3407) (@matyhtf) - sw_memset_zeroを追加し、bzero関数を置き換える (#3419) (@CismonX) - ログモジュールを最適化 (#3432) (@matyhtf) - 多处のlibswooleの再構築 (#3448) (#3473) (#3475) (#3492) (#3494) (#3497) (#3498) (#3526) (@matyhtf) - 多处のヘッダファイルの導入を再構築 (#3457) (@matyhtf) (@huanghantao) - Channel::count()とChannel::get_bytes()を追加 (f001581) (@matyhtf) - scope guardを追加 (#3504) (@huanghantao) - libswooleのカバレッジテストを追加 (#3431) (@huanghantao) - lib-swoole/ext-swoole MacOS環境のテストを追加 (#3521) (@huanghantao)
- lib-swoole/ext-swoole Alpine環境のテストを追加 (#3537) (@limingxinleo)
## v4.5.2

[v4.5.2](https://github.com/swoole/swoole-src/releases/tag/v4.5.2)、これはBUG修正バージョンであり、下向きの互換性の変更はありません。
### 增强 - `Server->set(['log_rotation' => SWOOLE_LOG_ROTATION_DAILY])`を使用して日付に基づいてログを生成 (#3311) (@matyhtf) - `swoole_async_set(['wait_signal' => true])`をサポートし、シグナルリスナーが存在する場合、reactorは退出しません (#3314) (@matyhtf) - `Server->sendfile`で空のファイルを送信 (#3318) (@twose) - workerの忙碌警告情報を最適化 (#3328) (@huanghantao) - HTTPSプロキシにおけるHostヘッダの構成を最適化 (ssl_host_nameを使用して構成) (#3343) (@twose) - SSLはデフォルトでecdh autoモードを使用 (#3316) (@matyhtf)
- SSLクライアントは接続が切断された時に静かに退出 (#3342) (@huanghantao)
### 修复 - OSXプラットフォームでの`Server->taskWait`の問題を修复 (#3330) (@matyhtf) - MQTTプロトコルの解析エラーのbug (8dbf506b) (@guoxinhua) (2ae8eb32) (@twose) - Content-Lengthのintタイプオーバーフローを修复 (#3346) (@twose) - PRIパケットの長さチェックが欠けている問題を修复 (#3348) (@twose) - CURLOPT_POSTFIELDSを空にできない問題を修复 (swoole/library@ed192f64) (@twose)
- 最新の接続オブジェクトが次の接続を受け取る前に解放されない問題を修复 (swoole/library@1ef79339) (@twose)
### 内核 - Socketのゼロコピー特性 (#3327) (@twose)
- swoole_get_last_error/swoole_set_last_errorを使用してグローバル変数の読み書きを置き換える (#3315) (@huanghantao) (#3363) (@matyhtf)
## v4.5.1

[v4.5.1](https://github.com/swoole/swoole-src/releases/tag/v4.5.1)、これはBUG修正バージョンであり、v4.5.0で導入されるはずだったSystemファイル関数の廃止マークを補完しました。
### 增强 - hookの下でのsocket_contextのbindto設定をサポート (#3275) (#3278) (@codinghuang) - client::sendtoが自動的にDNSを解析してアドレスを解決することをサポート (#3292) (@codinghuang) - Process->exit(0)は直接プロセスを退出させますが、shutdown_functionsを実行した後に退出したい場合は、PHPで提供されているexitを使用してください (a732fe56) (@matyhtf) - log_date_formatを設定してログの日付フォーマットを変更し、log_date_with_microsecondsでログにミクロSecondsのタイムスタンプを表示 (baf895bc) (@matyhtf) - CURLOPT_CAINFOとCURLOPT_CAPATHをサポート (swoole/library#32) (@sy-records)
- CURLOPT_FORBID_REUSEをサポート (swoole/library#33) (@sy-records)
### 修复 - 32位でのビル드が失敗する問題を修复 (#3276) (#3277) (@remicollet) (@twose) - コーoutine Clientが重複して接続した時にEISCONNエラー情報が含まれていない問題を修复 (#3280) (@codinghuang) - Tableモジュールに潜在的なbugを修复 (d7b87b65) (@matyhtf) - Serverで未定義行為によりnullポインタ (防御的プログラミング) (#3304) (#3305) (@twose) - ハートビート設定が有効になるとnullポインタエラーが発生する問題を修复 (#3307) (@twose) - mysqli設定が機能しない問題を修复 (swoole/library#35)
- response中の不正確なheader (スペースが欠けている) 解析問題を修复 (swoole/library#27) (@Yurunsoft)
### 废弃
- Coroutine\System::(fread/fgets/fwrite)などの方法を廃止し、hook特性を使用して、PHPで提供されているファイル関数を直接
### コア - 自定义オブジェクトにzend_object_allocを使用してメモリを割り当てる (cf1afb25) (@twose) - ログモジュールの設定項目を追加するためのいくつかの最適化 (#3296) (@matyhtf)
- 소규모コード最適化作業と単体テストの追加 (swoole/library) (@deminy)
## v4.5.0

[v4.5.0](https://github.com/swoole/swoole-src/releases/tag/v4.5.0)、これは大きなバージョンアップで、v4.4.xで既にマークされた廃止されたモジュールを削除しただけです
### 新しい API - DTLSサポートが追加され、WebRTCアプリケーションを構築するためにこの機能を使用できるようになりました (#3188) (@matyhtf) - 内蔵の`FastCGI`クライアントは、一行のコードでFPMにプロキシ请求を転送したり、FPMアプリケーションを呼び出すことができます (swoole/library#17) (@twose) - `Co::wait`, `Co::waitPid` (子プロセスを回収するために使用) `Co::waitSignal` (シグナルを待つために使用) (#3158) (@twose) - `Co::waitEvent` (ソケット上で指定されたイベントを待つために使用) (#3197) (@twose) - `Co::set(['exit_condition' => $callable])` (プログラムの退出条件をカスタムするために使用) (#2918) (#3012) (@twose) - `Co::getElapsed` (コーラントランクラインの運用時間を取得し、分析統計またはゾンビコーラントランクラインの特定を容易にするために使用) (#3162) (@doubaokun) - `Socket::checkLiveness` (システム呼び出しを通じて接続がアクティブかどうかを判断するために使用)、`Socket::peek` (読み取りバッファを覗き見るために使用) (#3057) (@twose) - `Socket->setProtocol(['open_fastcgi_protocol' => $bool])` (内蔵のFastCGIデンプリングサポート) (#3103) (@twose) - `Server::get(Master|Manager|Worker)Pid`, `Server::getWorkerId` (非同期サーバーインスタンスとその情報を取得するために使用) (#2793) (#3019) (@matyhtf) - `Server::getWorkerStatus` (ワーカープロセスの状態を取得し、忙碌状態を表す定数SWOOLE_WORKER_BUSY, SWOOLE_WORKER_IDLEを返すために使用) (#3225) (@matyhtf) - `Server->on('beforeReload', $callable)` 和 `Server->on('afterReload', $callable)` (サービス再起動イベント、managerプロセスで発生) (#3130) (@hantaohuang) - `Http\Server`の静的なファイルハンドラーは、現在`http_index_files`と`http_autoindex`設定をサポートしています (#3171) (@hantaohuang) - `Http2\Client->read(float $timeout = -1)`メソッドは、ストリーム形式の応答を読むことをサポートしています (#3011) (#3117) (@twose) - `Http\Request->getContent` (rawContentメソッドの別名) (#3128) (@hantaohuang)
- `swoole_mime_type_(add|set|delete|get|exists)()` (mime関連APIs、内蔵のmimeタイプを追加、削除、変更、検索、変更することができます) (#3134) (@twose)
### 增强 - masterとworkerプロセス間のメモリコピーを最適化 (極端な状況で4倍の性能向上) (#3075) (#3087) (@hantaohuang) - WebSocketの派送ロジックを最適化 (#3076) (@matyhtf) - WebSocketの構築フレーム時の一回のメモリコピーを最適化 (#3097) (@matyhtf) - SSL検証モジュールを最適化 (#3226) (@matyhtf) - SSL受け入れとSSLハンドシェイクを分離し、スローのSSLクライアントがコーラントランクラインサーバーを偽死させることがある問題を解決 (#3214) (@twose) - MIPSアーキテクチャをサポート (#3196) (@ekongyun) - UDPクライアントは現在、传入されたドメイン名を自動的に解析することができます (#3236) (#3239) (@huanghantao) - Coroutine\Http\Serverは、いくつかの一般的なオプションをサポートするように追加されました (#3257) (@twose) - WebSocketハンドシェイク時にcookieを設定することをサポート (#3270) (#3272) (@twose) - CURLOPT_FAILONERROR (swoole/library#20) (@sy-records) - CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY (swoole/library#22) (@sy-records)をサポート
- CURLOPT_HTTPGET (swoole/library@d730bd08) (@shiguangqi)をサポート
### 移除 - Runtime::enableStrictModeメソッドを移除 (b45838e3) (@twose)
- Bufferクラスを移除 (559a49a8) (@twose)
### コア関連 - 新しいC++ API: coroutine::async関数は、lambdaを引数として渡すことで非同期スレッドタスクを開始できます (#3127) (@matyhtf) - コアのevent-API内の整数型fdをswSocketオブジェクトに再構築 (#3030) (@matyhtf) - すべてのコアのCファイルがC++ファイルに変換されました (#3030) (71f987f3) (@matyhtf) - 一連のコード最適化 (#3063) (#3067) (#3115) (#3135) (#3138) (#3139) (#3151) (#3168) (@hantaohuang) - ヘッダファイルの正規化最適化 (#3051) (@matyhtf) - enable_reuse_port設定項をより正規化して再構築 (#3192) (@matyhtf) - Socket関連APIをより正規化して再構築 (#3193) (@matyhtf) - バッファ予測を通じて不要なシステム呼び出しを一度減らす (#3b5aa85d) (@matyhtf) - コアのflushタイマーのswServerGS::nowを移除し、直接時間関数を使用して時間を取得 (#3152) (@hantaohuang) - プロトコル設定器を最適化 (#3108) (@twose) - C構造体の初期化の書き方をより互換性のあるものに再構築 (#3069) (@twose) - bitフィールドをucharタイプに統一 (#3071) (@twose)
- 并列テストをサポートし、速度が向上しました (#3215) (@twose)
### 修复 - enable_delay_receiveが有効にされた後にonConnectがトリガーされない問題を修复しました (#3221) (#3224) (@matyhtf)
- 他のすべてのバグ修正はv4.4.xブランチに統合され、更新ログに反映されていますので、ここでは詳述しません
## v4.4.22
### 修复- HTTP2クライアントがHTTPプロキシの下で機能しない問題を修复しました (#3677) (@matyhtf) (@twose)- PDO断線再接続時のデータ混乱問題を修复 (swoole/library#54) (@sy-records)- swMutex_lockwait (0fc5665) (@matyhtf)- UDPサーバーがIPv6を使用した时的端口解析エラーを修复
- systemd fdsの問題を修复
## v4.4.20

[v4.4.20](https://github.com/swoole/swoole-src/releases/tag/v4.4.20)、これはBUG修正バージョンで、下位互換性のある変更はありません
### 修复- dispatch_funcを設定した後、Swoole\Server::closeを呼び出すときにエラーが発生する問題を修复しました (#3365) (@twose)- swoole\Redis\Server::format関数中のformat_buffer初期化問題を修复しました (#3369) (@matyhtf) (@twose)- MacOSでMACアドレスを取得できない問題を修复しました (#3372) (@twose)- MySQLテストケースを修复しました (#3374) (@qiqizjl)- WorkerStart回调関数内でServerを閉じることができない非同期Serverの問題を修复しました (#3382) (@huanghantao)- MySQL transactionエラー状態が抜け落ちている問題を修复しました (#3429) (@twose)- HTTP Clientがファイルをダウンロードするときにdouble-freeの問題を修复しました (#3489) (@Yurunsoft)- Coroutine\Http\Client->getHeaderOutメソッドがcoredumpを引き起こす問題を修复しました (#3534) (@matyhtf)- HTTP header/cookieでCRLFを使用した後、header注入問題が発生しました (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)- Coroutine WebSocketサーバーでframe->fd为空の問題を修复しました (#3549) (@huanghantao)- hook phpredisが生成するread error on connection問題を修复しました (#3579) (@twose)
- MQTTプロトコルの解析問題を修复しました (#3573) (#3517) (9ad2b455) (@GXhua) (@sy-records)
## v4.4.19

[v4.4.19](https://github.com/swoole/swoole-src/releases/tag/v4.4.19)、これはBUG修正バージョンで、下位互換性のある変更はありません

!> 注意: v4.4.xはもはやメインメンテナンスバージョンではなく、必要な場合のみBUGを修正します
### 修复
- v4.5.2からすべてのbug修正パッチを統合しました
## v4.4.18

[v4.4.18](https://github.com/swoole/swoole-src/releases/tag/v4.4.18)、これはBUG修正バージョンで、下位互換性のある変更はありません
### 增强- UDPクライアントは現在、传入されたドメイン名を自動的に解析することができます (#3236) (#3239) (@huanghantao)- CLIモードでは、shutdown後に生成されるエラーログを除いてstdoutとstderrを閉じなくなりました (#3249) (@twose)- Coroutine\Http\Serverは、いくつかの一般的なオプションをサポートするように追加されました (#3257) (@twose)- WebSocketハンドシェイク時にcookieを設定することをサポートしました (#3270) (#3272) (@twose)- CURLOPT_FAILONERROR (swoole/library#20) (@sy-records)- CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY (swoole/library#22) (@sy-records)をサポートしました - CURLOPT_HTTPGET (swoole/library@d730bd08) (@shiguangqi)をサポートしました
- 连接对象のクローンを禁止しました (swoole/library#23) (@deminy)
### 修复- SSLハンドシェイクに失敗する問題を修复しました (dc5ac29a) (@twose)- 错误信息生成時に発生する内存错误を修复しました (#3229) (@twose)- proxy验证信息が空白の場合を修复しました (#3243) (@twose)- Channelの内存泄漏問題を修复しました (真の内存泄漏ではありません) (#3260) (@twose)- Co\Http\Serverが循環参照時に発生する一次性内存泄漏を修复しました (#3271) (@twose)- ConnectionPool->fillにおける书写错误を修复しました (swoole/library#18) (@NHZEX)- curlクライアントがリダイレクトに遭遇した際に接続を更新しない問題を修复しました (swoole/library#21) (@doubaokun)- ioExceptionが発生した時に空指针問題が発生する問題を修复しました (swoole/library@4d15a4c3) (@twose)- ConnectionPool@putにnullを传入した際に新しい接続を返却せず、デッドロックを引き起こす問題を修复しました (swoole/library#25) (@Sinute)
- mysqli代理実装によるwrite_property错误を修复しました (swoole/library#26) (@twose)
## v4.4.17

[v4.4.17](https://github.com/swoole/swoole-src/releases/tag/v4.4.17)、これはBUG修正バージョンで、下位互換性のある変更はありません
### 增强- SSLサーバーの性能を向上させました (#3077) (85a9a595) (@matyhtf)- HTTPヘッダのサイズ制限を移除しました (#3187) limitation (@twose)- MIPSをサポートしました (#3196) (@ekongyun)
- CURLOPT_HTTPAUTH (swoole/library@570318be) (@twose)をサポートしました
### 修复- package_length_funcの行動と可能性のある一次性内存泄漏を修复しました (#3111) (@twose)- HTTP状態コード304の誤った行動を修复しました (#3118) (#3120) (@twose)- Traceログの誤ったマクロ展開による内存错误を修复しました (#3142) (@twose)- OpenSSL関数シグネチャーを修复しました (#3154) (#3155) (@twose)- SSL错误信息を修复しました (#3172) (@matyhtf) (@twose)- PHP-7.4での互換性を修复しました (@twose) (@matyhtf)- HTTP-chunkの長さ解析错误を修复しました (19a1c712) (@twose)- chunkedモードのmultipart requestの解析器行動を修复しました (3692d9de) (@twose)- PHP-DebugモードでのZEND_ASSUME断言失敗を修复しました (fc0982be) (@twose)- Socketの誤ったアドレスを修复しました (d72c5e3a) (@twose)- Socket getname (#3177) (#3179) (@matyhtf)- 静态ファイルハンドラーが空ファイルに対して誤った処理を行う問題を修复しました (#3182) (@twose)- Coroutine\Http\Serverでのアップロードファイル問題を修复しました (#3189) (#3191) (@twose)- shutdown中に可能性のある内存错误を修复しました (44aef60a) (@matyhtf)- Server->heartbeat (#3203) (@matyhtf)- CPUスケジュール器がデッドループをスケジュールできない可能性がある問題を修复しました (#3207) (@twose)- 不変配列に対する無効な書き込み操作を修复しました (#3212) (@twose)- WaitGroupの複数回wait問題を修复しました (swoole/library@537a82e1) (@twose)- 空headerの処理を修复しました (cURLと一致しています) (swoole/library@7c92ed5a) (@twose)- 非IO方法がfalseを返した場合に例外を投げない問題を修复しました (swoole/library@f6997394) (@twose)
- cURL-hook下でproxy端口号が何度もヘッダに追加される問題を修复しました (swoole/library@5e94e5da) (@twose)
## v4.4.16

[v4.4.16](https://github.com/swoole/swoole-src/releases/tag/v4.4.16)、これはBUG修正バージョンで、下位互換性のある変更はありません
### 增强 - 現在、[Swooleバージョンサポート情報](https://github.com/swoole/swoole-src/blob/master/SUPPORTED.md)を取得できます -より親切なエラー提示 (0412f442) (09a48835) (@twose) - 特定のシステムでシステム呼び出しの死循环に陥るのを防ぐ (069a0092) (@matyhtf)
- PDOConfigにドライバーオプションを追加しました (swoole/library#8) (@jcheron)
### 修复- http2_session.default_ctxの内存错误を修复しました (bddbb9b1) (@twose)- 初始化されていないhttp_contextを修复しました (ce77c641) (@twose)- Table模块の书写错误を修复しました (内存错误を引き起こす可能性があります) (db4eec17) (@twose)- Server内のtask-reloadの潜在的な問題を修复しました (e4378278) (@GXhua)- 不完全な协程HTTPサーバー请求原文を修复しました (#3079) (#3085) (@hantaohuang)- static handler (ファイル为空の場合、404応答を返すべきではありません) (#3084) (@Yurunsoft)- http_compression_level設定が正常に機能しない問題を修复しました (16f9274e) (@twose)- Coroutine HTTP2 Serverがhandleを登録していないために空指针错误を引き起こす問題を修复しました (ed680989) (@twose)- socket_dontwait設定が機能しない問題を修复しました (27589376) (@matyhtf)- zend::evalが何度も実行される可能性がある問題を修复しました (#3099) (@GXhua)- HTTP2サーバーが接続閉鎖後に応答生成によって空指针错误を引き起こす問題を修复しました (#3110) (@twose
