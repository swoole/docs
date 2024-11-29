#エラー報告の提出

## 须知

Swooleカーネルのバグを発見した場合は、報告してください。Swooleのカーネル開発者たちは問題の存在を知らないかもしれません。あなたが自ら報告しなければ、バグは発見されにくく修正されるかもしれません。問題を発見し修正するために、[GitHubのissueエリア](https://github.com/swoole/swoole-src/issues)でエラー報告を提出することができます（右上角的緑色の「New issue」ボタンをクリック）。ここでのエラー報告は最優先で解決されます。

メールリストやプライベートな手紙でエラー報告を送信しないでください。GitHubのissueエリアでもSwooleに対する任意の要求や提案を行うことができます。

エラー報告を提出する前に、以下の「エラー報告の提出方法」をよく読んでください。

## 新しい問題の作成

まず、issueを作成すると、システムは以下のテンプレートを出力しますので、それを真剣に記入してください。そうでなければ、情報が不足しているためissueが無視される可能性があります：

```markdown

Please answer these questions before submitting your issue. Thanks!
> Issueを提出する前にこれらの質問に答えください：
	
1. What did you do? If possible, provide a simple script for reproducing the error.
> 問題を発生させる過程を詳しく説明し、関連するコードを掲示してください。できれば、安定して再現できる簡単なスクリプトコードを提供してください。

2. What did you expect to see?
> 予想される結果は何でしたか？

3. What did you see instead?
>実際に実行された結果は何でしたか？

4. What version of Swoole are you using (`php --ri swoole`)?
> 使用しているSwooleのversionは何ですか？（`php --ri swoole`と実行して表示される内容を掲示してください）

5. What is your machine environment used (including the version of kernel & php & gcc)?
> 使用しているマシン環境は？（カーネル、PHP、gccコンパイラのバージョン情報を含めてください）
> `uname -a`、`php -v`、`gcc -v`の命令を実行して表示することができます

```

中でも最も重要なのは**安定して再現できる簡単なスクリプトコード**を提供することです。そうでなければ、できるだけ多くの他の情報を提供して開発者がエラーの原因を判断するのを手伝ってください。

## メモリ分析（強くお勧め）

より多くの場合、Valgrindはgdbよりもメモリの問題を発見することができます。以下のコマンドを実行してプログラムを動作させ、BUGが発生するまで続けます：

```shell
USE_ZEND_ALLOC=0 valgrind --log-file=/tmp/valgrind.log php your_file.php
```

* プログラムがエラーが発生したとき、`ctrl+c`を押して退出し、`/tmp/valgrind.log`ファイルをアップロードして開発チームがBUGを特定するのを手伝ってください。

## セグメントエラー（コアディ撒玛）について

また、特殊な状況では開発者が問題を特定するのを助けるためにデバッグツールを使用することができます。

```shell
WARNING	swManager_check_exit_status: worker#1 abnormal exit, status=0, signal=11
```

Swooleのログに上記の通知が現れる場合（signal11）、プログラムが`コアディ撒玛`が発生したことを意味します。追跡デバッグツールを使用してその発生位置を特定する必要があります。

> `gdb`を使用して`swoole`を追跡する前に、编译時に`--enable-debug`パラメータを追加してより多くの情報を保持する必要があります。

コアディ撒玛ファイルを有効にします
```shell
ulimit -c unlimited
```

BUGが発生すると、コアディ撒玛ファイルはプログラムのディレクトリまたはシステムのrootディレクトリ、または`/cores`ディレクトリに生成されます（システム構成によって異なります）。

以下のコマンドでgdbでプログラムをデバッグします

```
gdb php core
gdb php /tmp/core.1234
```

次に`bt`とEnterキーを押して、問題が発生した呼び出しスタックを表示します。
```
(gdb) bt
```

指定の呼び出しスタックフレームを查看するには、`f 数字`とEnterキーを押します。
```
(gdb) f 1
(gdb) f 0
```

上記の情報をすべてissueに掲示してください。
