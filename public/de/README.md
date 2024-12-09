# Swoole

?> `Swoole` ist ein auf der Sprache `C++` basierendes asynchrones Ereignisgetriebenes und cooperatives Parallel-Netzwerkkommunikationsmotor, der PHP [Cooperatives](/coroutine), [Hochleistungsnetzwerkprogrammierung](/question/use?id=how-is-the-performance-of-swoole) Unterstützung bietet. Es werden Netzwerkserver- und Client-Module für verschiedene Kommunikationsprotokolle bereitgestellt, die es ermöglichen, `TCP/UDP-Dienste`, `Hochleistungs-Web`, `WebSocket-Dienste`, `Internet der Dinge`, `Realtime-Kommunikation`, `Spiele`, `Microservices` usw. schnell und bequem zu implementieren, sodass PHP nicht mehr auf das traditionelle Web-Feld beschränkt ist.

## Swoole Klassendiagramm

!>Klicken Sie direkt auf den Link zur entsprechenden Dokumentseite

[//]: # (https://naotu.baidu.com/file/bd9d2ba7dfae326e6976f0c53f88b18c)

<embed src="/_images/swoole_class.svg" type="image/svg+xml" alt="Swoole Architektur diagramm" />

## offizielle Website

* [Swoole offizielle Website](//www.swoole.com)
* [Geschäftliche Produkte und Unterstützung](//business.swoole.com)
* [Swoole Fragen und Antworten](//wenda.swoole.com)

## Projektadresse

* [GitHub](//github.com/swoole/swoole-src) **（Bitte geben Sie ein Star, wenn Sie unterstützen）**
* [Gitee](//gitee.com/swoole/swoole)
* [PECL](//pecl.php.net/package/swoole)

## Entwicklungswerkzeuge

* [IDE Helper](https://github.com/swoole/ide-helper)
* [Yasd](https://github.com/swoole/yasd)
* [Debugger](https://github.com/swoole/debugger)

## Urheberrechtsinformationen

Der ursprüngliche Inhalt dieses Dokuments wurde aus den früheren alten Swoole-Dokumenten entnommen, mit dem Ziel, die seit langem beklagten Probleme mit den Dokumenten zu lösen. Es wird eine moderne Dokumentenorganisation verwendet, die nur Inhalte von `Swoole4` enthält. Viele falsche Inhalte aus den alten Dokumenten wurden korrigiert, die Dokumentendetails optimiert, Beispiele hinzugefügt und einige Lehrinhalte bereitgestellt, um Neulingen in Swoole freundlich zu begegnen.

Alle Inhalte dieses Dokuments, einschließlich aller Texte, Bilder und audiovisuellen Medien, sind urheberrechtlich **Shanghai Shiwu Network Technology Co., Ltd.** gehörig. Jede Medienplattform, Website oder Person kann sie in Form von Hyperlinks zitieren, aber ohne ein Abkommen darf sie in irgendeiner Form kopiert oder veröffentlicht werden.

## Initiatoren des Dokuments

* Yang Cai [GitHub](https://github.com/TTSimple)
* Guo Xinhua [Weibo](https://www.weibo.com/u/2661945152)
* [Lu Fei](https://github.com/sy-records) [Weibo](https://weibo.com/5384435686)

## ProblemFeedback

Über Probleme mit dem Inhalt dieses Dokuments (wie falsche Schriftzeichen, falsche Beispiele, fehlende Inhalte usw.) sowie Vorschläge und Anfragen sollten Sie diese **[swoole-inc/report](https://github.com/swoole-inc/report)** Projekt unter提交`issue` einreichen. Sie können auch direkt im oberen rechten Eck auf [Feedback](/?id=main) klicken, um zur Seite mit den `issue`s zu wechseln.

Sobald sie akzeptiert werden, wird der Name des Einreichers zur **[Dokumentenbeiträger](/CONTRIBUTING)** Liste hinzugefügt, um Dankbarkeit auszudrücken.

## Dokumentenprinzipien

Verwenden Sie eine klare Sprache, **versuchen Sie** so wenig wie möglich tiefe technische Details und einige tiefe Konzepte von Swoole vorzustellen. Tiefe Technologien können später in einem speziellen `hack` Kapitel gepflegt werden;

Wenn es um Konzepte geht, die nicht umgangen werden können, **müssen** sie an einem zentralen Ort vorgestellt werden,其他地方内链过去. Zum Beispiel: [Ereignishalter](/learn?id=什么是eventloop) ;

Beim Schreiben von Dokumenten müssen Sie denken umdrehen und sich von der Perspektive eines Neulings verabschieden, um zu sehen, ob andere es verstehen können;

Wenn sich die Funktionen später ändern, **müssen** alle betroffenen Bereiche geändert werden, nicht nur ein Ort;

Jeder Funktionsmodul **muss** ein vollständiges Beispiel haben;
