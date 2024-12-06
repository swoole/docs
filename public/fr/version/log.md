# Historique des mises à jour

À partir de la version `v1.5`, un rigorous historique des mises à jour a été établi. Actuellement, le délai moyen entre deux grandes mises à jour est de six mois, avec des mises à jour mineures toutes les `2-4` semaines.

## Versions de PHP recommandées

* 8.0
* 8.1
* 8.2
* 8.3

## Versions de Swoole recommandées
`Swoole6.x` et `Swoole5.x`

La différence entre les deux est que `v6.x` est une branche de développement active, tandis que `v5.x` est une branche **non** active, se contentant de corriger des `BUG`s.

!> Les versions `v4.x` et supérieures peuvent désactiver la feature de coroutines en setting [enable_coroutine](/server/setting?id=enable_coroutine), les rendant non coroutines.

## Types de versions

* `alpha` Version de prévisualisation des fonctionnalités, indiquant que les tâches planifiées dans le développement sont terminées et mises en prévisualisation ouverte, qui peut contenir de nombreux `BUG`s.
* `beta` Version de test, indiquant qu'elle peut être utilisée pour des tests dans un environnement de développement, qui peut contenir des `BUG`s.
* `rc[1-n]` Version de candidate à la publication, indiquant qu'elle est entrée dans la période de publication, en cours de tests à grande échelle, et qu'il est toujours possible de découvrir des `BUG`s pendant cette période.
* Sans suffixe signifie que c'est une version stable, indiquant que cette version est développée et prête à être utilisée officiellement.

## Verrouillage de la version actuelle

```shell
php --ri swoole
```

## v6.0.0

### Nouvelles fonctionnalités

- `Swoole` prend en charge le mode multithread, qui peut être utilisé lorsque `php` est en mode ZTS et que `--enable-swoole-thread` est activé lors de la compilation de `Swoole`.

- Ajout de la classe de gestion des threads `Swoole\Thread`. @matyhtf

- Ajout du verrou de thread `Swoole\Thread\Lock`. @matyhtf

- Ajout du compteur atomique de thread `Swoole\Thread\Atomic`, `Swoole\Thread\Atomic\Long`. @matyhtf

- Ajout de conteneurs de concurrence sûre `Swoole\Thread\Map`, `Swoole\Thread\ArrayList`, `Swoole\Thread\Queue`. @matyhtf

