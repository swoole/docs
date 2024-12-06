# Exemple simple

La plupart des fonctionnalités de `Swoole` ne peuvent être utilisées que dans un environnement de commande `cli`. Veuillez d'abord préparer un environnement de shell Linux. Vous pouvez utiliser `Vim`, `Emacs`, `PhpStorm` ou d'autres éditeurs pour écrire votre code, puis exécuter le programme depuis la ligne de commande avec l'instruction suivante :

```shell
php /chemin/vers/votre_fichier.php
```

Après avoir exécuté avec succès le programme serveur `Swoole`, si votre code ne contient aucune instruction `echo`, il n'y aura aucun affichage sur le écran, mais en réalité, le serveur est déjà en train d'écouter sur le port réseau, attendant que les clients établissent une connexion. Vous pouvez utiliser des outils et programmes clients correspondants pour vous connecter au serveur et effectuer des tests.

#### Gestion des processus

Par défaut, après avoir démarré le serveur `Swoole`, vous pouvez arrêter le service en utilisant `CTRL+C` dans la fenêtre qui s'ouvre, mais il y aura un problème si la fenêtre se ferme, il est donc nécessaire de démarrer en background, voir [Démarrage en tant que service](server/setting?id=daemonize) pour plus d'informations.

!> La plupart des exemples dans l'exemple simple sont écrits dans un style asynchrone, et la même fonction peut être réalisée avec le style de coroutines, voir [Serveur (style de coroutines)](coroutine/server.md).

!> La grande majorité des modules fournis par `Swoole` ne peuvent être utilisés que dans une console `cli`. Actuellement, seuls les [clients synchrones bloquants](/client) peuvent être utilisés dans un environnement PHP-FPM.
