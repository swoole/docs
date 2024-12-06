# Swoole

?> `Swoole` est un moteur de communication réseau parallèle écrit en `C++` qui repose sur l'événement non bloquant et les coroutines pour fournir à `PHP` un soutien à la programmation réseau [coroutines](/coroutine) et à haute performance [/question/use?id=how-is-the-performance-of-swoole). Il offre une variété de modules de serveur et de client pour différents protocoles de communication, permettant une mise en œuvre rapide et facile de services `TCP/UDP`, de `web` haute performance, de `services WebSocket`, d'`Internet des objets`, de `communication en temps réel`, de `jeu`, de `services microservices`, etc., libérant `PHP` de la limitation au domaine traditionnel du web.

## Dessin de classe Swoole

!> Vous pouvez cliquer directement sur le lien pour accéder à la page de documentation correspondante

[//]: # (https://naotu.baidu.com/file/bd9d2ba7dfae326e6976f0c53f88b18c)

<embed src="/_images/swoole_class.svg" type="image/svg+xml" alt="Dessin d'architecture Swoole" />

## Site officiel

* [Site officiel Swoole](//www.swoole.com)
* [Produits commerciaux et soutien](//business.swoole.com)
* [Questions et réponses Swoole](//wenda.swoole.com)

## Adresse du projet

* [GitHub](//github.com/swoole/swoole-src) **（ Veuillez faire un like si vous soutenez）**
* [Gitee](//gitee.com/swoole/swoole)
* [PECL](//pecl.php.net/package/swoole)

## Outils de développement

* [IDE Helper](https://github.com/swoole/ide-helper)
* [Yasd](https://github.com/swoole/yasd)
* [Débogueur](https://github.com/swoole/debugger)

## Informations de droits d'auteur

Le contenu original de cet文档 est tiré de l'ancien documentation de Swoole, dans le but de résoudre les problèmes de documentation que tout le monde se plaignait de, en utilisant une forme moderne d'organisation des documents, ne contenant que le contenu de `Swoole4`, en corrigeant un grand nombre d'erreurs dans l'ancien documentation, en optimisant les détails du document, en ajoutant des exemples de code et quelques contenus d'enseignement, rendant le document plus amical pour les nouveaux venus dans Swoole.

Tout le contenu de ce document, y compris tous les textes, images et médias audiovisuels, est la propriété exclusive de **Shanghai Shiwu Network Technology Co., Ltd.**, et peut être cité sous forme de lien externe par quiconque, mais ne doit pas être reproduit ou publié sans autorisation formelle.

## Initiateurs du document

* Yang Cai [GitHub](https://github.com/TTSimple)
* Guo Xinhua [Weibo](https://www.weibo.com/u/2661945152)
* [Lu Fei](https://github.com/sy-records) [Weibo](https://weibo.com/5384435686)

## Feedback sur les problèmes

Pour les problèmes de contenu (tels que des fautes d'orthographe, des erreurs d'exemples, des manques de contenu, etc.) et les suggestions de besoins dans ce document, veuillez soumettre une `issue` au projet [swoole-inc/report](https://github.com/swoole-inc/report), ou cliquez directement sur le bouton [Feedback](/?id=main) dans le coin supérieur droit pour accéder à la page des `issues`.

Une fois accepté, l'information du soumissionnaire sera ajoutée à la liste des [contributeurs au document](/CONTRIBUTING) en signe de remerciement.

## Principe du document

Utiliser un langage direct, **essayer** de ne pas trop introduire les détails techniques de base de Swoole et certains concepts de base, ces détails techniques peuvent être gérés dans un chapitre séparé pour les hacks à l'avenir ;

Lorsqu'il est impossible de contourner certains concepts, **il est essentiel** d'avoir un endroit centralisé pour présenter ce concept, et de relier à d'autres endroits dans le document. Par exemple : [l'événement loop](/learn?id=what-is-eventloop) ;

Lors de l'écriture du document, il faut changer de perspective, se demander si les autres peuvent comprendre ;

Lorsque des modifications fonctionnelles apparaissent à l'avenir, **il est certain** de devoir modifier toutes les parties concernées, et non seulement un endroit ;

Chaque module fonctionnel **doit** avoir un exemple complet ;
