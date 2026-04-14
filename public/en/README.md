# Swoole

?> `Swoole` is a parallel network communication engine based on asynchronous event-driven and coroutine, written in `C++`, providing [coroutine](/coroutine) and [high-performance](/question/use?id=how-is-the-performance-of-swoole) network programming support for `PHP`. It provides various network server and client modules for multiple communication protocols, making it easy to quickly implement `TCP/UDP services`, `high-performance Web`, `WebSocket services`, `IoT`, `real-time communication`, `games`, `microservices`, etc., breaking the limits of `PHP` in traditional web domains.

## Swoole Class Diagram

!>Click the link directly to the corresponding documentation page

[//]: # (https://naotu.baidu.com/file/bd9d2ba7dfae326e6976f0c53f88b18c)

<embed src="/_images/swoole_class.svg" type="image/svg+xml" alt="Swoole Architecture Diagram" />

## Official Website

* [Swoole Official Website](//www.swoole.com)
* [Commercial Products and Support](//business.swoole.com)
* [Swoole Q&A](//wenda.swoole.com)

## Project Links

* [GitHub](//github.com/swoole/swoole-src) **(Please Star to support)**
* [Gitee](//gitee.com/swoole/swoole)
* [Pecl](//pecl.php.net/package/swoole)

## Development Tools

* [IDE Helper](https://github.com/swoole/ide-helper)
* [Yasd](https://github.com/swoole/yasd)
* [debugger](https://github.com/swoole/debugger)
* [sdebug](https://github.com/swoole/sdebug)

## Copyright Information

The original content of this document is from the previous [old version of Swoole documentation](https://wiki.swoole.com/wiki/index/prid-1), aiming to solve the long-standing issues with documentation. It adopts a modernized document organizational form, only including the content of `Swoole4`, correcting many errors from the old documents, optimizing document details, adding sample code and some teaching content, making it more friendly to `Swoole` beginners.

All content in this document, including all text, images, and audiovisual materials, are copyrighted by **Shanghai SWO Network Technology Co., Ltd**. Any media, website, or individual can quote in the form of external links, but may not copy or publish in any form without authorization.

## Document Initiators

* Yang Cai [GitHub](https://github.com/TTSimple)
* Guo Xinhua [Weibo](https://www.weibo.com/u/2661945152)
* [Lu Fei](https://github.com/sy-records) [Weibo](https://weibo.com/5384435686)

## Issue Feedback

For issues regarding the content of this document (such as typos, errors in examples, missing content, etc.) and suggestions for improvements, please submit them to the [swoole-inc/report](https://github.com/swoole-inc/report) project by creating an `issue`. You can also click on the 'Feedback' link in the upper right corner to jump to the issue page.

Once accepted, the contributor's information will be added to the [Document Contributors](/CONTRIBUTING) list as a token of appreciation.

## Document Principles

Use straightforward language, **try** to introduce as few technical details of `Swoole` underlying technology as possible and some underlying concepts, which can be maintained in a dedicated 'hack' section later;

When encountering inevitable concepts, **must** have a centralized place to introduce this concept, and link to it from other places. For example: [Event Loop](/learn?id=what-is-eventloop);

When writing documents, shift your thinking, and examine whether others can understand it from a layman's perspective;

When there are feature changes in the future, **must** modify all relevant parts, and not just one;

Each functional module **must** have a complete example.
