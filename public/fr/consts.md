# Constantes

!> Ce document ne contient pas toutes les constantes, veuillez consulter ou installer pour voir toutes les constantes : [ide-helper](https://github.com/swoole/ide-helper/blob/master/output/swoole/constants.php)


## Swoole


Constante | Effet
---|---
SWOOLE_VERSION | Numéro de version actuel de Swoole, type chaîne, par exemple 1.6.0


## Paramètres de la méthode de construction


Constante | Effet
---|---
[SWOOLE_BASE](/learn?id=swoole_base) | Utiliser le mode Base, le code des affaires est exécuté directement dans le processus Reactor
[SWOOLE_PROCESS](/learn?id=swoole_process) | Utiliser le mode Process, le code des affaires est exécuté dans le processus Worker


## Types de socket


Constante | Effet
---|---
SWOOLE_SOCK_TCP | Créer un socket TCP
SWOOLE_SOCK_TCP6 | Créer un socket TCP IPv6
SWOOLE_SOCK_UDP | Créer un socket UDP
SWOOLE_SOCK_UDP6 | Créer un socket UDP IPv6
SWOOLE_SOCK_UNIX_DGRAM | Créer un socket Unix datagramme
SWOOLE_SOCK_UNIX_STREAM | Créer un socket Unix stream
SWOOLE_SOCK_SYNC | Client synchrone


## Méthodes de chiffrement SSL


Constante | Effet
---|---
SWOOLE_SSLv3_METHOD | -
SWOOLE_SSLv3_SERVER_METHOD | -
SWOOLE_SSLv3_CLIENT_METHOD | -
SWOOLE_SSLv23_METHOD (méthode de chiffrement par défaut) | -
SWOOLE_SSLv23_SERVER_METHOD | -
SWOOLE_SSLv23_CLIENT_METHOD | -
SWOOLE_TLSv1_METHOD | -
SWOOLE_TLSv1_SERVER_METHOD | -
SWOOLE_TLSv1_CLIENT_METHOD | -
SWOOLE_TLSv1_1_METHOD | -
SWOOLE_TLSv1_1_SERVER_METHOD | -
SWOOLE_TLSv1_1_CLIENT_METHOD | -
SWOOLE_TLSv1_2_METHOD | -
SWOOLE_TLSv1_2_SERVER_METHOD | -
SWOOLE_TLSv1_2_CLIENT_METHOD | -
SWOOLE_DTLSv1_METHOD | -
SWOOLE_DTLSv1_SERVER_METHOD | -
SWOOLE_DTLSv1_CLIENT_METHOD | -
SWOOLE_DTLS_SERVER_METHOD | -
SWOOLE_DTLS_CLIENT_METHOD | -

!> `SWOOLE_DTLSv1_METHOD`, `SWOOLE_DTLSv1_SERVER_METHOD`, `SWOOLE_DTLSv1_CLIENT_METHOD` ont été supprimés dans la version Swoole >= `v4.5.0`.


## Protocoles SSL


Constante | Effet
---|---
SWOOLE_SSL_TLSv1 | -
SWOOLE_SSL_TLSv1_1 | -
SWOOLE_SSL_TLSv1_2 | -
SWOOLE_SSL_TLSv1_3 | -
SWOOLE_SSL_SSLv2 | -
SWOOLE_SSL_SSLv3 | -

!> Disponible à partir de la version Swoole >= `v4.5.4`


## Niveaux de journalisation


Constante | Effet
---|---
SWOOLE_LOG_DEBUG | Journalisation de débogage, utilisée uniquement pour le développement et le débogage du noyau
SWOOLE_LOG_TRACE | Journalisation de suivi, utilisée pour suivre les problèmes du système, la journalisation de suivi est soigneusement configurée et porte des informations clés
SWOOLE_LOG_INFO | Informations ordinaires, utilisées uniquement pour l'affichage d'informations
SWOOLE_LOG_NOTICE | Informations de notification, le système peut avoir certains comportements, tels que le redémarrage, l'arrêt
SWOOLE_LOG_WARNING | Informations d'alerte, le système peut avoir certains problèmes
SWOOLE_LOG_ERROR | Informations d'erreur, le système a rencontré des erreurs clés et nécessite une résolution immédiate
SWOOLE_LOG_NONE | Equivaut à l'禁用 des informations de journalisation, les informations de journalisation ne sont pas émises

!> Les niveaux de journalisation `SWOOLE_LOG_DEBUG` et `SWOOLE_LOG_TRACE` ne peuvent être utilisés qu'après avoir compilé l'extension Swoole avec les options [--enable-debug-log](/environment?id=debug) ou [--enable-trace-log](/environment?id=debug). Même si `log_level` est set à `SWOOLE_LOG_TRACE` dans la version normale, ces types de journalisation ne peuvent pas être imprimés.

## Étiquettes de suivi

Les services en cours d'exécution en ligne traitent constamment un grand nombre de demandes et génèrent un nombre énorme de journaux en bas niveau. Vous pouvez utiliser `trace_flags` pour configurer les étiquettes des journaux de suivi et n'imprimer que certaines journaux de suivi. `trace_flags` prend en charge l'utilisation de l'opérateur `|` ou pour configurer plusieurs éléments de suivi.

```php
$serv->set([
	'log_level' => SWOOLE_LOG_TRACE,
	'trace_flags' => SWOOLE_TRACE_SERVER | SWOOLE_TRACE_HTTP2,
]);
```

Le niveau de suivi en bas niveau prend en charge les éléments suivants, vous pouvez utiliser `SWOOLE_TRACE_ALL` pour indiquer que vous souhaitez suivre tous les éléments :

* `SWOOLE_TRACE_SERVER`
* `SWOOLE_TRACE_CLIENT`
* `SWOOLE_TRACE_BUFFER`
* `SWOOLE_TRACE_CONN`
* `SWOOLE_TRACE_EVENT`
* `SWOOLE_TRACE_WORKER`
* `SWOOLE_TRACE_REACTOR`
* `SWOOLE_TRACE_PHP`
* `SWOOLE_TRACE_HTTP2`
* `SWOOLE_TRACE_EOF_PROTOCOL`
* `SWOOLE_TRACE_LENGTH_PROTOCOL`
* `SWOOLE_TRACE_CLOSE`
* `SWOOLE_TRACE_HTTP_CLIENT`
* `SWOOLE_TRACE_COROUTINE`
* `SWOOLE_TRACE_REDIS_CLIENT`
* `SWOOLE_TRACE_MYSQL_CLIENT`
* `SWOOLE_TRACE_AIO`
* `SWOOLE_TRACE_ALL`
