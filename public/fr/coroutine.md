# Coroutine <!-- {docsify-ignore-all} -->

Cette section présente quelques concepts de base et problèmes courants des coroutines.

À partir de la version 4.0, `Swoole` a fourni des fonctionnalités complètes de `Coroutine` + `Channel`, apportant un nouveau modèle de programmation `CSP`.

1. Les développeurs peuvent écrire du code synchrone sans conscience pour atteindre l'effet et les performances de l'[IO asynchrone](/learn?id=synchronisation-io-asynchrone), évitant ainsi la logique de code discrète apportée par les appels de rappel asynchrones traditionnels et l'impossibilité de maintenir le code en raison de l'entrée dans plusieurs niveaux de rappels.
2. En même temps, comme les coroutines sont encapsulées au niveau inférieur, contrairement aux cadres de coroutine traditionnels au niveau de `PHP`, les développeurs n'ont pas besoin d'utiliser le mot-clé [yield](https://www.php.net/manual/zh/language.generators.syntax.php) pour identifier une opération `IO` de coroutine, il n'est donc plus nécessaire de comprendre en profondeur la sémantique de `yield` et de modifier chaque appel en `yield`, ce qui améliore considérablement l'efficacité du développement.
3. Il fournit divers types de [clients de coroutine](/coroutine_client/init) complets, qui peuvent répondre aux besoins de la plupart des développeurs.

## Qu'est-ce qu'une coroutine

Les coroutines peuvent être simplement comprises comme des threads, sauf que ces threads sont dans l'espace utilisateur, sans nécessité d'intervention du système d'exploitation, le coût de création, de destruction et de commutation est très bas, contrairement aux threads, les coroutines ne peuvent pas exploiter plusieurs cœurs de CPU, pour utiliser plusieurs cœurs de CPU, il faut compter sur le modèle de processus multiple de `Swoole`.

## Qu'est-ce que Channel

`Channel` peut être compris comme une file d'attente de messages, sauf que c'est une file d'attente de messages entre coroutines, plusieurs coroutines utilisent les opérations `push` et `pop` pour produire et consommer des messages dans la file d'attente, pour envoyer ou recevoir des données pour la communication entre coroutines. Il est important de noter que `Channel` ne peut pas traverser les processus, il ne peut que communiquer entre les coroutines à l'intérieur d'un processus `Swoole`, les applications typiques sont [pool de connexions](/coroutine/conn_pool) et [appels concurrents](/coroutine/multi_call).

## Qu'est-ce que le conteneur de coroutine

Utilisez la méthode `Coroutine::create` ou `go()` pour créer une coroutine (voir [section des alias](/other/alias?id=nom-court-de-la-coroutine)), vous ne pouvez utiliser l'API de coroutine que dans la coroutine créée, et la coroutine doit être créée dans un conteneur de coroutine, voir [conteneur de coroutine](/coroutine/scheduler).

## Planification des coroutines

Ici, nous allons expliquer le plus simplement possible ce qu'est la planification des coroutines, tout d'abord chaque coroutine peut être simplement comprise comme un thread, vous savez que les multithreads sont là pour améliorer la concurrence du programme, de même les multicornoutines sont là pour améliorer la concurrence.

Chaque demande de l'utilisateur créera une coroutine, et la coroutine se terminera lorsque la demande est terminée, si à un moment donné il y a des dizaines de milliers de demandes concurrentes, à un certain moment, il y aura des dizaines de milliers de coroutines à l'intérieur d'un processus, alors les ressources CPU sont limitées, quelle coroutine va exécuter le code ?

Le processus de décision sur quelle coroutine doit exécuter le code est appelé `planification des coroutines`, quelle est la stratégie de planification de `Swoole` ?

- Tout d'abord, lors de l'exécution du code d'une coroutine, si cette ligne de code rencontre `Co::sleep()` ou génère une `IO` réseau, par exemple `MySQL->query()`, ce qui est certainement un processus long, `Swoole` placera le descripteur de connexion MySQL dans [EventLoop](/learn?id=quel-est-eventloop).
      
    * Ensuite, il cède le CPU de cette coroutine à d'autres coroutines : **c'est-à-dire `yield` (suspendu)**
    * Attendre que les données MySQL reviennent puis continuer à exécuter cette coroutine : **c'est-à-dire `resume` (restauré)**

- Deuxièmement, si le code de la coroutine contient du code intensif en CPU, vous pouvez activer [enable_preemptive_scheduler](/other/config), `Swoole` forcera cette coroutine à céder le CPU.

## Priorité des coroutines parent et enfant

Exécutez d'abord la coroutine enfant (c'est-à-dire la logique à l'intérieur de `go()`), jusqu'à ce qu'une coroutine `yield` se produise (à Co::sleep()), puis [planifiez](/coroutine?id=planification-des-coroutines) vers la coroutine externe.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

echo "main start\n";
run(function () {
    echo "coro " . Coroutine::getcid() . " start\n";
    Coroutine::create(function () {
        echo "coro " . Coroutine::getcid() . " start\n";
        Coroutine::sleep(.2);
        echo "coro " . Coroutine::getcid() . " end\n";
    });
    echo "coro " . Coroutine::getcid() . " do not wait children coroutine\n";
    Coroutine::sleep(.1);
    echo "coro " . Coroutine::getcid() . " end\n";
});
echo "end\n";

/*
main start
coro 1 start
coro 2 start
coro 1 do not wait children coroutine
coro 1 end
coro 2 end
end
*/
```

## Remarques

Avant de programmer avec Swoole, vous devriez prêter attention aux éléments suivants :

### Variables globales

Les coroutines rendent la logique asynchrone originale synchrone, mais le changement entre les coroutines est implicite, donc avant et après le changement de coroutine, on ne peut pas garantir la consistance des variables globales et des variables `static`.

Sous `PHP-FPM`, vous pouvez obtenir les paramètres de la demande, les paramètres du serveur, etc., par le biais de variables globales, mais dans `Swoole`, **vous ne pouvez pas** obtenir les attributs de paramètres par le biais de variables commençant par `$_` comme `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`.
Vous pouvez utiliser [contexte](/coroutine/coroutine?id=getcontext) pour isoler avec l'ID de la coroutine, réalisant l'isolement des variables globales.

### Partage de connexion TCP entre plusieurs coroutines

[Référence](/question/use?id=client-has-already-been-bound-to-another-coroutine)
