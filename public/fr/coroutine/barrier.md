# Co-rétine\Barrière

Dans la [Bibliothèque Swoole](https://github.com/swoole/library), un outil de gestion plus pratique des concurrences de co-rétines est fourni en dessous du niveau : la barrière de co-rétine `Coroutine\Barrier`, ou barrière de co-rétine. Elle est implémentée sur la base du comptage des références PHP et de l'API Co-rétine.

Contrairement à [Coroutine\WaitGroup](/coroutine/wait_group), l'utilisation de `Coroutine\Barrier` est un peu plus simple, il suffit de transmettre par paramètre ou d'utiliser la syntaxe `use` de la closure pour introduire la fonction de co-rétine child.

!> Disponible lorsque la version Swoole est >= v4.5.5.

## Exemple d'utilisation

```php
use Swoole\Coroutine\Barrier;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use Swoole\Coroutine;

run(function () {
    $barrier = Barrier::make();

    $count = 0;
    $N = 4;

    foreach (range(1, $N) as $i) {
        Coroutine::create(function () use ($barrier, &$count) {
            System::sleep(0.5);
            $count++;
        });
    }

    Barrier::wait($barrier);
    
    assert($count == $N);
});
```

## Processus d'exécution

* Créez d'abord une nouvelle barrière de co-rétine avec `Barrier::make()`.
* Dans les co-rétines enfants, utilisez la syntaxe `use` pour transmettre la barrière, augmentant le nombre de références.
* Insérez `Barrier::wait($barrier)` à l'endroit où vous devez attendre, ce qui suspendra automatiquement la co-rétine actuelle, attendant que les co-rétines enfants qui utilisent cette barrière de co-rétine se terminent.
* Lorsque les co-rétines enfants se terminent, elles réduisent le nombre de références de l'objet `$barrier`, jusqu'à atteindre zéro.
* Lorsque toutes les co-rétines enfants ont terminé leur traitement et se sont terminées, le nombre de références de l'objet `$barrier` est de zéro, et dans le destructeur de l'objet `$barrier`, les co-rétines suspendues sont automatiquement restaurées en dessous du niveau, et la fonction `Barrier::wait($barrier)` retourne.

`Coroutine\Barrier` est un contrôleur de concurrency plus facile à utiliser que [WaitGroup](/coroutine/wait_group) et [Channel](/coroutine/channel), améliorant considérablement l'expérience utilisateur de la programmation concurrencynelle PHP.
