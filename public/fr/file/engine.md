# Opérations asynchrones sur fichiers

La [gestion unifiée des coroutines](/runtime) de `Swoole` permet de transformer facilement les opérations de fichiers PHP en exécutions asynchrones, bloquantes et synchrones en exécutions asynchrones non bloquantes. `Swoole` intègre deux stratégies différentes pour l'opération asynchrone des fichiers.

## Pool de threads

* Le `pool de threads` est la stratégie par défaut pour l'opération asynchrone des fichiers dans `Swoole`. Lorsque l'utilisateur lance une opération de fichier, `Swoole` envoie directement cette opération au `pool de threads`, où un thread subordonné s'occupe de l'opération de fichier. Une fois l'opération terminée, le contexte coroutine est rétabli.
* Toutes les fonctions d'opération de fichiers PHP peuvent être réalisées asynchronement via le `pool de threads`, telles que `file_get_contents`, `fopen`, etc.
* Sans aucune dépendance supplémentaire, elle offre une haute compatibilité et peut être utilisée directement.

## io_uring

* L'`io_uring` est une stratégie intégrée dans `Swoole v6.0` qui utilise l'`io_uring` et `epoll` pour réaliser l'asynchrone.
* Avec un haut débit, il peut gérer un grand nombre d'opérations asynchrones de fichiers.
* Il nécessite une version Linux spécifique et dépend de la bibliothèque partagée `liburing`. Certains systèmes d'exploitation ne peuvent pas utiliser cette caractéristique.
* Étant basé sur les descripteurs de fichiers pour réaliser l'asynchrone des fichiers, il ne prend en charge qu'une petite partie des fonctions PHP de gestion de fichiers.
* Il exige une version Linux du noyau assez élevée.

!> La fonctionnalité `io_uring` est disponible uniquement après avoir installé `liburing` et compilé `Swoole` avec l'option `--enable-iouring`.

!> L'activation de `io_uring` ne remplace pas le mode `pool de threads`. Certaines fonctions qui ne peuvent pas être coroutinées par `io_uring` sont toujours traitées par le `pool de threads`.

!> `io_uring` ne prend en charge que les fonctions `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, `filesize`.
