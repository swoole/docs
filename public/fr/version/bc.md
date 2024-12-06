# Désaccords vers le bas


## v5.0.0
* Modification du mode de fonctionnement par défaut du `Server` en `SWOOLE_BASE`
* La version minimale de `PHP` est portée à `8.0`
* Tous les méthodes et fonctions de classe ont été ajoutés avec des contraintes de type, passant à un mode de type fort
* Les noms de classe au style de l'underscore `PSR-0` ont été supprimés, ne gardant que les noms de classe au style de l'espace de nommage, par exemple `swoole_server` doit être modifié en `Swoole\Server`
* `Swoole\Coroutine\Redis` et `Swoole\Coroutine\MySQL` sont marqués comme dépréciés, veuillez utiliser la `Runtime Hook` + le client native `Redis`/`MySQL`



## v4.8.0


- Dans le mode `BASE`, le callback `onStart` sera toujours appelé lors du démarrage du premier processus de travail (`workerId` est `0`), avant l'exécution de `onWorkerStart`. Dans la fonction `onStart`, vous pouvez toujours utiliser l'API coroutine, et lorsque le `Worker-0` rencontre une erreur fatale et se redémarre, le callback `onStart` sera à nouveau appelé
Dans les versions précédentes, `onStart` était appelé dans le `Worker-0` lorsqu'il n'y avait qu'un seul processus de travail. Lorsque there were plusieurs processus de travail, ils étaient exécutés dans le processus `Manager`.


## v4.7.0


- Le `Table\Row` a été supprimé, la `Table` ne prend plus en charge la lecture et l'écriture au format d'array


## v4.6.0


- La limitation maximale de `session id` a été supprimée, elle n'est plus répétée

- Lors de l'utilisation des coroutines, les fonctionnalités insécurisées sont désactivées, y compris `pcntl_fork`/`pcntl_wait`/`pcntl_waitpid`/`pcntl_sigtimedwait`

- Le hook coroutine est activé par défaut

- La prise en charge de PHP7.1 est abandonnée
- `Event::rshutdown()` est marqué comme déprécié, veuillez utiliser Coroutine\run à la place


## v4.5.4


- `SWOOLE_HOOK_ALL` comprend désormais `SWOOLE_HOOK_CURL`
- Le `ssl_method` a été supprimé, en soutien au `ssl_protocols`


## v4.4.12


- Cette version prend en charge la compression des trames WebSocket, le troisième argument de la méthode `push` a été modifié en `flags`, si `strict_types` n'est pas défini, la compatibilité du code n'est pas affectée, sinon une erreur de type bool ne peut pas être implicitement convertie en int, cette issue sera corrigée dans la version v4.4.13


## v4.4.1


- Les signaux enregistrés ne sont plus utilisés comme condition pour maintenir l'événement loop, **si le programme enregistre uniquement des signaux sans effectuer d'autres tâches, il sera considéré comme inactif et quittera immédiatement** (à ce moment, enregister un timer peut empêcher le processus de quitter)


## v4.4.0


- En accord avec la官方 PHP, la prise en charge de PHP7.0 est abandonnée (@matyhtf)

- Le module `Serialize` a été supprimé et est maintenant maintenu dans l'extension séparée [ext-serialize](https://github.com/swoole/ext-serialize)

- Le module `PostgreSQL` a été supprimé et est maintenant maintenu dans l'extension séparée [ext-postgresql](https://github.com/swoole/ext-postgresql)

- `Runtime::enableCoroutine` ne sera plus automatiquement compatible avec l'environnement extérieur à la coroutine, une fois activé, toutes les opérations bloquantes doivent être appelées à l'intérieur de la coroutine (@matyhtf)
- En raison de l'introduction d'un nouveau client MySQL coroutine, la conception de base est devenue plus standardisée, mais il y a eu des changements mineurs vers le bas de compatibilité (voir [Journal des mises à jour 4.4.0](https://wiki.swoole.com/wiki/page/p-4.4.0.html))


## v4.3.0


- Tous les modules asynchrones ont été supprimés, voir [Extensions asynchrones indépendantes](https://wiki.swoole.com/wiki/page/p-async_ext.html) ou [Journal des mises à jour 4.3.0](https://wiki.swoole.com/wiki/page/p-4.3.0.html)


## v4.2.13

> En raison de modifications inévitables dues à des problèmes dans la conception de l'API historique

* Changement d'opération du mode d'abonnement du client Redis coroutine, voir [Mode d'abonnement](https://wiki.swoole.com/#/coroutine_client/redis?id=%e8%ae%a2%e9%98%85%e6%a8%a1%e5%bc%8f)


## v4.2.12

> Caractéristiques expérimentales + En raison de modifications inévitables dues à des problèmes dans la conception de l'API historique


- Le paramètre de configuration `task_async` a été supprimé, remplacé par [task_enable_coroutine](https://wiki.swoole.com/#/server/setting?id=task_enable_coroutine)


## v4.2.5


- La prise en charge des clients `UDP` par `onReceive` et `Server::getClientInfo` a été supprimée


## v4.2.0


- Le client `swoole_http2_client` asynchrone a été complètement supprimé, veuillez utiliser le client HTTP2 coroutine


## v4.0.4

À partir de cette version, le client `Http2\Client` asynchrone déclenchera un avertissement `E_DEPRECATED` et sera supprimé dans la prochaine version, veuillez utiliser `Coroutine\Http2\Client` à la place

La propriété `body` de la réponse `Http2\Response` a été renommée `data`, cette modification est destinée à garantir l'uniformité entre la `request` et la `response`, et correspond davantage au nom des types de trames du protocole HTTP2

À partir de cette version, `Coroutine\Http2\Client` possède une prise en charge complète du protocole HTTP2, capable de répondre aux besoins des environnements de production d'entreprise, tels que `grpc`, `etcd`, etc., donc les modifications liées à HTTP2 sont très nécessaires


## v4.0.3

Rendre `swoole_http2_response` et `swoole_http2_request` cohérents, tous les noms de propriété ont été modifiés en forme plurielle, impliquant les propriétés suivantes



- `headers`
- `cookies`


## v4.0.2

> En raison de la complexité de la mise en œuvre de base, difficile à maintenir, et des confusions fréquentes des utilisateurs sur son utilisation, les API suivants sont temporairement supprimés :


- `Coroutine\Channel::select`

Mais en même temps, un deuxième argument de la méthode `pop` de `Coroutine\Channel` a été ajouté pour `timeout` pour répondre aux besoins du développement


## v4.0

> En raison de l'upgrade du noyau coroutine, il est possible d'appeler des coroutines n'importe où dans n'importe quelle fonction sans traitement spécial, donc les API suivants ont été supprimées


- `Coroutine::call_user_func`
- `Coroutine::call_user_func_array`
