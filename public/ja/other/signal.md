```
# Linuxシグナル一覧表

## 完全対照表

| シグナル   | 値   | デフォルト動作 | 意味（シグナルを発信した理由）                  |
| --------- | ---- | ------------ | ------------------------------------------- |
| SIGHUP    | 1    | Term         | ターミナルの切断またはプロセスの死亡            |
| SIGINT    | 2    | Term         |キーボードからの割り込みシグナル            |
| SIGQUIT   | 3    | Core         | キーボードからの退出シグナル                |
| SIGILL    | 4    | Core         |不正な命令                                  |
| SIGABRT   | 6    | Core         | abortからの異常シグナル                   |
| SIGFPE    | 8    | Core         | 浮動小数点例外                              |
| SIGKILL   | 9    | Term         | 殺害                                        |
| SIGSEGV   | 11   | Core         |セグメント違反エラー（メモリ参照無効）        |
| SIGPIPE   | 13   | Term         |パイプが壊れた：読み取りプロセスがないパイプにデータを書き込む |
| SIGALRM   | 14   | Term         | alarmからのタイマーの到達シグナル             |
| SIGTERM   | 15   | Term         | 終了                                        |
| SIGUSR1   | 30,10,16 | Term         | ユーザー定義シグナル 1                        |
| SIGUSR2   | 31,12,17 | Term         | ユーザー定義シグナル 2                        |
| SIGCHLD   | 20,17,18 | Ign          | 子プロセスが停止したり終了したりした          |
| SIGCONT   | 19,18,25 | Cont         | 停止していたら継続して実行する                |
| SIGSTOP   | 17,19,23 | Stop         | ターミナルから来る以外的停止シグナル            |
| SIGTSTP   | 18,20,24 | Stop         | ターミナルからの停止シグナル                  |
| SIGTTIN   | 21,21,26 | Stop         | 背景プロセスがターミナルを読んでいる          |
| SIGTTOU   | 22,22,27 | Stop         | 背景プロセスがターミナルに書き込んでいる        |
|           |      |              |                                             |
| SIGBUS    | 10,7,10  | Core         |バスエラー（メモリアクセスエラー）            |
| SIGPOLL   |      | Term         | Pollableイベントが発生（Sys V）、SIGIOと同義 |
| SIGPROF   | 27,27,29 | Term         | 统计分布図用のタイマーが到達                |
| SIGSYS    | 12,-,12  | Core         | 無効なシステム呼び出し（SVr4）              |
| SIGTRAP   | 5    | Core         | トラック/ブレークポイント自陷                 |
| SIGURG    | 16,23,21 | Ign          | socket緊急シグナル（4.2BSD）                 |
| SIGVTALRM | 26,26,28 | Term         | 仮想タイマーが到達（4.2BSD）                |
| SIGXCPU   | 24,24,30 | Core         | CPU時間を超えた（4.2BSD）                   |
| SIGXFSZ   | 25,25,31 | Core         | ファイルサイズの制限を超えた（4.2BSD）        |
|           |      |              |                                             |
| SIGIOT    | 6    | Core         | IOT自陷、SIGABRTと同義                     |
| SIGEMT    | 7,-,7    |              | Term                                        |
| SIGSTKFLT | -,16,-   | Term         | コプロセッサスタックエラー（使用しない）        |
| SIGIO     | 23,29,22 | Term         |DESCcriptors上でI/O操作ができる               |
| SIGCLD    | -,-,18   | Ign          | SIGCHLDと同義                               |
| SIGPWR    | 29,30,19 | Term         | 電力故障/再起動                             |
| SIGINFO   | 29,-,-   |              | SIGPWRと同義                              |
| SIGLOST   | -,-,-    | Term         | ファイルロックが失われた                      |
| SIGWINCH  | 28,28,20 | Ign          | ターミナルウィンドウサイズの変更（4.3BSD, Sun）|
| SIGUNUSED | -,31,-   | Term         | 未使用のシグナル（将来的にはSIGSYSになる）    |

## 非信頼シグナル

| 名称      | 説明                        |
| --------- | --------------------------- |
| SIGHUP    | 接続が切断された          |
| SIGINT    | ターミナルの割り込み文字      |
| SIGQUIT   | ターミナルの退出文字          |
| SIGILL    | 無効なハードウェア命令        |
| SIGTRAP   | ハードウェア故障              |
| SIGABRT   | 異常終了（abort）             |
| SIGBUS    | ハードウェア故障              |
| SIGFPE    | 算術例外                    |
| SIGKILL   | 終了                        |
| SIGUSR1   | ユーザー定義のシグナル        |
| SIGUSR2   | ユーザー定義のシグナル        |
| SIGSEGV   | 無効なメモリ参照              |
| SIGPIPE   | 読み取りプロセスがないパイプへの書き込み |
| SIGALRM   | タイマーのタイムアウト（alarm） |
| SIGTERM   | 終了                        |
| SIGCHLD   | 子プロセスの状態変更          |
| SIGCONT   | 停止していたプロセスを再開する      |
| SIGSTOP   | 停止                        |
| SIGTSTP   | ターミナルの停止文字          |
| SIGTTIN   | 背景プロセスがコントロールTTYを読んでいる |
| SIGTTOU   | 背景プロセスがコントロールTTYに書き込んでいる |
| SIGURG    | 緊急状況（socket）            |
| SIGXCPU   | CPU制限を超えた（setrlimit）    |
| SIGXFSZ   | ファイルサイズの制限を超えた（setrlimit） |
| SIGVTALRM | 仮想時間のアラーム（setitimer）     |
| SIGPROF   | プロファイル時間のタイムアウト（setitimer） |
| SIGWINCH  | ターミナルのウィンドウサイズの変更 |
| SIGIO     | 非同期I/O                   |
| SIGPWR    | 電力故障/再起動             |
| SIGSYS    | 無効なシステム呼び出し        |

## 可靠信号

| 名称        | ユーザー定義 |
| ----------- | ---------- |
| SIGRTMIN    |            |
| SIGRTMIN+1  |            |
| SIGRTMIN+2  |            |
| SIGRTMIN+3  |            |
| SIGRTMIN+4  |            |
| SIGRTMIN+5  |            |
| SIGRTMIN+6  |            |
| SIGRTMIN+7  |            |
| SIGRTMIN+8  |            |
| SIGRTMIN+9  |            |
| SIGRTMIN+10 |            |
| SIGRTMIN+11 |            |
| SIGRTMIN+12 |            |
| SIGRTMIN+13 |            |
| SIGRTMIN+14 |            |
| SIGRTMIN+15 |            |
| SIGRTMAX-14 |            |
| SIGRTMAX-13 |            |
| SIGRTMAX-12 |            |
| SIGRTMAX-11 |            |
| SIGRTMAX-10 |            |
| SIGRTMAX-9  |            |
| SIGRTMAX-8  |            |
| SIGRTMAX-7  |            |
| SIGRTMAX-6  |            |
| SIGRTMAX-5  |            |
| SIGRTMAX-4  |            |
| SIGRTMAX-3  |            |
| SIGRTMAX-2  |            |
| SIGRTMAX-1  |            |
| SIGRTMAX    |            |
```
