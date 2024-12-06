# Co-routine\WaitGroup

Dans `Swoole4`, il est possible d'utiliser [Channel](/coroutine/channel) pour réaliser la communication entre co-rôles, la gestion des dépendances et la synchronisation des co-rôles. Basé sur [Channel](/coroutine/channel), il est facile de réaliser la fonction `sync.WaitGroup` de `Golang`.

## Code d'implémentation

> Cette fonction est écrite en PHP et n'est pas en C/C++. Le code source est disponible dans la [Bibliothèque](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php).

* La méthode `add` augmente le compteur.
* `done` indique que la tâche est terminée.
* `wait` attend que toutes les tâches soient terminées pour reprendre l'exécution du co-rôle actuel.
* Un objet `WaitGroup` peut être réutilisé après avoir appelé `add`, `done` et `wait`.

## Exemple d'utilisation

```php
<?php
use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $wg = new WaitGroup();
    $result = [];

    $wg->add();
    // Déclenchement du premier co-rôle
    Coroutine::create(function () use ($wg, &$result) {
        // Création d'un client co-rôle pour demander la page d'accueil de Taobao
        $cli = new Client('www.taobao.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.taobao.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['taobao'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    $wg->add();
    // Déclenchement du deuxième co-rôle
    Coroutine::create(function () use ($wg, &$result) {
        // Création d'un client co-rôle pour demander la page d'accueil de Baidu
        $cli = new Client('www.baidu.com', 443, true);
        $cli->setHeaders([
            'Host' => 'www.baidu.com',
            'User-Agent' => 'Chrome/49.0.2587.3',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $cli->get('/index.php');

        $result['baidu'] = $cli->body;
        $cli->close();

        $wg->done();
    });

    // En attente de l'achèvement de toutes les tâches pour reprendre l'exécution du co-rôle actuel
    $wg->wait();
    // Ici, $result contient les résultats des deux tâches exécutées
    var_dump($result);
});
```
