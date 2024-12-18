# 伺服器端(異步風格)

方便地創建一個異步伺服器程式，支援`TCP`、`UDP`、[unixSocket](/learn?id=什麼是IPC) 三種socket類型，支援`IPv4`和`IPv6`，支援`SSL/TLS`單向雙向證書的隧道加密。使用者無需關注底層實現細節，僅需設置網絡[事件](/server/events)的回調函數即可，示範參考[快速啟動](/start/start_tcp_server)。

!> 只是`Server`端的風格是異步的(即所有事件都需要設置回調函數)，但同時也是支援協程的，開啟了[enable_coroutine](/server/setting?id=enable_coroutine)之后就支援協程了(默認開啟)，[協程](/coroutine)下所有的業務代碼都是同步寫法。

前往了解：

[Server 的三種運行模式介紹](/learn?id=server的三種運行模式介紹 ':target=_blank')  
[Process、ProcessPool、UserProcess的區別是什麼](/learn?id=process-diff ':target=_blank')  
[Master進程、Reactor線程、Worker進程、Task進程、Manager進程的區別與聯繫](/learn?id=diff-process ':target=_blank')  


### 運行流程圖 <!-- {docsify-ignore} --> 

![running_process](https://wiki.swoole.com/_images/server/running_process.png ':size=800xauto')

### 進程/線程結構圖 <!-- {docsify-ignore} --> 

![process_structure](https://wiki.swoole.com/_images/server/process_structure.png ':size=800xauto')

![process_structure_2](https://wiki.swoole.com/_images/server/process_structure_2.png)
