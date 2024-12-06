# Serveur (style asynchrone)

Créez facilement un programme de serveur asynchrone, supportant les trois types de sockets `TCP`, `UDP` et [unixSocket](/learn?id=什么是IPC), supportant les adresses IPv4 et IPv6, ainsi que l'encryptage de tunnel des certificats SSL/TLS unidirectionnels et bidirectionnels. Les utilisateurs n'ont pas besoin de se soucier des détails de la mise en œuvre sous-jacente, il suffit de configurer la fonction de rappel des événements réseau [Event](/server/events). Pour une référence, voir [Démarrage rapide](/start/start_tcp_server).

!> Notez que le style du `Server` est asynchrone (c'est-à-dire que tous les événements nécessitent une fonction de rappel), mais il prend également en charge les coroutines. Après avoir activé [enable_coroutine](/server/setting?id=enable_coroutine), il prend en charge les coroutines (désactivé par défaut), et tout le code métier sous coroutines est écrit de manière synchrone.

Pour en savoir plus :

[Présentation des trois modes de fonctionnement du serveur](/learn?id=server的三种运行模式介绍 ':target=_blank')  
[Quelle est la différence entre Process, ProcessPool, UserProcess](/learn?id=process-diff ':target=_blank')  
[Quelle est la différence et la connexion entre Master process, Reactor thread, Worker process, Task process, Manager process](/learn?id=diff-process ':target=_blank')  

### Flowchart de fonctionnement <!-- {docsify-ignore} -->

![running_process](https://wiki.swoole.com/_images/server/running_process.png ':size=800xauto')

### Structure de processus/threads <!-- {docsify-ignore} -->

![process_structure](https://wiki.swoole.com/_images/server/process_structure.png ':size=800xauto')

![process_structure_2](https://wiki.swoole.com/_images/server/process_structure_2.png)