- Prise en charge des opérations asynchrones de fichiers avec `iouring` comme moteur sous-jacent, installation de `liburing` et activation de `--enable-iouring` lors de la compilation de `Swoole`, les fonctions `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, `filesize` et autres opérations asynchrones seront implémentées par `iouring`. @matyhtf @NathanFreeman
- mise à niveau de la version de `Boost Context` à 1.84. Les processeurs Loongson peuvent également utiliser des coroutines maintenant. @NathanFreeman

### Réparations de bugs

- Réparation du problème d'installation via `pecl`. @remicollet

- Réparation du problème où le client `Swoole\Coroutine\FastCGI\Client` ne pouvait pas configurer la keepalive. @NathanFreeman

- Réparation du problème où les paramètres de demande dépassant `max_input_vars` provoquaient une erreur et entraînaient un redémarrage continu du processus. @NathanFreeman

- Réparation du problème inconnu survenant lors de l'utilisation de `Swoole\Event::wait()` dans les coroutines. @matyhtf

- Réparation du problème de non-supervision de pty lors de la coroutinisation de `proc_open`. @matyhtf

- Réparation du problème de segmentation fault avec `pdo_sqlite` dans PHP 8.3. @NathanFreeman

- Réparation des avertissements inutiles lors de la compilation de `Swoole`. @Appla @NathanFreeman

- Réparation du problème où l'appel au zend_fetch_resource2_ex échouerait si `STDOUT/STDERR` étaient déjà fermés. @Appla @matyhtf

- Réparation de la configuration invalide de `set_tcp_nodelay`. @matyhtf

- Réparation occasionnelle du problème de branche inaccessible lors de l'upload de fichiers. @NathanFreeman

- Réparation du problème où l'établissement de `dispatch_func` provoquerait une erreur au niveau du PHP. @NathanFreeman

- Réparation du fait que AC_PROG_CC_C99 est obsolète dans autoconf >= 2.70. @petk

- Capture des exceptions levées lors de la création manquée de threads. @matyhtf

- Réparation du problème de définition non définie de `_tsrm_ls_cache`. @jingjingxyk
- Réparation du problème de compilation fatale avec `GCC 14`. @remicollet

### Optimisation du noyau

- Suppression des vérifications inutiles des `structs socket`. @petk

- Mise à niveau de la bibliothèque Swoole. @deminy

- Ajout du soutien pour le code d'état 451 dans `Swoole\Http\Response`. @abnegate

- Synchronisation du code des opérations de fichier entre différentes versions de PHP. @NathanFreeman

- Synchronisation du code des opérations pdo entre différentes versions de PHP. @NathanFreeman

- Optimisation du code de la fonction `Socket::ssl_recv()`. @matyhtf

- Optimisation de config.m4, certaines configurations peuvent être établies via `pkg-config` pour la position des bibliothèques dépendantes. @NathanFreeman

- Optimisation du problème de l'utilisation d'arrays dynamiques lors de l'analyse des en-têtes de demande. @NathanFreeman

- Optimisation de la question de la durée de vie des descripteurs de fichiers `fd` en mode multithread. @matyhtf

- Optimisation de la logique de base des coroutines. @matyhtf

### Dépréciation

- Plus de soutien pour `PHP 8.0`.

- Plus de soutien pour le client coroutine `Swoole\Coroutine\MySQL`.

- Plus de soutien pour le client coroutine `Swoole\Coroutine\Redis`.

- Plus de soutien pour le client coroutine `Swoole\Coroutine\PostgreSQL`.

## v5.1.3

### Réparations de bugs :

- Réparation du problème d'installation via `pecl`.

- Réparation du problème où le client `Swoole\Coroutine\FastCGI\Client` ne pouvait pas configurer la keepalive.

- Réparation du problème où les paramètres de demande dépassant `max_input_vars` provoquaient une erreur et entraînaient un redémarrage continu du processus.

- Réparation du problème inconnu survenant lors de l'utilisation de `Swoole\Event::wait()` dans les coroutines.

- Réparation du problème de non-supervision de pty lors de la coroutinisation de `proc_open`.

- Réparation du problème de segmentation fault avec `pdo_sqlite` dans PHP 8.3.

- Réparation des avertissements inutiles lors de la compilation de `Swoole`.

- Réparation du problème où l'appel au zend_fetch_resource2_ex échouerait si `STDOUT/STDERR` étaient déjà fermés.

- Réparation de la configuration invalide de `set_tcp_nodelay`.

- Réparation occasionnelle du problème de branche inaccessible lors de l'upload de fichiers.

- Réparation du problème où l'établissement de `dispatch_func` provoquerait une erreur au niveau du PHP.
- Réparation du fait que AC_PROG_CC_C99 est obsolète dans autoconf >= 2.70.

### Optimisation du noyau :

- Suppression des vérifications inutiles des `structs socket`.

- Mise à niveau de la bibliothèque Swoole.

- Ajout du soutien pour le code d'état 451 dans `Swoole\Http\Response`.

- Synchronisation du code des opérations de fichier entre différentes versions de PHP.

- Synchronisation du code des opérations pdo entre différentes versions de PHP.

- Optimisation du code de la fonction `Socket::ssl_recv()`.

- Optimisation de config.m4, certaines configurations peuvent être établies via `pkg-config` pour la position des bibliothèques dépendantes. 
- Optimisation du problème de l'utilisation d'arrays dynamiques lors de l'analyse des en-têtes de demande. 

## v5.1.2

### Réparations de bugs

- Prise en charge de l'embedding sapi.

- Réparation de la compatibilité avec ZEND_CHECK_STACK_LIMIT dans PHP 8.3.

- Réparation du problème où le contenu complet des fichiers renvoyés par une demande de portée ne contenait pas de tête de réponse Content-Range.

- Réparation des cookies tronqués.

- Réparation du problème de crash de native-curl dans PHP 8.3.
- Réparation du bug de l'erreur `errno` invalide après `Server::Manager::wait()`.
- Réparation d'une erreur d'orthographe dans HTTP2.

### Optimisation

- Optimisation de la performance du serveur HTTP.
-Ajout de `CLOSE_SERVICE_RESTART`, `CLOSE_TRY_AGAIN_LATER`, `CLOSE_BAD_GATEWAY` comme raisons valides pour fermer un websocket


## v5.1.1



### Bug fixes

- Réparation du problème de fuite de mémoire du client HTTP协程.

- Réparation du problème de non-co-routineization de `pdo_odbc`.

- Réparation du problème d'exécution d'erreur de `socket_import_stream()`.

- Réparation du problème de `Context::parse_multipart_data()` qui ne pouvait pas gérer une demande vide.

- Réparation du problème où les paramètres du client PostgreSQL co-routine ne fonctionnaient pas.

- Réparation du bug où `curl` se crasheait lors de la destruction.

- Réparation du problème de compatibilité avec la nouvelle version de `xdebug` pour Swoole5.x.

- Réparation du problème où le changement de contexte co-routine pendant le processus d'autoload de classe provoquait une indication que la classe n'existait pas.

- Réparation du problème de compilation de `swoole` sur OpenBSD.


## v5.1.0




### Nouvelles fonctionnalités

-Ajout du soutien au co-routineage pour `pdo_pgsql`

-Ajout du soutien au co-routineage pour `pdo_odbc`

-Ajout du soutien au co-routineage pour `pdo_oci`

-Ajout du soutien au co-routineage pour `pdo_sqlite`
-Ajout de la configuration du pool de connexion pour `pdo_pgsql`, `pdo_odbc`, `pdo_oci`, `pdo_sqlite`




### Améliorations
- Amélioration des performances du `Http\Server`, pouvant atteindre jusqu'à `60%` dans des cas extrêmes




### Réparations

- Réparation de la fuite de mémoire causée par le client co-routine WebSocket à chaque demande

- Réparation du problème où l'arrêt élégant du serveur HTTP co-routine ne faisait pas sortir les clients

- Réparation du problème où l'ajout de l'option `--enable-thread-context` lors de la compilation rendait la fonction `Process::signal()` inefficace

- Réparation du problème d'erreur de comptage des connexions lorsque le processus ne se terminait pas normalement en mode `SWOOLE_BASE`

- Réparation de l'erreur de signature de la fonction `stream_select()`

- Réparation de l'erreur de sensibilité au casse des informations MIME du fichier

- Réparation de l'erreur d'orthographe de `Http2\Request::$usePipelineRead` qui provoquait une alerte dans l'environnement PHP8.2

- Réparation du problème de fuite de mémoire en mode `SWOOLE_BASE`

- Réparation du problème de fuite de mémoire dû à l'établissement d'un cookie avec une date d'expiration par `Http\Response::cookie()`

- Réparation du problème de fuite de connexion en mode `SWOOLE_BASE`




### noyau

- Réparation du problème de signature de la fonction `php_url_encode` de swoole sous PHP8.3

- Réparation du problème des options de test unitaire

- Optimisation et refactoring du code

- Compatibilité avec PHP8.3
- Ne prend pas en charge la compilation sur des systèmes d'exploitation 32 bits


## v5.0.3




### Améliorations

-Ajout de l'option `--with-nghttp2_dir`, utilisée pour utiliser la bibliothèque `nghttp2` du système

-Prise en charge des options liées à la longueur ou à la taille des octets

-Ajout de la fonction `Process\Pool::sendMessage()`

- `Http\Response:cookie()` prend en charge `max-age`




### Réparations
-Réparation du problème de fuite de mémoire causé par les événements `Server task/pipemessage/finish`




### noyau

- Les conflits dans les en-têtes HTTP ne provoqueront plus d'erreur
- La fermeture des connexions du serveur ne provoquera plus d'erreur


## v5.0.2




### Améliorations

-Prise en charge de la configuration par défaut pour HTTP2

-Prise en charge de xdebug version 8.1 ou supérieure

-Refactoring du curl originel pour prendre en charge les handle curl avec plusieurs sockets, par exemple pour le protocole ftp curl

-Ajout du paramètre `who` dans `Process::setPriority/getPriority`

-Ajout de la méthode `Coroutine\Socket::getBoundCid()`

-Ajustement du paramètre par défaut de la longueur pour les méthodes `Coroutine\Socket::recvLine/recvWithBuffer` à `65536`

-Refactoring des caractéristiques de sortie inter-co-routine pour rendre la libération de mémoire plus sûre et résoudre le problème de crash en cas d'erreur fatale

-Ajout de la propriété `socket` aux classes `Coroutine\Client`, `Coroutine\Http\Client`, `Coroutine\Http2\Client`, permettant une manipulation directe des ressources socket

-Permettre au serveur HTTP de envoyer un fichier vide au client HTTP2

-Prise en charge du redémarrage élégant du serveur HTTP co-routine. Lorsque le serveur se ferme, les connexions des clients ne sont plus forcées de se fermer, il s'arrête seulement d'écouter de nouvelles demandes

-Ajout de `pcntl_rfork` et `pcntl_sigwaitinfo` à la liste des fonctions dangereuses, qui seront fermées lors du démarrage du conteneur de co-routine

-Refactoring du gestionnaire de processus en mode `SWOOLE_BASE`, avec des comportements de fermeture et de rechargement cohérents avec `SWOOLE_PROCESS`


## v5.0.1




### Améliorations

-Prise en charge de PHP-8.2, amélioration du traitement des exceptions co-routine, compatible avec `ext-soap`

-Ajout du soutien aux LOB pour le client co-routine pgsql

-Amélioration du client websocket, l'en-tête est mis à jour pour inclure `websocket` au lieu de l'utilisation de `=`

-Optimisation du client HTTP, désactivation de `keep-alive` lorsque le serveur envoie `connection close`

-Optimisation du client HTTP, interdiction d'ajouter l'en-tête `Accept-Encoding` en l'absence de bibliothèque de compression

-Amélioration des informations de débogage, les mots de passe sont définis comme des paramètres sensibles sous PHP-8.2

-Renforcement de `Server::taskWaitMulti()`, qui ne sera plus bloqué dans un environnement co-routine

-Optimisation des fonctions de journalisation, qui ne produiront plus d'informations sur le écran en cas d'échec de l'écriture dans un fichier de journal




### Réparations

-Réparation du problème de compatibilité des paramètres avec `Coroutine::printBackTrace()` et `debug_print_backtrace()`

-Réparation du soutien des ressources de socket pour `Event::add()`

-Réparation de l'erreur de compilation en l'absence de `zlib`

-Réparation du crash lors de la décompression d'une string inattendue

-Réparation du problème où les horloges de moins de `1ms` étaient forcées à être fixées à `0`

-Réparation du crash dû à l'utilisation de `Table::getMemorySize()` avant l'ajout de colonnes

-Modification du nom du paramètre d'expiration de la méthode `Http\Response::setCookie()` en `expires`


## v5.0.0




### Nouvelles fonctionnalités

-Ajout de l'option `max_concurrency` pour le `Server`

-Ajout de l'option `max_retries` pour `Coroutine\Http\Client`

-Ajout de l'option globale `name_resolver`. Ajout de l'option `upload_max_filesize` pour le `Server`

-Ajout de la méthode `Coroutine::getExecuteTime()`

-Ajout du mode de dispatch `SWOOLE_DISPATCH_CONCURRENT_LB` pour le `Server`

-Renforcement du système de types, avec des types pour tous les paramètres et les retours des fonctions

-Optimisation du traitement des erreurs, tous les constructeurs lancent des exceptions en cas d'échec

-Ajustement du mode par défaut du `Server`, qui est maintenant le mode `SWOOLE_BASE` par défaut

-Déplacement du client co-routine pgsql vers la bibliothèque centrale.包含了`4.8.x`分支中的所有`bug`修复




### Retrait

-Retrait du style de classe PSR-0

-Retrait de la fonction automatique d'ajout d'attente d'événements lors de la fermeture des fonctions

-Retrait des surnoms pour `Server::tick/after/clearTimer/defer`

-Retrait de `--enable-http2/--enable-swoole-json`, qui sont maintenant activés par défaut




### Dépréciation

-Les clients co-routine `Coroutine\Redis` et `Coroutine\MySQL` sont dépréciés par défaut


## v4.8.13
### Renforcement

- Refaire le cURL natif pour prendre en charge les handleurs cURL avec plusieurs sockets, par exemple pour le protocole FTP cURL

- Prendre en charge le paramètre manuel de `http2`

- Améliorer le `client WebSocket`, l'en-tête a été mis à jour pour inclure `websocket` au lieu de `equal`

- Optimiser le client HTTP, désactiver `keep-alive` lorsque le serveur envoie une fermeture de connexion

- Améliorer les informations de débogage, dans PHP-8.2, la passe a été définie comme un paramètre sensible

- Prendre en charge les `demandes de plage HTTP`

### Réparation

- Réparer le problème de compatibilité des paramètres entre `Coroutine::printBackTrace()` et `debug_print_backtrace()`

- Réparer le problème d'analyse de la longueur erronée lorsque le serveur `WebSocket` active à la fois les protocoles `HTTP2` et `WebSocket`

- Réparer le problème de fuite de mémoire lors de l'émission de `send_yield` dans `Server::send()`, `Http\Response::end()`, `Http\Response::write()` et `WebSocket/Server::push()`

- Réparer le problème de crash qui se produit lorsqu'on utilise `Table::getMemorySize()` avant d'ajouter une colonne.

## v4.8.12

### Renforcement

- Prendre en charge PHP8.2

- La fonction `Event::add()` prend en charge les ressources de sockets

- La fonction `Http\Client::sendfile()` prend en charge les fichiers supérieurs à 4G

- La fonction `Server::taskWaitMulti()` prend en charge l'environnement de coroutines

### Réparation

- Réparer le problème qui se produit lorsque le corps multipart reçu est incorrect et qui provoque une erreur

- Réparer le problème d'erreur dû à un délai de temps de timeout inférieur à `1ms`

- Réparer le problème de verrouillage dû à un disque plein

## v4.8.11

### Renforcement

- Prendre en charge la mécanisme de défense de sécurité `Intel CET`

- Ajouter la propriété `$ssl` à `Server`

- Lors de la compilation de `swoole` avec `pecl`, ajouter la propriété `enable-cares`

- Refaire l'interpréteur `multipart_parser`

### Réparation

- Réparer l'exception qui se produit lors de l'utilisation des connexions persistantes PDO

- Réparer le problème de segmentation fault dû à l'utilisation des coroutines dans le destructeur

- Réparer l'information d'erreur incorrecte dans la méthode `Server::close()`

## v4.8.10

### Réparation

- Lorsque le paramètre de timeout de `stream_select` est inférieur à `1ms`, le reset à `0`

- Réparer le problème qui se produit lorsque l'ajout de `-Werror=format-security` lors de la compilation conduit à une compilation manquée

- Réparer le problème de segmentation fault dû à l'utilisation de `curl` dans `Swoole\Coroutine\Http\Server`

## v4.8.9

### Renforcement

- Prendre en charge l'option `http_auto_index` sous le serveur HTTP2

### Réparation

- Optimiser l'interpréteur de `Cookie`, prendre en charge l'option `HttpOnly`

- Réparer #4657, Hook le problème de type de retour de la méthode `socket_create`

- Réparer la fuite de mémoire de `stream_select`

### Mises à jour CLI

- Sous `CygWin`, il y a une chaîne de certificats SSL portée, ce qui résout le problème d'authentification SSL erronée

- Mettre à jour vers `PHP-8.1.5`

## v4.8.8

### Optimisation

- Réduire la taille maximale de la mémoire tampon SW_IPC_BUFFER_MAX_SIZE à 64k

- Optimiser la configuration de l'en-tête table_size du HTTP2

### Réparation

- Réparer le problème d'erreur de socket massif lors de l'utilisation de la fonction `enable_static_handler` pour télécharger des fichiers statiques

- Réparer l'erreur NPN du serveur HTTP2

## v4.8.7

### Renforcement

- Ajouter la prise en charge de `curl_share`

### Réparation

- Réparer le problème d'erreur indéfinie sous l'architecture arm32

- Réparer la compatibilité avec `clock_gettime()`

- Réparer le problème de défaillance du serveur PROCESS lorsqu'il manque une grande quantité de mémoire dans le noyau

## v4.8.6

### Réparation

- Ajouter un préfixe à la API boost/context

- Optimiser les options de configuration

## v4.8.5

### Réparation

- Rétablir le type de paramètre de Table

- Réparer le crash lorsque l'utilisation du protocole WebSocket reçoit des données erronées

## v4.8.4

### Réparation

- Réparer la compatibilité des hooks de sockets avec PHP-8.1

- Réparer la compatibilité de Table avec PHP-8.1

- Réparer le problème où, dans certains cas, le serveur HTTP au style de coroutines ne peut pas interpréter correctement les paramètres POST de `Content-Type` comme `application/x-www-form-urlencoded`

## v4.8.3

### Nouvelles API

- Ajouter la méthode `Coroutine\Socket::isClosed()`

### Réparation

- Réparer le problème de compatibilité du hook native cURL avec PHP8.1

- Réparer le problème de compatibilité des hooks de sockets avec PHP8

- Réparer le problème de valeur de retour erronée des fonctions de hook de sockets

- Réparer le problème de l'incapacité à 设置 content-type avec la méthode sendfile du serveur HTTP2

- Optimiser les performances de l'en-tête date du serveur HTTP, en ajoutant un cache

## v4.8.2

### Réparation

- Réparer le problème de fuite de mémoire du hook de proc_open

- Réparer le problème de compatibilité du hook native cURL avec PHP-8.0 et PHP-8.1

- Réparer le problème de fermeture不正常 des connexions dans le processus Manager

- Réparer le problème de l'incapacité à utiliser la méthode sendMessage dans le processus Manager

- Réparer le problème d'analyse anormale de données POST volumineuses dans le serveur `Coroutine\Http\Server`

- Réparer le problème de non-sortie directe en cas d'erreur fatale dans l'environnement PHP8

- Ajuster la configuration de la configuration `max_concurrency` des coroutines, autorisée uniquement dans `Co::set()`

- Ajuster la méthode `Coroutine::join()` pour ignorer les coroutines inexistantes

## v4.8.1

### Nouvelles API

- Ajouter les fonctions `swoole_error_log_ex()` et `swoole_ignore_error()` (#4440) (@matyhtf)

### Renforcement

- Migrer l'API admin de ext-swoole_plus vers ext-swoole (#4441) (@matyhtf)

- Le serveur admin ajoute la commande get_composer_packages (swoole/library@07763f46) (swoole/library@8805dc05) (swoole/library@175f1797) (@sy-records) (@yunbaoi)

- Ajouter une limite aux demandes POST pour les opérations d'écriture (swoole/library@ac16927c) (@yunbaoi)

- Le serveur admin prend en charge l'obtention d'informations sur les méthodes de classe (swoole/library@690a1952) (@djw1028769140) (@sy-records)

- Optimiser le code du serveur admin (swoole/library#128) (swoole/library#131) (@sy-records)

- Le serveur admin prend en charge plusieurs objectifs de demande simultanés et plusieurs API de demande simultanées (swoole/library#124) (@sy-records)

- Le serveur admin prend en charge l'obtention d'informations sur les interfaces (swoole/library#130) (@sy-records)

- SWOOLE_HOOK_CURL prend en charge CURLOPT_HTTPPROXYTUNNEL (swoole/library#126) (@sy-records)

### Réparation

- Interdire l'appel simultané à la même coroutine avec la méthode join (#4442) (@matyhtf)

- Réparer le problème de libération accidentelle du verrou atomique de Table (#4446) (@Txhua) (@matyhtf)

- Réparer le problème de perte des options helper (swoole/library#123) (@sy-records)

- Réparer l'erreur de paramètre de la commande get_static_property_value (swoole/library#129) (@sy-records)

## v4.8.0

### Changements incompatibles vers le bas

- Dans la mode base, le callback onStart s'exécutera toujours au démarrage du premier processus de travail (avec un identifiant de worker de 0), avant d'exécuter onWorkerStart (#4389) (@matyhtf)

### Nouvelles API

- Ajouter la méthode `Co::getStackUsage()` (#4398) (@matyhtf) (@twose)

- Ajouter certaines API de `Coroutine\Redis` (#4390) (@chrysanthemum)

- Ajouter la méthode `Table::stats()` (#4405) (@matyhtf)

- Ajouter la méthode `Coroutine::join()` (#4406) (@matyhtf)

### Nouvelles fonctionnalités

- Prendre en charge la commande server (#4389) (@matyhtf)

- Prendre en charge l'événement de callback onBeforeShutdown du serveur (#4415) (@matyhtf)
### Renforcement



- Lorsqu'un paquet WebSocket échoue, set un code d'erreur (swoole/swoole-src@d27c5a5) (@matyhtf)

- Ajout du champ `Timer::exec_count` (#4402) (@matyhtf)

- Hook de support pour l'utilisation de la configuration open_basedir lors de la création de directories (#4407) (@NathanFreeman)

- Library ajout du script vendor_init.php (swoole/library@6c40b02) (@matyhtf)

- SWOOLE_HOOK_CURL prend en charge l'option CURLOPT_UNIX_SOCKET_PATH (swoole/library#121) (@sy-records)

- Le client prend en charge l'établissement de la configuration ssl_ciphers (#4432) (@amuluowin)
- Ajout de nouvelles informations à la méthode `Server::stats()` (#4410) (#4412) (@matyhtf)


### Réparation



- Réparation du décodage inutile des noms de fichiers lors de l'upload de fichiers (swoole/swoole-src@a73780e) (@matyhtf)

- Réparation du problème de size_max_frame_http2 (#4394) (@twose)

- Réparation du bug de curl_multi_select (#4393) (#4418) (@matyhtf)

- Réparation des options de coroutine manquantes (#4425) (@sy-records)
- Réparation du problème où la connexion ne peut pas être fermée lorsque le tampon d'envoi est plein (swoole/swoole-src@2198378) (@matyhtf)


## v4.7.1


### Renforcement



- `System::dnsLookup` prend en charge la recherche dans `/etc/hosts` (#4341) (#4349) (@zmyWL) (@NathanFreeman)

-Ajout du soutien pour le contexte boost sur mips64 (#4358) (@dixyes)

- `SWOOLE_HOOK_CURL` prend en charge l'option `CURLOPT_RESOLVE` (swoole/library#107) (@sy-records)

- `SWOOLE_HOOK_CURL` prend en charge l'option `CURLOPT_NOPROGRESS` (swoole/library#117) (@sy-records)

-Ajout du soutien pour le contexte boost sur riscv64 (#4375) (@dixyes)


### Réparation



- Réparation de l'erreur de mémoire générée par PHP-8.1 lors de la fermeture (#4325) (@twose)

- Réparation des classes irréalisables de la version 8.1.0beta1 (#4335) (@remicollet)

- Réparation du problème de création de directories par des coroutines en recursion (#4337) (@NathanFreeman)

- Réparation du problème d'timeout occasionnel de transfert de gros fichiers avec curl native sur le réseau extérieur, ainsi que du crash lorsque l'API de fichier coroutine est utilisée dans la fonction CURLWRITEFUNCTION (#4360) (@matyhtf)
- Réparation du problème où `PDOStatement::bindParam()` attend un argument 1 de type chaîne (#4368) (@sy-records)


## v4.7.0


### Nouvelles API



- Ajout de la méthode `Process\Pool::detach()` (#4221) (@matyhtf)

- Le `Server` prend en charge la fonction de rappel `onDisconnect` (#4230) (@matyhtf)

- Ajout des méthodes `Coroutine::cancel()` et `Coroutine::isCanceled()` (#4247) (#4249) (@matyhtf)

- Le `Http\Client` prend en charge les options `http_compression` et `body_decompression` (#4299) (@matyhtf)


### Renforcement



- Prise en charge des clients MySQL coroutines lors de la préparation avec une contrainte stricte sur le type des champs (#4238) (@Yurunsoft)

- Prise en charge de la bibliothèque `c-ares` pour DNS (#4275) (@matyhtf)

- Le `Server` prend en charge la configuration de la détection de heartbeat pour différents ports lors de l'écoute sur plusieurs ports (#4290) (@matyhtf)

- Le `dispatch_mode` du `Server` prend en charge les modes `SWOOLE_DISPATCH_CO_CONN_LB` et `SWOOLE_DISPATCH_CO_REQ_LB` (#4318) (@matyhtf)

- La méthode `ConnectionPool::get()` prend en charge le paramètre `timeout` (swoole/library#108) (@leocavalcante)

- Hook Curl prend en charge l'option `CURLOPT_PRIVATE` (swoole/library#112) (@sy-records)

- Optimisation de la déclaration de la méthode `PDOStatementProxy::setFetchMode()` (swoole/library#109) (@yespire)


### Réparation



- Réparation de l'exception levée par la création de trop de coroutines lorsqu'un contexte de thread est utilisé (#8ce5041) (@matyhtf)

- Réparation du problème de perte du fichier php_swoole.h lors de l'installation de Swoole (#4239) (@sy-records)

- Réparation du problème de non-compatibilité avec EVENT_HANDSHAKE (#4248) (@sy-records)

- Réparation du problème où la macro SW_LOCK_CHECK_RETURN pourrait appeler la fonction deux fois (#4302) (@zmyWL)

- Réparation du problème sur la puce M1 avec Atomic\Long (#e6fae2e) (@matyhtf)

- Réparation du problème de perte de la valeur de retour de `Coroutine\go()` (swoole/library@1ed49db) (@matyhtf)

- Réparation du problème du type de retour de `StringObject` (swoole/library#111) (swoole/library#113) (@leocavalcante) (@sy-records)


### noyau


- Interdiction des fonctions hookées qui ont été désactivées par PHP (#4283) (@twose)


### Tests



- Ajout de la construction sous Cygwin (#4222) (@sy-records)

- Ajout des tests de compilation pour Alpine 3.13 et 3.14 (#4309) (@limingxinleo)


## v4.6.7


### Renforcement


- Le processus Manager et le processus Task prennent en charge l'appel de la fonction `Process::signal()` (#4190) (@matyhtf)


### Réparation



- Réparation du problème de double inscription des signaux (#4170) (@matyhtf)

- Réparation des problèmes de compilation sur OpenBSD/NetBSD (#4188) (#4194) (@devnexen)

- Réparation du problème de perte d'événement onClose dans des cas particuliers lors de l'écoute d'événements écrits (#4204) (@matyhtf)

- Réparation du problème de l'utilisation de curl native avec HttpClient Symfony (#4204) (@matyhtf)

- Réparation du problème où la méthode `Http\Response::end()` retourne toujours true (swoole/swoole-src@66fcc35) (@matyhtf)

- Réparation de l'exception PDOException générée par PDOStatementProxy (swoole/library#104) (@twose)


### noyau



- Refactoring du tampon worker, ajout d'un identifiant de message au data de l'événement (#4163) (@matyhtf)

- Modification du niveau de journalisation pour Request Entity Too Large à l'échelle de warning (#4175) (@sy-records)

- Remplacement des fonctions inet_ntoa et inet_aton (#4199) (@remicollet)

- Modification de la valeur par défaut de output_buffer_size à UINT_MAX (swoole/swoole-src@46ab345) (@matyhtf)


## v4.6.6


### Renforcement



- Prise en charge de l'envoi du signal SIGTERM au processus Manager après la sortie du processus Master sous FreeBSD (#4150) (@devnexen)

- Prise en charge de la compilation statique de Swoole dans PHP (#4153) (@matyhtf)

- Prise en charge de l'utilisation d'un proxy HTTP pour SNI (#4158) (@matyhtf)


### Réparation



- Réparation de l'erreur d'établissement de connexion asynchrone par le client synchrone (#4152) (@matyhtf)

- Réparation du problème de fuite de mémoire causé par l'hook du multi curl native (swoole/swoole-src@91bf243) (@matyhtf)


## v4.6.5


### Nouvelles API


- Ajout de la méthode `count` dans `WaitGroup` (swoole/library#100) (@sy-records) (@deminy)


### Renforcement



- Prise en charge du multi curl native (#4093) (#4099) (#4101) (#4105) (#4113) (#4121) (#4147) (swoole/swoole-src@cd7f51c) (@matyhtf) (@sy-records) (@huanghantao)
- Permettre l'utilisation d'un tableau pour l'établissement des headers dans la réponse HTTP/2


### Réparation



- Réparation de la compilation sur NetBSD (#4080) (@devnexen)

- Réparation de la compilation sur OpenBSD (#4108) (@devnexen)

- Réparation de la compilation sur illumos/solaris, où il n'y a que des alias de membres (#4109) (@devnexen)
- Correction du détection de pouls de la connexion SSL lorsque la main courante n'est pas terminée (#4114) (@matyhtf)

- Correction de l'erreur générée par la présence de `host:port` dans le `host` lorsque l'Http\Client utilise un proxy (#4124) (@Yurunsoft)
- Correction de l'établissement des en-têtes et des cookies dans Swoole\Coroutine\Http::request (swoole/library#103) (@leocavalcante) (@deminy)

### noyau

- Support pour le contexte asm sur BSD (#4082) (@devnexen)
- Utilisation de arc4random_buf pour réaliser getrandom sous FreeBSD (#4096) (@devnexen)
- Optimisation du contexte arm64 darwin : suppression du workaround utilisant une étiquette (#4127) (@devnexen)

### Tests

- Ajout de scripts de build pour Alpine (#4104) (@limingxinleo)

## v4.6.4

### Nouvelles API

- Ajout de la fonction Coroutine\Http::request, Coroutine\Http::post, Coroutine\Http::get (swoole/library#97) (@matyhtf)

### Améliorations

- Support de la build ARM 64 (#4057) (@devnexen)
- Support de l'établissement du protocole HTTP ouvert dans les serveurs TCP Swoole (#4063) (@matyhtf)
- Support de l'établissement d'un certificat SSL uniquement pour les clients (91704ac) (@matyhtf)
- Support de l'option tcp_defer_accept sous FreeBSD (#4049) (@devnexen)

### Correction

- Correction du problème d'absence d'autorisation de proxy lors de l'utilisation de Coroutine\Http\Client (edc0552) (@matyhtf)
- Correction du problème d'allocation de mémoire de Swoole\Table (3e7770f) (@matyhtf)
- Correction du crash lors de la connexion parallèle avec le client HTTP/2 (630536d) (@matyhtf)
- Correction du problème de mise en œuvre de l'encryptage SSL TLS (842733b) (@matyhtf)
- Correction du débordement de mémoire de Coroutine\Barrier (swoole/library#94) (@Appla) (@FMiS)
- Correction de l'erreur d'offset causée par l'ordre de CURLOPT_PORT et CURLOPT_URL (swoole/library#96) (@sy-records)
- Correction de l'erreur lors de la retrieval d'une valeur de champ de type float avec `Table::get($key, $field)` (08ea20c) (@matyhtf)
- Correction du débordement de mémoire de Swoole\Table (d78ca8c) (@matyhtf)

## v4.4.24

### Correction

- Correction du crash lors de la connexion parallèle avec le client HTTP/2 (#4079)

## v4.6.3

### Nouvelles API

- Ajout de la fonction Coroutine\Http::request, Coroutine\Http::post, Coroutine\Http::get (swoole/library@82f63be) (@matyhtf)
- Ajout de la fonction Coroutine\defer (swoole/library@92fd0de) (@matyhtf)

### Améliorations

-Ajout de l'option compression_min_length pour les serveurs HTTP (#4033) (@matyhtf)
- Permettre l'établissement du header HTTP Content-Length au niveau de l'application (#4041) (@doubaokun)

### Correction

- Correction du crash du cœur (coredump) lorsque le programme atteint la limite de ouvertures de fichiers (#4098) (@matyhtf)
- Correction du problème de déactivation de JIT (#4029) (@twose)
- Correction du problème d'erreur d'argument dans la création de réponse (swoole/swoole-src@a630b5b) (@matyhtf)
- Correction du problème d'erreur de rapport de task_worker_id lors de la délivrance de tâches sur la plateforme ARM (#4040) (@doubaokun)
- Correction du crash du cœur (coredump) lors de l'activation du hook native curl PHP8 (#4042)(#4045) (@Yurunsoft) (@matyhtf)
- Correction du problème de débordement de mémoire lors de la phase de déconnexion après un fatal error (#4050) (@matyhtf)

### noyau

- Optimisation de ssl_connect/ssl_shutdown (#4030) (@matyhtf)
- Exitation du processus directement en cas d'erreur fatale (#4053) (@matyhtf)

## v4.6.2

### Nouvelles API

- Ajout de la méthode `Http\Request\getMethod()` (#3987) (@luolaifa000)
- Ajout de la méthode `Coroutine\Socket->recvLine()` (#4014) (@matyhtf)
- Ajout de la méthode `Coroutine\Socket->readWithBuffer()` (#4017) (@matyhtf)

### Améliorations

- Amélioration de la méthode `Response\create()` qui peut être utilisée indépendamment du serveur (#3998) (@matyhtf)
- Support pour le retour de type bool pour `Coroutine\Redis->hExists` après l'établissement du mode de compatibilité (swoole/swoole-src@b8cce7c) (@matyhtf)
- Support pour l'établissement de l'option PHP_NORMAL_READ lors de la lecture avec socket (swoole/swoole-src@b1a0dcc) (@matyhtf)

### Correction

- Correction du problème de crash du cœur (coredump) lors de l'utilisation de `Coroutine::defer` sous PHP8 (#3997) (@huanghantao)
- Correction du problème d'erreur dans l'établissement de errCode pour les sockets Coroutine lorsqu'un contexte de thread est utilisé (swoole/swoole-src@004d08a) (@matyhtf)
- Correction du problème de compilation ratée de Swoole sous le dernier macOS (#4007) (@matyhtf)
- Correction du problème de null pointer pour le contexte de flux PHP lorsque le paramètre md5_file est fourni avec une URL (#4016) (@ZhiyangLeeCN)

### noyau

- Utilisation de la pile de threads AIO pour hook stdio (résolution du problème précédent où stdio était considéré comme un socket, entraînant des problèmes de lecture et d'écriture conjoints dans plusieurs coroutines) (#4002) (@matyhtf)
- Refactoring du HttpContext (#3998) (@matyhtf)
- Refactoring de la méthode `Process::wait()` (#4019) (@matyhtf)

## v4.6.1

### Améliorations

-Ajout de l'option de compilation `--enable-thread-context` (#3970) (@matyhtf)
- Vérification de l'existence de la connexion lors de l'opération sur session_id (#3993) (@matyhtf)
- Amélioration de CURLOPT_PROXY (swoole/library#87) (@sy-records)

### Correction

- Correction du problème de la version minimale de PHP requise pour la installation via PECL (#3979) (@remicollet)
- Correction du manque des options `--enable-swoole-json` et `--enable-swoole-curl` lors de l'installation via PECL (#3980) (@sy-records)
- Correction du problème de sécurité multithread de OpenSSL (b516d69f) (@matyhtf)
- Correction du crash du cœur lors de l'activation de enableSSL (#3990) (@huanghantao)

### noyau

- Optimisation de l'écriture vectorisée ipc, évitant le crash du cœur lorsque les données de l'événement sont vides (9647678) (@matyhtf)

## v4.5.11

### Améliorations

- Optimisation de Swoole\Table (#3959) (@matyhtf)
- Amélioration de CURLOPT_PROXY (swoole/library#87) (@sy-records)

### Correction

- Correction du problème de non-clearage de toutes les colonnes lors de l'augmentation et de la diminution des tableaux (#3956) (@matyhtf) (@sy-records)
- Correction de l'erreur `clock_id_t` générée lors de la compilation (49fea171) (@matyhtf)
- Correction des bugs de lecture de fread (#3972) (@matyhtf)
- Correction du crash multithread de ssl (7ee2c1a0) (@matyhtf)
- Correction de l'erreur Invalid argument supplied for foreach causée par une格式错误 de l'URI (swoole/library#80) (@sy-records)
- Correction de l'erreur d'argument pour trigger_error (swoole/library#86) (@sy-records)

## v4.6.0

### Changements incompatibles vers le bas

- Suppression de la limite maximale pour l'identifiant de session, ne se répétant plus (#3879) (@matyhtf)
- Désactivation des fonctionnalités insécurisées lors de l'utilisation de coroutines, y compris pcntl_fork/pcntl_wait/pcntl_waitpid/pcntl_sigtimedwait (#3880) (@matyhtf)
- Activation par défaut du hook coroutine (#3903) (@matyhtf)

### Suppression

- Arrêt du support pour PHP7.1 (4a963df) (9de8d9e) (@matyhtf)

### Désuétude

- Marquer `Event::rshutdown()` comme déprécié, veuillez utiliser Coroutine\run à la place (#3881) (@matyhtf)
### Nouvelles API



- Prise en charge de setPriority/getPriority (#3876) (@matyhtf)

- Prise en charge du hook native-curl (#3863) (@matyhtf) (@huanghantao)

- Prise en charge de la transmission d'arguments sous forme d'objet pour les fonctions de rappel d'événements Server, par défaut sans arguments sous forme d'objet (#3888) (@matyhtf)

- Prise en charge de l'extension des sockets hook (#3898) (@matyhtf)

- Prise en charge des headers répétés (#3905) (@matyhtf)

- Prise en charge du SNI SSL (#3908) (@matyhtf)

- Prise en charge du hook stdio (#3924) (@matyhtf)

- Prise en charge de l'option capture_peer_cert pour stream_socket (#3930) (@matyhtf)

-Ajout de Http\Request::create/parse/isCompleted (#3938) (@matyhtf)
-Ajout de Http\Response::isWritable (db56827) (@matyhtf)


### Renforcements



- La précision de toutes les dates du Server est passée de int à double (#3882) (@matyhtf)

- Vérification de la situation EINTR dans la fonction swoole_client_select (#3909) (@shiguangqi)

-Ajout de la détection de deadlock de coroutines (#3911) (@matyhtf)

- Prise en charge de l'utilisation du mode SWOOLE_BASE pour fermer des connexions dans un autre processus (#3916) (@matyhtf)
- Optimisation de la performance de la communication entre le processus maître du Server et les processus worker, réduisant la copie de mémoire (#3910) (@huanghantao) (@matyhtf)


### Réparations



- Lorsqu'un canal Coroutine\Channel est fermé, tous les données qu'il contient sont retirées (#960431d) (@matyhtf)

- Réparation des erreurs de mémoire lors de l'utilisation de JIT (#3907) (@twose)

- Réparation de l'erreur de compilation dtls lors de l'appel de port->set() (#3947) (@Yurunsoft)

- Réparation de l'erreur de connection_list (#3948) (@sy-records)

- Réparation de la vérification SSL (#3954) (@matyhtf)

- Réparation du problème de non-réinitialisation de toutes les colonnes lors de l'augmentation et de la diminution de Swoole\Table (#3956) (@matyhtf) (@sy-records)

- Réparation de l'échec de la compilation avec LibreSSL 2.7.5 (#3962) (@matyhtf)
- Réparation des constantes CURLOPT_HEADEROPT et CURLOPT_PROXYHEADER indefinies (swoole/library#77) (@sy-records)


### noyau



- Par défaut, l'ignoration du signal SIGPIPE est activée (9647678) (@matyhtf)

- Prise en charge de l'exécution simultanée de coroutines PHP et C (c94bfd8) (@matyhtf)

-Ajout de la test get_elapsed (#3961) (@luolaifa000)
-Ajout de la test get_init_msec (#3964) (@luffluo)


## v4.5.10


### Réparations



- Réparation de l'écrasement du core lors de l'utilisation d'Event::cycle (93901dc) (@matyhtf)

- Compatibilité avec PHP8 (f0dc6d3) (@matyhtf)
- Réparation de l'erreur de connection_list (#3948) (@sy-records)


## v4.4.23


### Réparations



- Réparation de l'erreur de données lors de la diminution de Swoole\Table (bcd4f60d)(0d5e72e7) (@matyhtf)

- Réparation des informations d'erreur du client synchrone (#3784)

- Réparation du problème de dépassement de mémoire lors de l'analyse des données de formulaire (#3858)
- Réparation du bug du canal, l'impossibilité de retirer les données après la fermeture


## v4.5.9


### Renforcements


-Ajout de la constante SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED pour Coroutine\Http\Client (#3873) (@sy-records)


### Réparations


- Compatibilité avec PHP8 (#3868) (#3869) (#3872) (@twose) (@huanghantao) (@doubaokun)

- Réparation des constantes CURLOPT_HEADEROPT et CURLOPT_PROXYHEADER indefinies (swoole/library#77) (@sy-records)
- Réparation de CURLOPT_USERPWD (swoole/library@7952a7b) (@twose)


## v4.5.8


### Nouvelles API



- Ajout de la fonction swoole_error_log, optimisant la rotation des logs (swoole/swoole-src@67d2bff) (@matyhtf)
- Prise en charge de SSL pour readVector et writeVector (#3857) (@huanghantao)


### Renforcements


- Après la sortie du processus enfant, laissez System::wait sortir de manière bloquée (#3832) (@matyhtf)

- Prise en charge de paquets de 16K pour DTLS (#3849) (@matyhtf)

- La méthode Response::cookie prend en charge le paramètre priority (#3854) (@matyhtf)

- Prise en charge de davantage d'options CURL (swoole/library#71) (@sy-records)
- Traitement du problème de surcharge des noms dans les headers HTTP CURL (#3858) (@filakhtov) (@twose) (@sy-records)


### Réparations


- Réparation du traitement des erreurs EAGAIN pour readv_all et writev_all (#3830) (@huanghantao)

- Réparation des avertissements de compilation PHP8 (swoole/swoole-src@03f3fb0) (@matyhtf)

- Réparation de la sécurité binaire de Swoole\Table (#3842) (@twose)

- Réparation du problème de surwrite des fichiers lors de l'utilisation de System::writeFile sous MacOS (swoole/swoole-src@a71956d) (@matyhtf)

- Réparation de CURLOPT_WRITEFUNCTION pour CURL (swoole/library#74) (swoole/library#75) (@sy-records)

- Réparation du problème de dépassement de mémoire lors de l'analyse des données form-data HTTP (#3858) (@twose)
- Réparation du problème de l'accès aux méthodes privées des classes lors de l'utilisation de is_callable() dans PHP8 (#3859) (@twose)


### noyau



- Refactoring des fonctions d'allocation de mémoire, utilisant SwooleG.std_allocator (#3853) (@matyhtf)
- Refactoring des tuyaux (#3841) (@matyhtf)


## v4.5.7


### Nouvelles API


- Ajout de writeVector, writeVectorAll, readVector et readVectorAll pour les clients Socket Coroutine (#3764) (@huanghantao)


### Renforcements


-Ajout de task_worker_num et dispatch_count pour server->stats (#3771) (#3806) (@sy-records) (@matyhtf)

-Ajout de dépendances d'extension, y compris json, mysqlnd, sockets (#3789) (@remicollet)

- Limitation du minimum de uid pour server->bind à INT32_MIN (#3785) (@sy-records)

-Ajout d'une option de compilation pour swoole_substr_json_decode, supportant des décalages négatifs (#3809) (@matyhtf)
- Prise en charge de l'option CURLOPT_TCP_NODELAY pour CURL (swoole/library#65) (@sy-records) (@deminy)


### Réparations


- Réparation de l'erreur d'information de connexion du client synchrone (#3784) (@twose)

- Réparation du problème de fonction hook scandir (#3793) (@twose)
- Réparation de l'erreur dans la barrière barrier (swoole/library#68) (@sy-records)


### noyau


- Utilisation de boost.stacktrace pour optimiser l'impression de backtrace (#3788) (@matyhtf)


## v4.5.6


### Nouvelles API


- Ajout de [swoole_substr_unserialize](/functions?id=swoole_substr_unserialize) et [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode) (#3762) (@matyhtf)


### Renforcements


- Modification de la méthode onAccept de Coroutine\Http\Server en privée (dfcc83b) (@matyhtf)


### Réparations


- Réparation du problème de coverity (#3737) (#3740) (@matyhtf)

- Réparation de certains problèmes sous l'environnement Alpine (#3738) (@matyhtf)

- Réparation de swMutex_lockwait (0fc5665) (@matyhtf)
- Réparation de l'échec de l'installation de PHP-8.1 (#3757) (@twose)


### noyau


-Ajout de la détection de l'activité pour read/write/shutdown de Socket (#3735) (@matyhtf)
- Changement du type de session_id et task_id en int64 (#3756) (@matyhtf)
## v4.5.5

!> Cette version ajoute une fonction de détection des paramètres de configuration ([paramètres de configuration](/server/setting)), et si un choix non fourni par Swoole est établi, une alerte sera générée.

```shell
PHP Alert:  Option non prise en charge [foo] dans @swoole-src/library/core/Server/Helper.php 
```

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->set(['foo' => 'bar']);

$http->on('request', function ($request, $response) {
    $response->header("Content-Type", "text/html; charset=utf-8");
    $response->end("<h1>Bonjour Swoole. #".rand(1000, 9999)."</h1>");
});

$http->start();
```


