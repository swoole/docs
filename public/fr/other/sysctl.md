# Paramètres du noyau


## Paramètres ulimit

`ulimit -n` doit être ajusté à 100000 ou même plus grand. Exécutez `ulimit -n 100000` dans la ligne de commande pour modifier. Si cela ne peut pas être modifié, il est nécessaire de configurer le `/etc/security/limits.conf`, en ajoutant

```
* soft nofile 262140
* hard nofile 262140
root soft nofile 262140
root hard nofile 262140
* soft core unlimited
* hard core unlimited
root soft core unlimited
root hard core unlimited
```

Notez que après avoir modifié le fichier `limits.conf`, il est nécessaire de redémarrer le système pour que les modifications prennent effet.


## Paramètres du noyau

Il existe 3 façons de modifier les paramètres du noyau dans le système d'exploitation `Linux` :



- Modifiez le fichier `/etc/sysctl.conf`, ajoutez les options de configuration, format `key = value`, sauvegardez la modification et appelez `sysctl -p` pour charger la nouvelle configuration.

- Utilisez la commande `sysctl` pour modifier temporairement, par exemple : `sysctl -w net.ipv4.tcp_mem="379008 505344 758016"`

- Modifiez directement les fichiers dans le répertoire `/proc/sys/`, par exemple : `echo "379008 505344 758016" > /proc/sys/net/ipv4/tcp_mem`

> La première méthode prend effet automatiquement après un redémarrage du système d'exploitation, tandis que les deux dernières méthodes perdent leur effet après un redémarrage.


### net.unix.max_dgram_qlen = 100

Swoole utilise des sockets Unix datagrammes pour la communication entre processus. Si le volume des demandes est élevé, il est nécessaire d'ajuster ce paramètre. Le système a une valeur par défaut de 10, qui peut être augmenté à 100 ou plus grand. Ou alors, augmentez le nombre de processus workers pour réduire la charge de demande allouée à chaque processus worker.


### net.core.wmem_max

Modifiez ce paramètre pour augmenter la taille de la mémoire tampon des sockets.

```
net.ipv4.tcp_mem  =   379008       505344  758016
net.ipv4.tcp_wmem = 4096        16384   4194304
net.ipv4.tcp_rmem = 4096          87380   4194304
net.core.wmem_default = 8388608
net.core.rmem_default = 8388608
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
```


### net.ipv4.tcp_tw_reuse

Est-ce que le réusoie de socket est activé, cette fonction permet au serveur de réutiliser rapidement les ports en attente lors du redémarrage. Si ce paramètre n'est pas configuré, cela peut entraîner un échec du démarrage du serveur en raison de ports qui ne sont pas libérés à temps.


### net.ipv4.tcp_tw_recycle

Utilisation rapide du recyclage des sockets, les serveurs à connexion courte doivent activer ce paramètre. Ce paramètre indique l'activation du recyclage rapide des sockets en état TIME-WAIT dans les connexions TCP, le système Linux a une valeur par défaut de 0, ce qui signifie désactivé. Activer ce paramètre peut entraîner des connexions instables pour les utilisateurs NAT, veuillez le tester avec prudence avant d'activer.


## Paramètres de la file de message

Lorsque vous utilisez une file de message comme mode de communication entre processus, vous devez ajuster ce paramètre de noyau



- kernel.msgmnb = 4203520, la taille maximale des messages dans la file de message

- kernel.msgmni = 64, le nombre maximal de files de message autorisées à être créées
- kernel.msgmax = 8192, la longueur maximale d'une seule donnée dans la file de message


## FreeBSD/MacOS



- sysctl -w net.local.dgram.maxdgram=8192
- sysctl -w net.local.dgram.recvspace=200000
  Modifiez la taille de la zone tampon du socket Unix


## Activer CoreDump

Configurez les paramètres du noyau

```
kernel.core_pattern = /data/core_files/core-%e-%p-%t
```

Utilisez la commande `ulimit -c` pour afficher la limitation actuelle des fichiers CoreDump

```shell
ulimit -c
```

Si elle est à 0, il est nécessaire de modifier `/etc/security/limits.conf` pour effectuer des limites.

> Après avoir activé le CoreDump, si un programme rencontre une anomalie, il exporte le processus dans un fichier. Cela est très utile pour enquêter sur les problèmes du programme.


## Autres configurations importantes



- net.ipv4.tcp_syncookies=1

- net.ipv4.tcp_max_syn_backlog=81920

- net.ipv4.tcp_synack_retries=3

- net.ipv4.tcp_syn_retries=3

- net.ipv4.tcp_fin_timeout = 30

- net.ipv4.tcp_keepalive_time = 300

- net.ipv4.tcp_tw_reuse = 1

- net.ipv4.tcp_tw_recycle = 1

- net.ipv4.ip_local_port_range = 20000 65000

- net.ipv4.tcp_max_tw_buckets = 200000
- net.ipv4.route.max_size = 5242880

## Verifier si la configuration est active

Par exemple, après avoir modifié `net.unix.max_dgram_qlen = 100`, utilisez

```shell
cat /proc/sys/net/unix/max_dgram_qlen
```

Si la modification est réussie, voici la nouvelle valeur définie.
