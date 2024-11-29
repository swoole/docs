```markdown
# サーバー (非同期スタイル)

TCP、UDP、[unixSocket](/learn?id=何谓IPC)の3種類のsocketタイプをサポートし、IPv4とIPv6をサポートし、SSL/TLSの単方向および双方向の証明書のトンネル暗号化をサポートします。ユーザーは低レベルの実装の詳細に关注する必要はなく、ネットワーク[イベント](/server/events)の回调関数を設定するだけで済みます。例は[快速启动](/start/start_tcp_server)を参照してください。

!> ただし、「Server」側のスタイルは非同期であり（つまり、すべてのイベントには回调関数を設定する必要があります）、同時に协程をサポートしており、「enable_coroutine](/server/setting?id=enable_coroutine)」を有効にすると协程がサポートされます（デフォルトで有効）。[协程](/coroutine)の下でのすべてのビジネスコードは同期で書かれています。

学ぶべきところ：

[Serverの3つの運用モードの紹介](/learn?id=serverの3つの運用モードの紹介 ':target=_blank')  
[Process、ProcessPool、UserProcessの違いは何ですか](/learn?id=process-diff ':target=_blank')  
[Masterプロセス、Reactorスレッド、Workerプロセス、Taskプロセス、Managerプロセスの違いと関連性](/learn?id=diff-process ':target=_blank')  
### 運用プロセス図 <!-- {docsify-ignore} --> 

![running_process](https://wiki.swoole.com/_images/server/running_process.png ':size=800xauto')
### プロセス/スレッド構造図 <!-- {docsify-ignore} --> 

![process_structure](https://wiki.swoole.com/_images/server/process_structure.png ':size=800xauto')

![process_structure_2](https://wiki.swoole.com/_images/server/process_structure_2.png)
```
