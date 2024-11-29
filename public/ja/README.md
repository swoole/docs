# Swoole

> `Swoole`は、`C++`言語で書かれた非同期イベント駆動型と協程を基にした並行ネットワークコミュニケーションエンジンであり、`PHP`に協程（/coroutine）、高パフォーマンス（/question/use?id=how-is-the-performance-of-swoole）ネットワークプログラミングのサポートを提供します。TCP/UDPサービス、高パフォーマンスWeb、WebSocketサービス、IoT、リアルタイムコミュニケーション、ゲーム、マイクロサービスなど、多様な通信プロトコルのネットワークサーバーとクライアントモジュールを提供し、`PHP`を従来のWeb分野に囚われることなく、簡単かつ迅速に実現できます。
## Swooleクラス図

!>クリックして対応するドキュメントページに直接アクセスできます。

[//]: # (https://naotu.baidu.com/file/bd9d2ba7dfae326e6976f0c53f88b18c)

<embed src="/_images/swoole_class.svg" type="image/svg+xml" alt="Swooleアーキテクチャ図" />
## 官方ウェブサイト

* [Swoole公式サイト](//www.swoole.com)
* [ビジネス製品とサポート](//business.swoole.com)
* [SwooleQ&A](//wenda.swoole.com)
## プロジェクトアドレス

* [GitHub](//github.com/swoole/swoole-src) **（サポートする場合はStarを押してください）**
* [Gitee](//gitee.com/swoole/swoole)
* [PECL](//pecl.php.net/package/swoole)
## 开発ツール

* [IDE Helper](https://github.com/swoole/ide-helper)
* [Yasd](https://github.com/swoole/yasd)
* [デバッガ](https://github.com/swoole/debugger)
## 版权情報

このドキュメントの元の内容は、以前のSwooleドキュメントから摘んだもので、皆さんがずっと不満を言っていたドキュメントの問題を解決することを目的としています。現代的なドキュメントの組織形式を採用し、Swoole4の内容のみを含み、古いドキュメントの誤った内容を多く修正し、ドキュメントの詳細を最適化し、サンプルコードといくつかの教育内容を追加し、Swoole初心者にとってよりフレンドリーになっています。
このドキュメントのすべての内容、テキスト、画像、音声、ビデオ資料の著作権はすべて**上海识沃网络科技有限公司**に属しています。どんなメディア、ウェブサイト、または個人も外部リンクの形で引用することができますが、協定による許可なく、どのような形でもコピー・発表してはいけません。
## 文档発起者

* 杨才 [GitHub](https://github.com/TTSimple)
* 郭新华 [Weibo](https://www.weibo.com/u/2661945152)
* [鲁飞](https://github.com/sy-records) [Weibo](https://weibo.com/5384435686)
## 問い合わせ

このドキュメントの内容に関する問題（誤字、例えばの誤り、内容の欠落など）や要求、提案は、[swoole-inc/report](https://github.com/swoole-inc/report)プロジェクトに`issue`を提出してください。また、右上の[反馈](/?id=main)をクリックして`issue`ページに直接アクセスすることもできます。

採用されれば、提出者の情報が[文档贡献者](/CONTRIBUTING)リストに追加され、感謝の意を表します。
## 文档原則

直感的な言葉を使い、**できるだけ**Swooleの低レベル技術的詳細やいくつかの低レベルの概念を紹介しません。低レベルの後続には、専門の`hack`セクションを维护することができます。

ある概念を避けて通ることができない場合は、**必ず**その概念を集中的に紹介する場所が必要であり、他の場所では内部リンクを通じています。例えば：[イベントループ](/learn?id=什么是eventloop)；

文書を書く際には思考を変え、初心者の視点から他人が理解できるかどうかを見る必要があります。

後続の機能変更が発生した場合は**必ず**すべて関連する部分を修正し、一箇所だけを修正することはできません。

各機能モジュールには**必ず**完全な例が必要です；
