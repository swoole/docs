# Installation de Swoole

L'extension `Swoole` est construite selon les normes d'extensions PHP. Utilisez `phpize` pour générer un script de détection de compilation, `./configure` pour effectuer la détection de configuration de compilation, `make` pour compiler et `make install` pour installer.

* Sauf besoins particuliers, veuillez compiler et installer la dernière version de [Swoole](https://github.com/swoole/swoole-src/releases/).
* Si l'utilisateur actuel n'est pas `root`, il se peut qu'il n'ait pas les droits d'écriture dans le répertoire d'installation de PHP, l'installation nécessitera `sudo` ou `su`.
* Si vous mettez à jour le code directement sur une branche git avec `git pull`, assurez-vous d'exécuter `make clean` avant de recompiler.
* Pris en charge uniquement sur les systèmes d'exploitation `Linux` (noyau supérieur à 2.3.32), `FreeBSD`, `MacOS`.
* Sur les anciens systèmes Linux (comme `CentOS 6`), vous pouvez compiler avec les `devtools` fournis par `RedHat`, [voir la documentation](https://blog.csdn.net/ppdouble/article/details/52894271).
* Sur la plateforme `Windows`, vous pouvez utiliser `WSL (Windows Subsystem for Linux)` ou `CygWin`.
* Certaines extensions ne sont pas compatibles avec l'extension Swoole, voir [Conflits d'extensions](/getting_started/extension).

## Préparation à l'installation

Avant l'installation, assurez-vous que le système a déjà installé les logiciels suivants

- `4.8` version nécessite `PHP-7.2` ou une version supérieure

- `5.0` version nécessite `PHP-8.0` ou une version supérieure

- `6.0` version nécessite `PHP-8.1` ou une version supérieure

- `gcc-4.8` ou une version supérieure

- `make`
- `autoconf`

## Installation rapide

> 1.Télécharger le code source de swoole

* [https://github.com/swoole/swoole-src/releases](https://github.com/swoole/swoole-src/releases)
* [https://pecl.php.net/package/swoole](https://pecl.php.net/package/swoole)
* [https://gitee.com/swoole/swoole/tags](https://gitee.com/swoole/swoole/tags)

> 2.Compilation et installation à partir du code source

Après avoir téléchargé le paquet de code source, entrez dans le répertoire du code source dans le terminal et exécutez les commandes suivantes pour compiler et installer

!> Sur ubuntu, si phpize n'est pas installé, vous pouvez exécuter la commande : `sudo apt-get install php-dev` pour installer phpize

```shell
cd swoole-src && \
phpize && \
./configure && \
sudo make && sudo make install
```

> 3.Activer l'extension

Après un succès de compilation et d'installation dans le système, vous devez ajouter une ligne `extension=swoole.so` dans `php.ini` pour activer l'extension Swoole

## Exemple de compilation avancée complète

!> Les développeurs qui découvrent Swoole pour la première fois doivent d'abord essayer la compilation simple ci-dessus, s'ils ont des besoins supplémentaires, ils peuvent ajuster les paramètres de compilation dans l'exemple suivant selon leurs besoins spécifiques et versions. [Référence des paramètres de compilation](/environment?id=Options de compilation)

Le script suivant téléchargera et compilera le code source de la branche `master`, assurez-vous d'avoir installé toutes les dépendances, sinon vous rencontrerez diverses erreurs de dépendance.

```shell
mkdir -p ~/build && \
cd ~/build && \
rm -rf ./swoole-src && \
curl -o ./tmp/swoole.tar.gz https://github.com/swoole/swoole-src/archive/master.tar.gz -L && \
tar zxvf ./tmp/swoole.tar.gz && \
mv swoole-src* swoole-src && \
cd swoole-src && \
phpize && \
./configure \
--enable-openssl --enable-sockets --enable-mysqlnd --enable-swoole-curl --enable-cares --enable-swoole-pgsql && \
sudo make && sudo make install
```

## PECL

> Remarque : La publication PECL est plus tardive que la publication GitHub

Le projet Swoole a été inclus dans la bibliothèque officielle d'extensions PHP, en plus du téléchargement et de la compilation manuels, vous pouvez également utiliser la commande `pecl` fournie par PHP pour télécharger et installer en un clic

```shell
pecl install swoole
```

Lors de l'installation de Swoole via PECL, il demandera pendant l'installation si vous souhaitez activer certaines fonctionnalités, ce qui peut également être fourni avant d'exécuter l'installation, par exemple :

```shell
pecl install -D 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole

#ou
pecl install --configureoptions 'enable-sockets="no" enable-openssl="yes" enable-http2="yes" enable-mysqlnd="yes" enable-swoole-json="no" enable-swoole-curl="yes" enable-cares="yes"' swoole
```

## Ajouter Swoole à php.ini

Enfin, après un succès de compilation et d'installation, modifiez `php.ini` pour ajouter

```ini
extension=swoole.so
```

Utilisez `php -m` pour vérifier si `swoole.so` a été chargé avec succès, si ce n'est pas le cas, le chemin de `php.ini` pourrait être incorrect.  
Vous pouvez utiliser `php --ini` pour localiser le chemin absolu de `php.ini`, l'élément `Loaded Configuration File` affiche le fichier php.ini chargé, si la valeur est `none`, cela signifie qu'aucun fichier php.ini n'a été chargé, vous devez en créer un vous-même.

!> Conserver la compatibilité avec les versions PHP prises en charge et les versions PHP officiellement maintenues, voir [Calendrier de support des versions PHP](http://php.net/supported-versions.php)

## Compilation sur d'autres plateformes

Plateforme ARM (Raspberry PI)

* Utilisez la compilation croisée `GCC`
* Lors de la compilation de `Swoole`, vous devez modifier manuellement le `Makefile` pour supprimer le paramètre de compilation `-O2`

Plateforme MIPS (routeur OpenWrt)

* Utilisez la compilation croisée GCC

Windows WSL

Le système d'exploitation `Windows 10` a ajouté la prise en charge du sous-système Linux, l'environnement `BashOnWindows` peut également utiliser `Swoole`. Commandes d'installation

```shell
apt-get install php7.0 php7.0-curl php7.0-gd php7.0-gmp php7.0-json php7.0-mysql php7.0-opcache php7.0-readline php7.0-sqlite3 php7.0-tidy php7.0-xml  php7.0-bcmath php7.0-bz2 php7.0-intl php7.0-mbstring  php7.0-mcrypt php7.0-soap php7.0-xsl  php7.0-zip
pecl install swoole
echo 'extension=swoole.so' >> /etc/php/7.0/mods-available/swoole.ini
cd /etc/php/7.0/cli/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
cd /etc/php/7.0/fpm/conf.d/ && ln -s ../../mods-available/swoole.ini 20-swoole.ini
```

!> Dans l'environnement `WSL`, vous devez désactiver l'option `daemonize`  
Pour les versions de `WSL` inférieures à `17101`, après `configure` du code source, vous devez modifier `config.h` pour désactiver `HAVE_SIGNALFD`

## Image Docker officielle

- GitHub: [https://github.com/swoole/docker-swoole](https://github.com/swoole/docker-swoole)  
- dockerhub: [https://hub.docker.com/r/phpswoole/swoole](https://hub.docker.com/r/phpswoole/swoole)

## Options de compilation

Voici des paramètres supplémentaires pour la configuration de compilation `./configure`, utilisés pour activer certaines fonctionnalités

### Paramètres généraux

#### --enable-openssl

Activer la prise en charge de `SSL`

> Utilise la bibliothèque de connexion dynamique `libssl.so` fournie par le système d'exploitation

#### --with-openssl-dir

Activer la prise en charge de `SSL` et spécifier le chemin de la bibliothèque `openssl`, suivez-le avec le paramètre de chemin, par exemple : `--with-openssl-dir=/opt/openssl/`

#### --enable-http2

Activer la prise en charge de `HTTP2`

> Dépend de la bibliothèque `nghttp2`. Après la version `V4.3.0`, il n'est plus nécessaire d'installer la dépendance, elle est intégrée, mais il est toujours nécessaire d'ajouter ce paramètre de compilation pour activer la prise en charge de `http2`, `Swoole5` active par défaut ce paramètre.

#### --enable-swoole-json

Activer la prise en charge de [swoole_substr_json_decode](/functions?id=swoole_substr_json_decode), `Swoole5` active par défaut ce paramètre

> Dépend de l'extension `json`, disponible à partir de la version `v4.5.7`

#### --enable-swoole-curl

Activer la prise en charge de [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl), pour activer cela, assurez-vous que `php` et `Swoole` utilisent la même bibliothèque partagée `libcurl` et les mêmes fichiers d'en-tête, sinon des problèmes imprévisibles peuvent survenir.

> Disponible à partir de la version `v4.6.0`. Si la compilation échoue avec l'erreur `curl/curl.h: No such file or directory`, veuillez consulter [Problèmes d'installation](/question/install?id=libcurl)

#### --enable-cares

Activer la prise en charge de `c-ares`

> Dépend de la bibliothèque `c-ares`, disponible à partir de la version `v4.7.0`. Si la compilation échoue avec l'erreur `ares.h: No such file or directory`, veuillez consulter [Problèmes d'installation](/question/install?id=libcares)

#### --with-jemalloc-dir

Activer la prise en charge de `jemalloc`

#### --enable-brotli

Activer la prise en charge de la compression `libbrotli`

#### --with-brotli-dir

Activer la prise en charge de la compression `libbrotli` et spécifier le chemin de la bibliothèque `libbrotli`, suivez-le avec le paramètre de chemin, par exemple : `--with-brotli-dir=/opt/brotli/`

#### --enable-swoole-pgsql

Activer la coroutine pour la base de données `PostgreSQL`.

> Avant `Swoole5.0`, la coroutine était utilisée pour la coroutine de `PostgreSQL` avec le client coroutine, après `Swoole5.1`, en plus de l'utilisation du client coroutine pour la coroutine, il est également possible d'utiliser la coroutine native `pdo_pgsql` pour la coroutine de `PostgreSQL`.

#### --with-swoole-odbc

Activer la coroutine pour `pdo_odbc`, après activation de ce paramètre, toutes les bases de données prenant en charge l'interface `odbc` peuvent être coroutine.

>`Disponible à partir de la version v5.1.0, nécessite la dépendance unixodbc-dev`

Exemple de configuration

```
with-swoole-odbc="unixODBC,/usr"
```

#### --with-swoole-oracle

Activer la coroutine pour `pdo_oci`, après activation de ce paramètre, toutes les opérations de base de données `oracle` déclencheront des opérations coroutine.

>`Disponible à partir de la version v5.1.0`

#### --enable-swoole-sqlite

Activer la coroutine pour `pdo_sqlite`, après activation de ce paramètre, toutes les opérations de base de données `sqlite` déclencheront des opérations coroutine.

>`Disponible à partir de la version v5.1.0`

#### --enable-swoole-thread

Activer le mode multithread de `swoole`, après ajout de cette option de compilation, `Swoole` passera d'un modèle de plusieurs processus et un seul thread à un modèle d'un seul processus et plusieurs threads.

>`Disponible à partir de la version v6.0`, et `PHP` doit être en mode `ZTS`

#### --enable-iouring

Après avoir ajouté cette option de compilation, le traitement asynchrone des fichiers de `swoole` passera du thread asynchrone au mode `iouring`.

>`Disponible à partir de la version v6.0`, et nécessite l'installation de la dépendance `liburing` pour prendre en charge cette fonctionnalité, si les performances du disque sont bonnes, les deux modes ont peu de différence de performance, seulement sous une grande pression `I/O`, le mode `iouring` aura de meilleures performances que le mode thread asynchrone.
### Paramètres spéciaux

!> **Il n'est pas recommandé d'activer sans raison historique**

#### --enable-mysqlnd

Activez la prise en charge de `mysqlnd`, activez la méthode `Coroutine\MySQL::escapse`. Après activation de ce paramètre, `PHP` doit avoir le module `mysqlnd`, sinon cela empêchera `Swoole` de fonctionner.

> Dépend de l'extension `mysqlnd`

#### --enable-sockets

Ajoutez la prise en charge des ressources `sockets` de PHP. Lorsque ce paramètre est activé, [Swoole\Event::add](/event?id=add) peut ajouter des connexions créées par l'extension `sockets` au [circuit d'événements](/learn?id=quelques-conseils-sur-le-devenirmachinelearningengineer) de `Swoole`.
Les méthodes [getSocket()](/server/methods?id=getsocket) de `Server` et `Client` dépendent également de ce paramètre de compilation.

> Dépend de l'extension `sockets`, l'effet de ce paramètre a été affaibli après la version `v4.3.2`, car le [Coroutine\Socket](/coroutine_client/socket) intégré à Swoole peut accomplir la plupart des tâches

### Paramètres de débogage

!> **Ne doit pas être activé en environnement de production**

#### --enable-debug

Activez le mode débogage. Utilisez `gdb` pour suivre et ajoutez ce paramètre lors de la compilation de `Swoole`.

#### --enable-debug-log

Ouvrez le journal de débogage du noyau. **(Version Swoole >= 4.2.0)**

#### --enable-trace-log

Ouvrez le journal de suivi, après activation de cette option, swoole affichera divers journaux de débogage détaillés, à utiliser uniquement pour le développement du noyau

#### --enable-swoole-coro-time

Activez le calcul du temps d'exécution des coroutines, après activation de cette option, vous pouvez utiliser `Swoole\Coroutine::getExecuteTime()` pour calculer le temps d'exécution des coroutines, à l'exclusion du temps d'attente I/O.

### Paramètres de compilation PHP

#### --enable-swoole

Compilez statiquement l'extension Swoole dans PHP, selon les opérations suivantes, l'option `--enable-swoole` apparaîtra.

```shell
cp -r /home/swoole-src /home/php-src/ext
cd /home/php-src
./buildconf --force
./configure --help | grep swoole
```

!> Cette option est utilisée lors de la compilation de PHP et non de Swoole

## Problèmes courants

* [Problèmes courants d'installation de Swoole](/question/install)
