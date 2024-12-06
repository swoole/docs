# Gestionnaire de Processus

Gestionnaire de processus, implémenté sur la base de [Process\Pool](/process/process_pool). Permet de gérer plusieurs processus. Contrairement à `Process\Pool`, il est très pratique de créer plusieurs processus exécutant différentes tâches et de contrôler si chaque processus doit fonctionner dans un environnement coroutine.


## Situation de support des versions

| Numéro de version | Nom de la classe                | Détails de mise à jour                       |
|-------------------|---------------------------------|--------------------------------------------|
| v4.5.3            | Swoole\Process\ProcessManager   | -                                           |
| v4.5.5            | Swoole\Process\Manager          | Renommage, ProcessManager est un alias de Manager |

!> Disponible à partir de la version `v4.5.3`.


## Exemple d'utilisation

```php
use Swoole\Process\Manager;
use Swoole\Process\Pool;

$pm = new Manager();

for ($i = 0; $i < 2; $i++) {
    $pm->add(function (Pool $pool, int $workerId) {
    });
}

$pm->start();
```


## Méthodes


### __construct()

Constructeur.

```php
Swoole\Process\Manager::__construct(int $ipcType = SWOOLE_IPC_NONE, int $msgQueueKey = 0);
```

* **Paramètres**

  * **`int $ipcType`**
    * **Fonction** : Mode de communication entre les processus, conforme à `$ipc_type` de `Process\Pool`【Par défaut `0` signifie ne pas utiliser de caractéristiques de communication entre processus】
    * **Valeur par défaut** : `0`
    * **Autres valeurs** : Aucun

  * **`int $msgQueueKey`**
    * **Fonction** : `key` de la queue de messages, conforme à `$msgqueue_key` de `Process\Pool`
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun


### setIPCType()

Définir le mode de communication entre les processus travaillant.

```php
Swoole\Process\Manager->setIPCType(int $ipcType): self;
```

* **Paramètres**

  * **`int $ipcType`**
    * **Fonction** : Mode de communication entre les processus
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun


### getIPCType()

Obtenir le mode de communication entre les processus travaillant.

```php
Swoole\Process\Manager->getIPCType(): int;
```


### setMsgQueueKey()

Définir la `key` de la queue de messages.

```php
Swoole\Process\Manager->setMsgQueueKey(int $msgQueueKey): self;
```

* **Paramètres**

  * **`int $msgQueueKey`**
    * **Fonction** : `key` de la queue de messages
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun


### getMsgQueueKey()

Obtenir la `key` de la queue de messages.

```php
Swoole\Process\Manager->getMsgQueueKey(): int;
```


### add()

Ajouter un processus de travail.

```php
Swoole\Process\Manager->add(callable $func, bool $enableCoroutine = false): self;
```

* **Paramètres**

  * **`callable $func`**
    * **Fonction** : La fonction de rappel exécutée par le processus actuel
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`bool $enableCoroutine`**
    * **Fonction** : Créer un coroutine pour exécuter la fonction de rappel dans ce processus
    * **Valeur par défaut** : `false`
    * **Autres valeurs** : Aucun


### addBatch()

Ajouter plusieurs processus de travail en batch.

```php
Swoole\Process\Manager->addBatch(int $workerNum, callable $func, bool $enableCoroutine = false): self
```

* **Paramètres**

  * **`int $workerNum`**
    * **Fonction** : Nombre de processus à ajouter en batch
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`callable $func`**
    * **Fonction** : La fonction de rappel exécutée par ces processus
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`bool $enableCoroutine`**
    * **Fonction** : Créer des coroutines pour exécuter la fonction de rappel dans ces processus
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

### start()

Démarrer les processus de travail.

```php
Swoole\Process\Manager->start(): void
```