### Nouvelles API



- Ajout de Process\Manager, modification de Process\ProcessManager en alias (swoole/library#eac1ac5) (@matyhtf)

- Prise en charge du serveur HTTP2 GOAWAY (#3710) (@doubaokun)
- Ajout de la fonction `Co\map()` (swoole/library#57) (@leocavalcante)


### Améliorations



- Prise en charge du client Unix socket HTTP2 (#3668) (@sy-records)

- Après la sortie du processus worker, le statut du processus worker est mis à SW_WORKER_EXIT (#3724) (@matyhtf)

- Ajout de send_queued_bytes et recv_queued_bytes dans les valeurs de retour de `Server::getClientInfo()` (#3721) (#3731) (@matyhtf) (@Yurunsoft)
- Prise en charge de l'option de configuration stats_file pour le serveur (#3725) (@matyhtf) (@Yurunsoft)


### Réparations



- Réparation des problèmes de compilation sous PHP8 (changement de zend_compile_string) (#3670) (@twose)

- Réparation des problèmes de compilation sous PHP8 (compatibilité avec ext/sockets) (#3684) (@twose)

- Réparation des problèmes de compilation sous PHP8 (changement de php_url_encode_hash_ex) (#3713) (@remicollet)

- Réparation du problème de conversion de type de `const char*` à `char*` (#3686) (@remicollet)

- Réparation du problème de fonctionnement du client HTTP2 sous proxy HTTP (#3677) (@matyhtf) (@twose)

- Réparation du problème de désordre des données lors de la reconnexion après une rupture de PDO (#368) (@sy-records)

- Réparation du problème d'analyse erronée du port lors de l'utilisation d'un serveur UDP avec IPv6
- Réparation du problème d'invalidité de la attente de verrouillage de Lock::lockwait


## v4.5.4


### Changements incompatibles avec les versions précédentes



- SWOOLE_HOOK_ALL comprend désormais SWOOLE_HOOK_CURL (#3606) (@matyhtf)
- Suppression de ssl_method, ajout de ssl_protocols (#3639) (@Yurunsoft)


### Nouvelles API


- Ajout des méthodes firstKey et lastKey pour les tableaux (swoole/library#51) (@sy-records)


### Améliorations


- Ajout des paramètres de configuration open_websocket_ping_frame et open_websocket_pong_frame pour les serveurs Websocket (#3600) (@Yurunsoft)


### Réparations


- Réparation du problème de positionnement fseek et ftell incorrect lorsque le fichier est supérieur à 2G (#3619) (@Yurunsoft)

- Réparation du problème de barrière de socket (#3627) (@matyhtf)

- Réparation du problème de négociation de main-de-main HTTP proxy (#3630) (@matyhtf)

- Réparation du problème d'analyse erronée des en-têtes HTTP lorsqu'une part de données chunk est envoyée par l'autre partie (#3633) (@matyhtf)

- Réparation du problème de défaillance de l'assert dans zend_hash_clean (#3634) (@twose)

- Réparation du problème de suppression impossible d'un fd cassé dans l'événement loop (#3650) (@matyhtf)

- Réparation du problème de coredump lorsque des paquets invalides sont reçus (#3653) (@matyhtf)
- Réparation du bug de array_key_last (swoole/library#46) (@sy-records)


### noyau



- Optimisation des codes (#3615) (#3617) (#3622) (#3635) (#3640) (#3641) (#3642) (#3645) (#3658) (@matyhtf)

- Réduction des opérations de mémoire inutiles lors de l'écriture de données dans une table Swoole (#3620) (@matyhtf)

- Refactoring de l'AIO (#3624) (@Yurunsoft)

- Prise en charge des hooks readlink/opendir/readdir/closedir (#3628) (@matyhtf)
- Optimisation de la création de swMutex, prise en charge de SW_MUTEX_ROBUST (#3646) (@matyhtf)


## v4.5.3


### Nouvelles API



- Ajout de `Swoole\Process\ProcessManager` (swoole/library#88f147b) (@huanghantao)

- Ajout de append pour ArrayObject et equals pour StringObject (swoole/library#f28556f) (@matyhtf)

- Ajout de [Coroutine::parallel](/coroutine/coroutine?id=parallel) (swoole/library#6aa89a9) (@matyhtf)
- Ajout de [Coroutine\Barrier](/coroutine/barrier) (swoole/library#2988b2a) (@matyhtf)


### Améliorations



- Prise en charge de usePipelineRead pour le streaming du client HTTP2 (#3354) (@twose)

- Lorsque le client HTTP Downloade un fichier, il ne crée pas de fichier avant d'accepter les données (#3381) (@twose)

- Le client HTTP prend en charge les configurations bind_address et bind_port (#3390) (@huanghantao)

- Le client HTTP prend en charge la configuration lowercase_header (#3399) (@matyhtf)

- Le serveur `Swoole\Server` prend en charge la configuration tcp_user_timeout (#3404) (@huanghantao)

- Le `Coroutine\Socket` augmente le barrier d'événement pour réduire le changement de coroutines (#3409) (@matyhtf)

- Une allocation de mémoire spécifique est ajoutée pour certains swString (#3418) (@matyhtf)

- Le cURL prend en charge `__toString` (swoole/library#38) (@twose)

- Prise en charge de l'établissement direct du nombre de attentes dans le constructeur de WaitGroup (swoole/library#2fb228b8) (@matyhtf)

-Ajout de `CURLOPT_REDIR_PROTOCOLS` (swoole/library#46) (@sy-records)

- Le serveur HTTP1.1 prend en charge les Trailers (#3485) (@huanghantao)

- Si le temps de sommeil d'une coroutine est inférieur à 1ms, elle va céder le contrôle à la coroutine actuelle (#3487) (@Yurunsoft)

- Le gestionnaire statique HTTP prend en charge les fichiers symbolic link (#3569) (@LeiZhang-Hunter)

- Après que le serveur a appelé la méthode close, il ferme immédiatement la connexion WebSocket (#3570) (@matyhtf)

- Prise en charge du hook stream_set_blocking (#3585) (@Yurunsoft)

- Le serveur HTTP2 asynchrone prend en charge le contrôle de flux (#3486) (@huanghantao) (@matyhtf)
- Libération du tampon de socket lors de l'exécution de la fonction de retour de package (#3551) (@huanghantao) (@matyhtf)


### Réparations



- Réparation du coredump du WebSocket, traitement de l'état d'erreur du protocole (#3359) (@twose)

- Réparation du problème d'erreur de null pointer dans la fonction swSignalfd_setup et dans la fonction wait_signal (#3360) (@twose)

- Réparation du problème d'erreur lors de l'appel de la méthode close de Swoole\Server lorsqu'une fonction de dispatch_func est définie (#3365) (@twose)

- Réparation du problème d'initialisation de format_buffer dans la fonction format de Swoole\Redis\Server (#3369) (@matyhtf) (@twose)

- Réparation du problème d'impossibilité d'obtenir l'adresse MAC sur MacOS (#3372) (@twose)

- Réparation des cas de test MySQL (#3374) (@qiqizjl)

- Réparation de plusieurs problèmes de compatibilité PHP8 (#3384) (#3458) (#3578) (#3598) (@twose)

- Réparation du problème de perte de php_error_docref, timeout_event et de valeur de retour dans l'écriture du socket hook (#3383) (@twose)

- Réparation du problème de fermeture impossible du serveur lors de l'appel de la fonction WorkerStart dans le serveur asynchrone (#3382) (@huanghantao)

- Réparation du problème de possible coredump lorsque le thread de heartbeat manipule le socket de conn->socket (#3396) (@huanghantao)

- Réparation du problème logique de send_yield (#3397) (@twose) (@matyhtf)
- Résolvez le problème de compilation sur Cygwin64 (#3400) (@twose)

- Résolvez le problème de propriété finish invalide du WebSocket (#3410) (@matyhtf)

- Résolvez le problème d'erreur de transaction MySQL manquant (#3429) (@twose)

- Résolvez le problème de comportement incohérent de `stream_select` après hook par rapport aux valeurs de retour avant hook (#3440) (@Yurunsoft)

- Résolvez le problème de perte de signal SIGCHLD lors de la création de processus fils avec `Coroutine\System` (#3446) (@huanghantao)

- Résolvez le problème de manque de support SSL pour `sendwait` (#3459) (@huanghantao)

- Résolvez plusieurs problèmes de `ArrayObject` et `StringObject` (swoole/library#44) (@matyhtf)

- Résolvez l'erreur d'information d'exception de mysqli (swoole/library#45) (@sy-records)

- Résolvez le problème de `Swoole\Client` ne pas pouvoir obtenir le bon `errCode` après avoir établi `open_eof_check` (#3478) (@huanghantao)

- Résolvez plusieurs problèmes de `atomic->wait()`/`wakeup()` sur MacOS (#3476) (@Yurunsoft)

- Résolvez le problème de retour d'état réussi lors de l'acceptation refusée par `Client::connect` (#3484) (@matyhtf)

- Résolvez le problème de déclaration manquante de `nullptr_t` dans l'environnement Alpine (#3488) (@limingxinleo)

- Résolvez le problème de double-free lors du téléchargement de fichiers par l'HTTP Client (#3489) (@Yurunsoft)

- Résolvez le problème de fuite de mémoire dû à la non-libération de `Server\Port` après la destruction du `Server` (#3507) (@twose)

- Résolvez le problème d'analyse du protocole MQTT (318e33a) (84d8214) (80327b3) (efe6c63) (@GXhua) (@sy-records)

- Résolvez le problème de coredump causé par la méthode `Coroutine\Http\Client->getHeaderOut` (#3534) (@matyhtf)

- Résolvez le problème de perte d'information d'erreur après un échec de vérification SSL (#3535) (@twose)

- Résolvez le problème de lien erroné pour `Swoole benchmark` dans la README (#3536) (@sy-records) (@santalex)

- Résolvez le problème d'injection de header dû à l'utilisation de `CRLF` dans les headers/cookies HTTP (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)

- Résolvez le problème de variable erronée mentionné dans l'issue #3463 (#3547) (chromium1337) (@huanghantao)

- Résolvez le problème d'orthographe erronée mentionné dans la pr #3463 (#3547) (@deminy)

- Résolvez le problème de frame->fd vide pour le serveur WebSocket coroutine (#3549) (@huanghantao)

- Résolvez le problème de fuite de connexion causé par un mauvais jugement de état de connexion dans le thread de heartbeat (#3534) (@matyhtf)

- Résolvez le problème de blocage des signaux dans `Process\Pool` (#3582) (@huanghantao) (@matyhtf)

- Résolvez le problème d'utilisation de send headers dans `SAPI` (#3571) (@twose) (@sshymko)

- Résolvez le problème de non-设置 de errCode et errMsg lors du échec de l'exécution de CURL (swoole/library#1b6c65e) (@sy-records)
- Résolvez le problème de coredump de l'acceptation de `swoole_socket_coro` après avoir appelé la méthode setProtocol (#3591) (@matyhtf)


### noyau



- Utiliser le style C++ (#3349) (#3351) (#3454) (#3479) (#3490) (@huanghantao) (@matyhtf)

- Ajouter `Swoole known strings` pour améliorer les performances de la lecture des propriétés PHP (#3363) (@huanghantao)

- Optimisation de nombreux codages (#3350) (#3356) (#3357) (#3423) (#3426) (#3461) (#3463) (#3472) (#3557) (#3583) (@huanghantao) (@twose) (@matyhtf)

- Optimisation de nombreux codes de test (#3416) (#3481) (#3558) (@matyhtf)

- Simplification du type int pour `Swoole\Table` (#3407) (@matyhtf)

- Ajouter `sw_memset_zero` et remplacer la fonction bzero (#3419) (@CismonX)

- Optimisation du module de journalisation (#3432) (@matyhtf)

- Reconstruction de nombreux éléments de libswoole (#3448) (#3473) (#3475) (#3492) (#3494) (#3497) (#3498) (#3526) (@matyhtf)

- Reconstruction de nombreuses inclusions de fichiers de tête (#3457) (@matyhtf) (@huanghantao)

- Ajouter `Channel::count()` et `Channel::get_bytes()` (f001581) (@matyhtf)

- Ajouter un `scope guard` (#3504) (@huanghantao)

- Ajouter des tests de couverture pour libswoole (#3431) (@huanghantao)

- Ajouter des tests pour l'environnement MacOS de lib-swoole/ext-swoole (#3521) (@huanghantao)
- Ajouter des tests pour l'environnement Alpine de lib-swoole/ext-swoole (#3537) (@limingxinleo)


## v4.5.2

[v4.5.2](https://github.com/swoole/swoole-src/releases/tag/v4.5.2), c'est une version de correction de bugs, sans aucune modification incompatibilité descendante


### Renforcements



- Supporter `Server->set(['log_rotation' => SWOOLE_LOG_ROTATION_DAILY])` pour générer des journaux par date (#3311) (@matyhtf)

- Supporter `swoole_async_set(['wait_signal' => true])`, si un auditeur de signal est présent, le réacteur ne quittera pas (#3314) (@matyhtf)

- Supporter `Server->sendfile` pour envoyer des fichiers vides (#3318) (@twose)

- Optimiser les informations d'alerte sur la charge du worker (#3328) (@huanghantao)

- Optimiser la configuration de l'en-tête Host sous l'agent HTTPS proxy (utiliser ssl_host_name pour la configuration) (#3343) (@twose)

- SSL utilise par défaut le mode auto ecdh (#3316) (@matyhtf)
- Le client SSL utilise une sortie silencieuse lors de la déconnexion (#3342) (@huanghantao)


### Résolutions



- Résolvez le problème de `Server->taskWait` sur la plateforme OSX (#3330) (@matyhtf)

- Résolvez le bug d'analyse du protocole MQTT (8dbf506b) (@guoxinhua) (2ae8eb32) (@twose)

- Résolvez le problème de dépassement de type int pour Content-Length (#3346) (@twose)

- Résolvez le problème de vérification manquante de la longueur du paquet PRI (#3348) (@twose)

- Résolvez le problème de l'incapacité à vider CURLOPT_POSTFIELDS (#3348) (@twose)
- Résolvez le problème de non-libération du dernier objet de connexion avant qu'il ne reçoive la prochaine connexion (swoole/library@1ef79339) (@twose)


### Noyau



- Caractéristique de copie zéro de la socket écriture (#3327) (@twose)
- Utiliser swoole_get_last_error/swoole_set_last_error pour remplacer l'écriture/lecture de variables globales (e25f262a) (@matyhtf) (#3315) (@huanghantao)
- Prise en charge de la configuration de `log_date_format` pour changer le format de la date dans les journaux, `log_date_with_microseconds` affiche des timestamps au microsecondes dans les journaux (baf895bc) (@matyhtf)

- Prise en charge de CURLOPT_CAINFO et CURLOPT_CAPATH (swoole/library#32) (@sy-records)
- Prise en charge de CURLOPT_FORBID_REUSE (swoole/library#33) (@sy-records)


### Réparations



- Réparation de l'échec de la compilation en 32 bits (#3276) (#3277) (@remicollet) (@twose)

- Réparation du problème où le client coroutine se reconnecte à plusieurs reprises sans générer d'erreur EISCONN (#3280) (@codinghuang)

- Réparation d'un bug potentiel dans le module Table (d7b87b65) (@matyhtf)

- Réparation d'un NULL pointer due à un comportement non défini dans le Server (#3304) (#3305) (@twose)

- Réparation du problème d'erreur NULL pointer généré après avoir activé la configuration de la détection de cœur (#3307) (@twose)

- Réparation de l'absence d'effet de la configuration MySQLi (swoole/library#35)
- Réparation du problème de parsing des headers irréguliers dans la réponse (manque d'espace) (swoole/library#27) (@Yurunsoft)


### Abandonnements


- Marquer les méthodes de Coroutine\System telles que (fread/fgets/fwrite) comme abandonnées (veuillez utiliser la caractéristique hook pour les remplacer, utilisez directement les fonctions de fichiers PHP fournies) (c7c9bb40) (@twose)


### noyau



- Allocation de mémoire pour les objets personnalisés en utilisant zend_object_alloc (cf1afb25) (@twose)

- Plusieurs optimisations, ajout de plus configuration options au module de journal (#3296) (@matyhtf)
- Un grand nombre d'optimizations de code et d'ajout de tests unitaires (swoole/library) (@deminy)


## v4.5.0

[v4.5.0](https://github.com/swoole/swoole-src/releases/tag/v4.5.0), c'est une grande mise à jour de la version, qui ne fait que supprimer certains modules qui avaient été marqués comme abandonnés dans la version 4.4.x


### Nouvelles API



- Prise en charge du DTLS, vous pouvez maintenant utiliser cette fonction pour construire des applications WebRTC (#3188) (@matyhtf)

- Un client FastCGI intégré, qui peut proxy des demandes à FPM ou appeler une application FPM en une ligne de code (swoole/library#17) (@twose)

- `Co::wait`, `Co::waitPid` (pour récupérer les processus enfants) `Co::waitSignal` (pour attendre un signal) (#3158) (@twose)

- `Co::waitEvent` (pour attendre un événement spécifique sur le socket) (#3197) (@twose)

- `Co::set(['exit_condition' => $callable])` (pour personnaliser la condition de sortie du programme) (#2918) (#3012) (@twose)

- `Co::getElapsed` (pour obtenir le temps de fonctionnement du coroutine afin d'analyser les statistiques ou de trouver des coroutines zombie) (#3162) (@doubaokun)

- `Socket::checkLiveness` (pour déterminer si une connexion est active par des appels système), `Socket::peek` (pour regarder dans le tampon de lecture) (#3057) (@twose)

- `Socket->setProtocol(['open_fastcgi_protocol' => $bool])` (prise en charge intégrée du déballage FastCGI) (#3103) (@twose)

- `Server::get(Master|Manager|Worker)Pid`, `Server::getWorkerId` (pour obtenir l'instance asynchrone du Server et ses informations) (#2793) (#3019) (@matyhtf)

- `Server::getWorkerStatus` (pour obtenir l'état du processus worker, retourne les constantes SWOOLE_WORKER_BUSY, SWOOLE_WORKER_IDLE pour indiquer l'état occupé ou inactif) (#3225) (@matyhtf)

- `Server->on('beforeReload', $callable)` et `Server->on('afterReload', $callable)` (événements de redémarrage du service, qui se produisent dans le processus manager) (#3130) (@hantaohuang)

- Le gestionnaire de fichiers statiques de `Http\Server` prend maintenant en charge les configurations `http_index_files` et `http_autoindex` (#3171) (@hantaohuang)

- La méthode `Http2\Client->read(float $timeout = -1)` prend maintenant en charge la lecture de réponses en flux (#3011) (#3117) (@twose)

- `Http\Request->getContent` (alias de la méthode rawContent) (#3128) (@hantaohuang)
- `swoole_mime_type_(add|set|delete|get|exists)()` (API mime, permettant d'ajouter, de configurer, d'effacer, d'obtenir ou d'exister des types MIME intégrés) (#3134) (@twose)


### Améliorations



- Optimisation de la copie de mémoire entre les processus master et worker (amélioration de quatre fois dans les cas extrêmes) (#3075) (#3087) (@hantaohuang)

- Optimisation de la logique de déploiement du WebSocket (#3076) (@matyhtf)

- Optimisation de la copie de mémoire lors de la construction d'un cadre WebSocket (#3097) (@matyhtf)

- Optimisation du module de vérification SSL (#3226) (@matyhtf)

- Séparation de l'acceptation SSL et du handshake SSL, résolvant le problème où les clients SSL lent peuvent faire semblant que le serveur coroutine est mort (#3214) (@twose)

- Prise en charge de l'architecture MIPS (#3196) (@ekongyun)

- Le client UDP peut maintenant analyser automatiquement les noms de domaine entrants (#3236) (#3239) (@huanghantao)

- Le serveur HTTP coroutine a ajouté la prise en charge de certaines options couramment utilisées (#3257) (@twose)

- Prise en charge de la configuration de cookie lors du handshake WebSocket (#3270) (#3272) (@twose)

- Prise en charge de CURLOPT_FAILONERROR (swoole/library#20) (@sy-records)

- Prise en charge de CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY (swoole/library#22) (@sy-records)
- Prise en charge de CURLOPT_HTTPGET (swoole/library@d730bd08) (@shiguangqi)


### Retrait



- Retrait de la méthode Runtime::enableStrictMode (b45838e3) (@twose)
- Retrait de la classe Buffer (559a49a8) (@twose)


### noyau lié



- Nouvelle API C++: la fonction coroutine::async prend en charge une lambda pour lancer une tâche asynchrone (#3127) (@matyhtf)

- Refactoring du fd entier dans l'API event-API de base en objet swSocket (#3030) (@matyhtf)

- Tous les fichiers C clés ont été convertis en fichiers C++ (#3030) (71f987f3) (@matyhtf)

- Une série d'optimizations de code (#3063) (#3067) (#3115) (#3135) (#3138) (#3139) (#3151) (#3168) (@hantaohuang)

- Optimisation de la normalisation des headers (#3051) (@matyhtf)

- Refactoring de l'option `enable_reuse_port` pour la rendre plus standardisée (#3192) (@matyhtf)

- Refactoring des API Socket pour les rendre plus standardisées (#3193) (@matyhtf)

- Réduction d'un appel système inutile par la prédiction de tampon (#3b5aa85d) (@matyhtf)

- Retrait du minuteur de rafraîchissement de base swServerGS::now, en utilisant directement la fonction système pour obtenir l'heure (#3152) (@hantaohuang)

- Optimisation du Configurateur de protocole (#3108) (@twose)

- Écriture de la initialization des structures C plus compatible (#3069) (@twose)

- Unification des champs bit en type uchar (#3071) (@twose)
- Prise en charge des tests parallèles, plus rapide (#3215) (@twose)


### Réparations



- Réparation du problème où l'activation de enable_delay_receive empêchait l'événement onConnect de se déclencher (#3221) (#3224) (@matyhtf)
- Toutes les autres corrections de bugs ont été fusionnées dans la branche 4.4.x et reflétées dans le journal des mises à jour, elles ne sont donc pas répétées ici
- Résolvez le problème de confusion des données lors de la reconnexion après une rupture de PDO (swoole/library#54) (@sy-records)

- Résolvez l'attente de verrouillage de swMutex (0fc5665) (@matyhtf)

- Résolvez l'erreur d'analyse du port lors de l'utilisation du serveur UDP avec IPv6
- Résolvez les problèmes des descriptors FDS avec systemd

## v4.4.20

[v4.4.20](https://github.com/swoole/swoole-src/releases/tag/v4.4.20), c'est une version de correction de bugs, sans aucune modification incompatibilité descendante

### Résolutions

- Résolvez le problème d'erreur lorsqu'on appelle `Swoole\Server::close` après avoir établi une fonction de dispatch (#3365) (@twose)

- Résolvez le problème d'initialisation de format_buffer dans la fonction `Swoole\Redis\Server::format` (#3369) (@matyhtf) (@twose)

- Résolvez le problème de non-obtention de l'adresse MAC sur MacOS (#3372) (@twose)

- Résolvez les cas de test MySQL (#3374) (@qiqizjl)

- Résolvez le problème de fermeture du serveur par un Server asynchrone dans la fonction de rappel WorkerStart (#3382) (@huanghantao)

- Résolvez les états d'erreur manquants des transactions MySQL (#3429) (@twose)

- Résolvez le problème de double-free lors du téléchargement de fichiers avec l'HTTP Client (#3489) (@Yurunsoft)

- Résolvez le problème de coredump causé par la méthode `Coroutine\Http\Client->getHeaderOut` (#3534) (@matyhtf)

- Résolvez le problème d'injection de header causé par l'utilisation de `CRLF` dans les headers/cookies HTTP (#3539) (#3541) (#3545) (@chromium1337) (@huanghantao)

- Résolvez le problème de frame->fd vide pour les serveurs WebSocket coroutinés (#3549) (@huanghantao)

- Résolvez le problème de `read error on connection` généré par l'hook phpredis (#3579) (@twose)

- Résolvez les problèmes de parsing du protocole MQTT (#3573) (#3517) (9ad2b455) (@GXhua) (@sy-records)

## v4.4.19

[v4.4.19](https://github.com/swoole/swoole-src/releases/tag/v4.4.19), c'est une version de correction de bugs, sans aucune modification incompatibilité descendante

!> Remarque : Les versions 4.4.x ne sont plus maintenues comme versions principales, et ne sont corrigées que si nécessaire

### Résolutions

- Intégrez toutes les corrections de bugs depuis la version 4.5.2

## v4.4.18

[v4.4.18](https://github.com/swoole/swoole-src/releases/tag/v4.4.18), c'est une version de correction de bugs, sans aucune modification incompatibilité descendante

### Améliorations

- Le client UDP peut maintenant analyser automatiquement les noms de domaine entrants (#3236) (#3239) (@huanghantao)

- Dans le mode CLI, stdout et stderr ne sont plus fermés (affichant les erreurs générées après le shutdown) (#3249) (@twose)

- Le serveur HTTP coroutiné prend en charge certaines options couramment utilisées (#3257) (@twose)

- Prise en charge de la configuration de cookie lors du handshake WebSocket (#3270) (#3272) (@twose)

- Prise en charge de CURLOPT_FAILONERROR (swoole/library#20) (@sy-records)

- Prise en charge de CURLOPT_SSLCERTTYPE, CURLOPT_SSLCERT, CURLOPT_SSLKEYTYPE, CURLOPT_SSLKEY (swoole/library#22) (@sy-records)

- Prise en charge de CURLOPT_HTTPGET (swoole/library@d730bd08) (@shiguangqi)

- Prise en charge autant que possible de toutes les versions des extensions PHP-Redis (les constructeurs varient selon les versions) (swoole/library#24) (@twose)
- Interdiction de cloner les objets de connexion (swoole/library#23) (@deminy)

### Résolutions

- Résolvez le problème d'échec du handshake SSL (dc5ac29a) (@twose)

- Résolvez l'erreur de mémoire générée lors de la création d'informations d'erreur (#3229) (@twose)

- Résolvez les informations d'authentification proxy vides (#3243) (@twose)

- Résolvez le problème de fuite de mémoire du Channel (pas vraiment une fuite de mémoire) (#3260) (@twose)

- Résolvez la fuite de mémoire à la fois éphémère due à la référence circulaire dans le serveur HTTP Co (#3271) (@twose)

- Résolvez l'erreur d'écriture dans `ConnectionPool->fill` (swoole/library#18) (@NHZEX)

- Résolvez le problème où le client curl ne met pas à jour la connexion lors d'un redirection (#321) (@doubaokun)

- Résolvez le problème de pointer vers le vide lors de la génération d'exceptions ioException (swoole/library@4d15a4c3) (@twose)

- Résolvez le problème de deadlock dû à la non-rétroaction de la nouvelle connexion lors de la mise en pool ConnectionPool@put avec null (swoole/library#25) (@Sinute)

- Résolvez l'erreur write_property causée par la mise en œuvre de l'agent mysqli (swoole/library#26) (@twose)

## v4.4.17

[v4.4.17](https://github.com/swoole/swoole-src/releases/tag/v4.4.17), c'est une version de correction de bugs, sans aucune modification incompatibilité descendante

### Améliorations

- Amélioration de la performance du serveur SSL (#3077) (85a9a595) (@matyhtf)

- Suppression de la limitation de taille des headers HTTP (#3187) limitation (@twose)

- Prise en charge de MIPS (#3196) (@ekongyun)

- Prise en charge de CURLOPT_HTTPAUTH (swoole/library@570318be) (@twose)

### Résolutions

- Résolvez le comportement de la fonction package_length_func et le possible problème de fuite de mémoire à la fois (#3111) (@twose)

- Résolvez le comportement d'erreur des codes d'état HTTP 304 (#3118) (#3120) (@twose)

- Résolvez l'erreur de mémoire causée par l'expansion incorrecte des macros de journalisation Trace (#3142) (@twose)

- Résolvez les signatures des fonctions OpenSSL (#3154) (#3155) (@twose)

- Résolvez les informations d'erreur SSL (#3172) (@matyhtf) (@twose)

- Résolvez la compatibilité avec PHP-7.4 (@twose) (@matyhtf)

- Résolvez le problème de parsing de la longueur des chunks HTTP (#3172) (@twose)

- Résolvez le comportement du parser des demandes multipart dans le mode chunk (#3172) (@twose)

- Résolvez l'échec de l'assert ZEND_ASSUME dans le mode Debug PHP (#3172) (@twose)

- Résolvez l'adresse erronée des erreurs de socket (#3172) (@twose)

- Résolvez les problèmes de getname socket (#3172) (#3179) (@matyhtf)

- Résolvez le traitement des fichiers vides par le gestionnaire de fichiers statiques (#3172) (@twose)

- Résolvez les problèmes de téléchargement de fichiers par le serveur HTTP coroutiné (#3172) (@twose)

- Résolvez l'erreur de mémoire potentielle pendant le shutdown (#3172) (@matyhtf)

- Résolvez le heartbeat du serveur (#3172) (@matyhtf)

- Résolvez le problème où le scheduler CPU ne peut pas programmer un loop mort (#3172) (@twose)

- Résolvez l'opération d'écriture invalide sur un tableau immuable (#3172) (@twose)

- Résolvez le problème de wait multiple sur WaitGroup (swoole/library@537a82e1) (@twose)

- Résolvez le traitement des headers vides (cohérent avec cURL) (swoole/library@7c92ed5a) (@twose)

- Résolvez le problème de dé抛出异常 lorsque la méthode non IO retourne false (swoole/library@f6997394) (@twose)

- Résolvez le problème de l'ajout répété du numéro de port de proxy dans les headers sous l'hook cURL (swoole/library@5e94e5da) (@twose)

## v4.4.16

[v4.4.16](https://github.com/swoole/swoole-src/releases/tag/v4.4.16), c'est une version de correction de bugs, sans aucune modification incompatibilité descendante

### Améliorations

- Amélioration des performances du serveur SSL (#3077) (85a9a595) (@matyhtf)

- Suppression de la limitation de taille des headers HTTP (#3187) (@twose)

- Prise en charge de MIPS (#3196) (@ekongyun)

- Prise en charge de CURLOPT_HTTPAUTH (swoole/library@570318be) (@twose)
- Vous pouvez maintenant obtenir les informations de support pour la version Swoole : [Informations de support pour la version Swoole](https://github.com/swoole/swoole-src/blob/master/SUPPORTED.md)

- Des messages d'erreur plus amicaux (0412f442) (09a48835) (@twose)

- Previent l'enlisement dans des boucles de system call sur certains systèmes spéciaux (069a0092) (@matyhtf)
- Ajout de nouvelles options de pilote dans PDOConfig (swoole/library#8) (@jcheron)

### Réparations

- Réparation d'une erreur de mémoire dans http2_session.default_ctx (bddbb9b1) (@twose)

- Réparation d'un contexte HTTP non initialisé (ce77c641) (@twose)

- Réparation d'une erreur d'écriture dans le module Table (qui pourrait entraîner une erreur de mémoire) (db4eec17) (@twose)

- Réparation d'un problème potentiel avec le reload de tâches dans Server (e4378278) (@GXhua)

- Réparation d'une erreur de pointer vide due au manque de demande originale dans le serveur HTTP coroutin (HTTP2) (#3079) (#3085) (@hantaohuang)

- Réparation du gestionnaire static (ne devrait pas retourner de réponse 404 lorsque le fichier est vide) (#3084) (@Yurunsoft)

- Réparation du problème avec la configuration de http_compression_level qui ne fonctionne pas correctement (16f9274e) (@twose)

- Réparation d'une erreur de pointer vide dans le serveur HTTP2 Coroutine dû au manque de enregistrement de handle (ed680989) (@twose)

- Réparation du problème avec la configuration de socket_dontwait qui ne fonctionne pas (27589376) (@matyhtf)

- Réparation du problème où zend::eval pourrait être exécuté plusieurs fois (#3099) (@GXhua)

- Réparation d'une erreur de pointer vide dans le serveur HTTP2 dû à la réponse après la fermeture de la connexion (#3110) (@twose)

- Réparation du problème avec l'adaptation inappropriée de PDOStatementProxy::setFetchMode (swoole/library#13) (@jcheron)
