# Swoole 项目的起源与命名由来

> 本页面由 Swoole 开源项目创始人 Rango 编写，仅代表其个人观点。

## 项目起源

Swoole 项目的最初想法源自于我之前所参与的一个企业软件项目。那是在大约 2010 年底的时候，我们公司的产品有一个需求，那就是用户可以任意生成一个 email 地址，然后其他用户可以向这个 email 发送邮件，后台能实时地将邮件内容解析成数据，并主动通知用户。当时这个项目是使用 PHP 来开发的，在实现这个需求的过程中遇到了难题，PHP 只能依赖其他的 SMTP 服务器，通过 pop3 协议定时查收新邮件来完成，这样就不是实时的。如果要实现一个实时的系统，就必须自己写一个 `TCP Socket Server` 来实现 `SMTP` 协议接收数据。当时 PHP 在这个领域几乎是空白的，没有一套成熟的网络通信框架。为了实现这个需求，我从 `socket` 学起，一直到 `TCP/IP`、IO 复用、`libevent`、多进程，最终实现了这套程序。完成这个项目后，我就想把这套程序开源出来，希望能帮助其他 PHPer 解决在这个领域的难题。如果能有这样一个框架，那么 PHP 就能从单纯地做一个 Web 网站延伸到更大的空间。

## 性能问题

另一个重要的原因是 PHP 程序的性能问题，我最早是学 Java 出身的，工作后才转行成为一名 PHP 程序员。在使用 PHP 开发程序的过程中，我一直在思考的问题是什么？PHP 和 Java 相比最大的优势是什么？简单高效，PHP 在请求完成之后会释放所有资源和内存，无须担心内存泄漏。代码的质量无论高低一样运行得很流畅。但同时这也是 PHP 致命的缺点。一旦请求数量上升，并发很高的时候，快速创建资源，又马上释放，使得 PHP 程序运行效率急剧下降。另外一旦项目的功能越来越复杂，代码增多后，对于 PHP 也会是一场灾难。这也是为什么 PHP 的框架没有被 PHP 程序员广泛接受，而 Java 不存在这个问题。再好的框架也会被这种低效的方式拖累，导致系统变慢。所以想到了使用 PHP 来开发 PHP 的应用服务器，让 PHP 的代码加载到内存后，拥有更长的生命周期，这样建立的数据库连接和其他大的对象，不被释放。每次请求只需要处理很少的代码，而这些代码只在第一次运行时，被 PHP 解析器编译，驻留内存。另外，之前 PHP 不能实现的，对象持久化、数据库连接池、缓存连接池都可以实现。系统的运行效率会大大提高。

经过一段时间的研究，目前已经初步得到了实现。使用 PHP 本身编写出 HTTP 服务器，以独立服务器方式运行，单个程序页面（有对象生成，数据库连接、smarty 模板操作）的执行时间由原来的 0.0x 秒，下降到了 0.00x 秒。使用 Apache AB 并发 100 测试，比传统 LAMP 方式，Request per Second 高出至少 10 倍。在我的测试机上（Ubuntu10.04 Inter Core E5300 + 2G 内存），Apache 只跑到 83RPS，Swoole Server 可以跑到 1150 多 RPS。

这个项目就是 Swoole 的雏形。这个版本一直持续维护了 2 年多，在这个过程中逐步有了一些经验积累，对这套技术方案存在的问题有了更深入的理解，比如性能差、限制较多无法直接调用操作系统接口、内存管理效率低下。

## 入职腾讯

2011 年底我入职腾讯，负责朋友网的 PHP 平台开发工作。惊奇地发现朋友网的同事不光这样想了，他们直接做到了。朋友网团队已经在生产环境中使用了这套方案。朋友网有三架马车，第一个是 PWS，这是一个纯 PHP 编写的 WebServer，朋友网线上有 600 多台服务器运行在 PWS 上，完全没有使用 Apache、PHP-FPM 之类的程序。第二个是 SAPS，这是使用纯 PHP 开发的一个分布式队列，当时大概由 150 台服务器的集群在跑，很多图片裁剪、头像处理、消息同步、数据同步等逻辑全部使用了 SAPS 做逻辑异步化。第三个是 PSF，这是一个 PHP 实现的 Server 框架，朋友网很多逻辑层的服务器都是基于 PSF 实现的。大概有 300 台左右的集群在运行 PSF 服务器程序。在朋友网的这段时间，我学到了很多 Linux 底层、网络通信的知识，积累了很多大型集群高并发环境的网络通信跟踪、调试经验，为开发 Swoole 打下了一个很好的基础。

## 开发 Swoole

在这期间也学习了解到了 Node.js、Golang 这些优秀的技术方案，得到了更多灵感。在 2012 年的时候就有了新的想法，决定使用 C 语言重新实现一个性能更强、功能更强大的版本。这就是现在的 Swoole 扩展。

现在 Swoole 已经被很多 PHP 技术团队用于实际项目的开发工作，国内国外都有。国内有名的有百度订单中心、百度地图、腾讯 QQ 公众号和企业 QQ、战旗直播、360、当当网、穷游等。另外还有很多物联网、硬件、游戏项目也在使用 Swoole。另外基于 Swoole 的开源框架也越来越多，比如 TSF、Blink、swPromise 等等，在 GitHub 上也能找到很多 Swoole 相关的项目和代码。

## 名字由来

Swoole 这个名字不是一个英文单词，是由我创造的一个音近字。我最早想到的名字是叫做 `sword-server`，寓意是为广大 PHPer 创造一把锋利的剑，后来联想到 Google 也是凭空创造出来的，所以我就给它命名为 `swoole`。
