# Soumettre un rapport d'erreur


## Remarques préalables

Lorsque vous pensez avoir découvert une erreur du noyau Swoole, veuillez soumettre un rapport. Les développeurs du noyau Swoole peuvent ne pas être au courant de l'existence du problème, et à moins que vous ne le signaliez activement, il peut être difficile de découvrir et de corriger l'erreur. Vous pouvez soumettre un rapport d'erreur (c'est-à-dire cliquer sur le bouton `Nouvel issue` en haut à droite) dans la [section des issues de GitHub](https://github.com/swoole/swoole-src/issues), où les rapports d'erreurs seront traités en priorité.

Veuillez ne pas envoyer de rapports d'erreur par courriel ou par lettre privée. La section des issues de GitHub peut également être utilisée pour soumettre toute demande ou suggestion concernant Swoole.

Avant de soumettre un rapport d'erreur, veuillez lire la section suivante sur **la façon de soumettre un rapport d'erreur**.


## Création d'une nouvelle issue

Lors de la création d'une issue, le système fournira le modèle suivant, que vous devez remplir attentivement, sinon l'issue pourrait être ignorée en raison du manque d'informations :

```markdown

Veuillez répondre à ces questions avant de soumettre votre issue. Merci !
> Veuillez répondre aux questions suivantes avant de soumettre une issue :
	
1. Qu'avez-vous fait ? Si possible, fournissez un script simple pour reproduire l'erreur.
> Veuillez décrire en détail le processus qui a conduit à l'erreur,附上 le code pertinent, et si possible fournir un script simple qui peut être utilisé pour reproduire l'erreur de manière fiable.

2. Qu'espérait-vous voir ?
> Quel était le résultat espéré ?

3. Qu'avez-vous vu à la place ?
> Quel était le résultat réel lors de l'exécution ?

4. Quelle est la version de Swoole que vous utilisez (`php --ri swoole`) ?
> Quelle est votre version ? Collez le contenu imprimé par `php --ri swoole`	

5. Quelle est votre configuration de l'environnement machine utilisée (y compris la version du noyau, de PHP et du compilateur gcc) ?
> Quelle est votre configuration système de l'ordinateur utilisé (y compris la version du noyau, de PHP et du compilateur gcc) ?	
> Vous pouvez utiliser les commandes `uname -a`, `php -v`, `gcc -v` pour imprimer les informations

```

Il est crucial de fournir un **script simple qui peut être utilisé pour reproduire l'erreur de manière fiable**. Sinon, vous devez fournir autant d'informations que possible pour aider les développeurs à déterminer la cause de l'erreur.


## Analyse de la mémoire (recommandée)

Plus souvent, Valgrind peut découvrir les problèmes de mémoire mieux que gdb. Exécutez votre programme avec la commande suivante jusqu'à ce qu'une erreur se produise :

```shell
USE_ZEND_ALLOC=0 valgrind --log-file=/tmp/valgrind.log php your_file.php
```

* Lorsque le programme rencontre une erreur, vous pouvez quitter en tapant `ctrl+c`, puis envoyer le fichier `/tmp/valgrind.log` pour aider l'équipe de développement à localiser l'erreur.

## concernant les erreurs de segmentation (génération de core dump)

De plus, dans certains cas spéciaux, vous pouvez utiliser des outils de débogage pour aider les développeurs à localiser le problème

```shell
WARNING	swManager_check_exit_status: worker#1 abnormal exit, status=0, signal=11
```

Lorsque un tel message apparaît dans le journal Swoole (signal 11), cela indique qu'un `génération de core dump` a eu lieu. Vous devez utiliser un outil de débogage pour déterminer l'emplacement de l'erreur.

> Pour utiliser `gdb` pour déboguer Swoole, vous devez ajouter le paramètre `--enable-debug` lors de la compilation pour conserver plus d'informations.

Activer le fichier de génération de core dump
```shell
ulimit -c unlimited
```

Générez l'erreur et le fichier de génération de core dump se créera dans le répertoire du programme ou dans le répertoire racine du système ou dans le répertoire `/cores` (selon la configuration de votre système).

Entrez dans le débogueur gdb avec la commande suivante

```
gdb php core
gdb php /tmp/core.1234
```

Ensuite, entrez `bt` et appuyez sur Enter pour voir l'appel de la pile qui a provoqué l'erreur
```
(gdb) bt
```

Vous pouvez afficher un frame d'appel spécifique en entrant `f numéro`
```
(gdb) f 1
(gdb) f 0
```

Mettez toutes ces informations dans l'issue.
