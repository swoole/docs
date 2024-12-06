```
# Swoole

?> `Swoole`は、`C++`言語を使用して書かれた非同期イベント駆動と協程に基づく並列ネットワーク通信エンジンであり、`PHP`に[協程](/coroutine)、[高性能](/question/use?id=how-is-the-performance-of-swoole)ネットワークプログラミングのサポートを提供しています。TCP/UDPサービス、高性能Web、WebSocketサービス、IoT、リアルタイム通信、ゲーム、ミニサービスなど、様々な通信プロトコルを持つネットワークサーバーとクライアントモジュールを提供し、`PHP`を従来のWeb分野に限定させません。

## Swooleクラス図

!> 直接にリンクして対応するドキュメントページにアクセスできます

[//]: # (https://naotu.baidu.com/file/bd9d2ba7dfae326e6976f0c53f88b18c)

<embed src="/_images/swoole_class.svg" type="image/svg+xml" alt="Swoole構造図" />

## 公式ウェブサイト

* [Swoole公式](//www.swoole.com)
* [ビジネス製品とサポート](//business.swoole.com)
* [Swoole質問](//wenda.swoole.com)

## プロジェクトアドレス

* [GitHub](//github.com/swoole/swoole-src) **（支援はStarをクリックしてください）**
* [Gitee](//gitee.com/swoole/swoole)
* [PECL](//pecl.php.net/package/swoole)

## 開発ツール

* [IDE Helper](https://github.com/swoole/ide-helper)
* [Yasd](https://github.com/swoole/yasd)
* [デバッガー](https://github.com/swoole/debugger)

## 版权情報

このドキュメントの元の内容は、以前の旧版Swooleドキュメントから抜粋されたもので、皆さんがずっと不満を持っていましたドキュメントの問題を解決することを目的としています。現代的なドキュメントの組織形式を採用し、`Swoole4`の内容のみを含んでいます。多くの古いドキュメントの誤った内容を修正し、ドキュメントの詳細を最適化し、例コードといくつかの教育内容を追加し、`Swoole`初心者にもっとフレンドリーにしました。

このドキュメントのすべての内容、文字、画像、音声、ビデオ資料の著作権はすべて **上海識沃网络科技有限公司** が所有しています。メディア、ウェブサイト、個人は外部リンクの形で引用することができますが、協定未经授权でいかなる形で複製・発表することもできません。

## ドキュメント発起人

* 杨才 [GitHub](https://github.com/TTSimple)
* 郭新华 [微博](https://www.weibo.com/u/2661945152)
* [鲁飞](https://github.com/sy-records) [微博](https://weibo.com/5384435686)

## 問題フィードバック

このドキュメントの内容に関する問題（例えば、誤字、例の誤り、内容の欠如など）やニーズの提案については、[swoole-inc/report](https://github.com/swoole-inc/report)プロジェクトに`issue`を提出してください。または、右上角的 [フィードバック](/?id=main)をクリックして`issue`ページに直接跳转することもできます。

採用されれば、提出者の情報を[ドキュメント貢献者](/CONTRIBUTING)リストに追加し、感謝の意を表します。

## ドキュメント原則

直感的な言語を使用し、**できるだけ** `Swoole`の低層技術の詳細や低層の概念を少なく説明します。低層については後ほど専門の`hack`セクションを維持することができます。

ある概念を説明できない場合は、**必ず**その概念を集中的に紹介する場所があり、他の場所では内链で飛ぶことができます。例えば：[イベントループ](/learn?id=什么是eventloop)などです。

ドキュメントを書くときは、小白の視点から他の人が理解できるかどうかを見極めるように考え转变する必要があります。

機能の変更がこれ以降に発生した場合、**必ず**関連するすべての部分を修正する必要があります。一つの場所だけを修正するわけにはいきません。

各機能モジュール**必ず**完全な例を持っています。
```
