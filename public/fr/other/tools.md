# Utilisation des outils


## yasd

[yasd](https://github.com/swoole/yasd)

Un outil de débogage en un seul pas, utilisable dans un environnement de coroutines Swoole, qui prend en charge le débogage dans les IDE et en mode commande.


## tcpdump

Lors du débogage de programmes de communication réseau, tcpdump est un outil indispensable. Tcpdump est très puissant, il permet de voir tous les détails de la communication réseau. Par exemple, pour le TCP, il est possible de voir l'établissement de la connection à trois mains, le push/ack des données, l'achèvement de la connexion à quatre mains, tous les détails. Cela inclut le nombre de bytes reçus par chaque paquet réseau, le temps, etc.


### Méthode d'utilisation

Un exemple simple d'utilisation :

```shell
sudo tcpdump -i any tcp port 9501
```
* Le paramètre `-i` spécifie la carte réseau, `any` signifie toutes les cartes réseau
* Le TCP indique que l'on écoute uniquement le protocole TCP
* Le port spécifie le port d'écoute

!> tcpdump nécessite des droits root ; si l'on veut voir le contenu des données de communication, on peut ajouter le paramètre `-Xnlps0`, pour plus de paramètres, veuillez consulter des articles en ligne.


### Résultat de l'exécution

```
13:29:07.788802 IP localhost.42333 > localhost.9501: Flags [S], seq 828582357, win 43690, options [mss 65495,sackOK,TS val 2207513 ecr 0,nop,wscale 7], length 0
13:29:07.788815 IP localhost.9501 > localhost.42333: Flags [S.], seq 1242884615, ack 828582358, win 43690, options [mss 65495,sackOK,TS val 2207513 ecr 2207513,nop,wscale 7], length 0
13:29:07.788830 IP localhost.42333 > localhost.9501: Flags [.], ack 1, win 342, options [nop,nop,TS val 2207513 ecr 2207513], length 0
13:29:10.298686 IP localhost.42333 > localhost.9501: Flags [P.], seq 1:5, ack 1, win 342, options [nop,nop,TS val 2208141 ecr 2207513], length 4
13:29:10.298708 IP localhost.9501 > localhost.42333: Flags [.], ack 5, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 0
13:29:10.298795 IP localhost.9501 > localhost.42333: Flags [P.], seq 1:13, ack 5, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 12
13:29:10.298803 IP localhost.42333 > localhost.9501: Flags [.], ack 13, win 342, options [nop,nop,TS val 2208141 ecr 2208141], length 0
13:29:11.563361 IP localhost.42333 > localhost.9501: Flags [F.], seq 5, ack 13, win 342, options [nop,nop,TS val 2208457 ecr 2208141], length 0
13:29:11.563450 IP localhost.9501 > localhost.42333: Flags [F.], seq 13, ack 6, win 342, options [nop,nop,TS val 2208457 ecr 2208457], length 0
13:29:11.563473 IP localhost.42333 > localhost.9501: Flags [.], ack 14, win 342, options [nop,nop,TS val 2208457 ecr 2208457], length 0
```
* `13:29:11.563473` Le temps est précis jusqu'à la microseconde
*  localhost.42333 > localhost.9501 Indique la direction de la communication, 42333 est le client, 9501 est le serveur
* [S] Indique qu'il s'agit d'une demande SYN
* [.] Indique qu'il s'agit d'un paquet ACK de confirmation, (client)SYN->(server)SYN->(client)ACK est le processus de trois mains
* [P] Indique qu'il s'agit d'un push de données, qui peut être de la part du serveur vers le client ou du client vers le serveur
* [F] Indique qu'il s'agit d'un paquet FIN, qui est une opération de fermeture de connexion, que le client ou le serveur puisse lancer
* [R] Indique qu'il s'agit d'un paquet RST, qui a la même fonction que le F, mais le RST indique que la connexion est fermée et qu'il y a encore des données non traitées. Cela peut être compris comme une coupure forcée de la connexion
* win 342 fait référence à la taille de la fenêtre coulissante
* length 12 fait référence à la taille du paquet de données


## strace

strace peut suivre l'exécution des appels système, et après que le programme a rencontré un problème, il peut utiliser strace pour analyser et suivre le problème.

!> Sur FreeBSD/MacOS, on peut utiliser truss


### Méthode d'utilisation

```shell
strace -o /tmp/strace.log -f -p $PID
```

* `-f` signifie suivre les threads et les processus multiples, sans le paramètre `-f`, il est impossible de capturer l'exécution des processus fils et des threads
* `-o` signifie que les résultats seront Exportés dans un fichier
* `-p $PID`, spécifier le PID du processus à suivre, qui peut être vu avec `ps aux`
* `-tt` imprime l'heure à laquelle les appels système se produisent, précis jusqu'à la microseconde
* `-s` limite la longueur de l'impression des chaînes, comme les données reçues par l'appel système recvfrom, par défaut, il n'imprime que 32 bytes
* `-c` Statistiques en temps réel de chaque appel système
* `-T` Imprime le temps pris par chaque appel système


## gdb

GDB est un outil de débogage puissant publié par l'organisation GNU, qui peut être utilisé pour déboguer des programmes développés en C/C++, PHP et Swoole sont développés en C, donc GDB peut être utilisé pour déboguer des programmes PHP+Swoole.

Le débogage avec GDB est interactif en commande ligne et nécessite de maîtriser les commandes courantes.


### Méthode d'utilisation

```shell
gdb -p 进程ID
gdb php
gdb php core
```

Il y a 3 façons d'utiliser GDB :

* Suivre un programme PHP en cours d'exécution, utilisez `gdb -p 进程ID`
* Exécuter et déboguer un programme PHP avec GDB, utilisez `gdb php -> run server.php` pour déboguer
* Après qu'un programme PHP a généré un core dump, utilisez GDB pour charger l'image de mémoire core pour le débogage `gdb php core`

!> Si l'environnement PATH ne contient pas php, GDB doit spécifier la voie absolue, comme `gdb /usr/local/bin/php`


### Commandes courantes

* `p` : print, imprimer la valeur d'une variable C
* `c` : continue, continuer à exécuter le programme qui a été arrêté
* `b` : breakpoint, établir un point d'arrêt, cela peut être fait par fonction name comme `b zif_php_function`, ou par numéro de ligne de code source comme `b src/networker/Server.c:1000`
* `t` : thread, passer à un autre thread, si le processus possède plusieurs threads, on peut utiliser la commande t pour passer à différents threads
* `ctrl + c` : arrêter le programme qui est actuellement en cours d'exécution, utilisé en combinaison avec la commande c
* `n` : next, exécuter la ligne suivante, déboguer pas à pas
* `info threads` : voir tous les threads en cours d'exécution
* `l` : list, voir le code source, cela peut être fait avec `l fonction name` ou `l numéro de ligne`
* `bt` : backtrace, voir l'appel de fonction en cours d'exécution
* `finish` : terminer la fonction actuelle
* `f` : frame, utilisé avec bt, pour passer à un certain niveau dans l'appel de fonction
* `r` : run, exécuter le programme


## zbacktrace

zbacktrace est une commande personnalisée GDB fournie par le paquet source PHP, dont la fonction est similaire à celle de bt, mais ce qui diffère de bt, c'est que l'appel de fonction vu par zbacktrace est l'appel de fonction PHP, et non celui en C.

Téléchargez le paquet source PHP, dézippez-le, puis trouvez un fichier `.gdbinit` dans le répertoire racine, et dans la shell GDB, entrez

```shell
source .gdbinit
zbacktrace
```
Le fichier `.gdbinit` fournit également d'autres commandes supplémentaires, qui peuvent être utilisées pour consulter le code source et obtenir des informations plus détaillées.

#### Utiliser gdb+zbacktrace pour suivre un problème de boucle infinie

```shell
gdb -p 进程ID
```

* Utilisez la commande `ps aux` pour trouver le PID du Worker qui a un problème de boucle infinie
* `gdb -p` pour suivre le processus spécifié
* Appuyez à plusieurs reprises sur `ctrl + c`, `zbacktrace`, `c` pour voir où le programme est en train de faire une boucle dans quel morceau de code PHP
* Trovez le code PHP correspondant et résolvez le problème


## lsof

La plateforme Linux offre l'outil `lsof` qui permet d'afficher les handles de fichiers ouverts par un processus. Il peut être utilisé pour suivre toutes les sockets, fichiers et ressources ouvertes par les processus de travail de swoole.


### Méthode d'utilisation

```shell
lsof -p [ID du processus]
```


### Résultat de l'exécution

```shell
lsof -p 26821
lsof: WARNING: can't stat() tracefs file system /sys/kernel/debug/tracing
      Output information may be incomplete.
COMMAND   PID USER   FD      TYPE             DEVICE SIZE/OFF    NODE NAME
php     26821  htf  cwd       DIR                8,4     4096 5375979 /home/htf/workspace/swoole/examples
php     26821  htf  rtd       DIR                8,4     4096       2 /
php     26821  htf  txt       REG                8,4 24192400 6160666 /opt/php/php-5.6/bin/php
php     26821  htf  DEL       REG                0,5          7204965 /dev/zero
php     26821  htf  DEL       REG                0,5          7204960 /dev/zero
php     26821  htf  DEL       REG                0,5          7204958 /dev/zero
php     26821  htf  DEL       REG                0,5          7204957 /dev/zero
php     26821  htf  DEL       REG                0,5          7204945 /dev/zero
php     26821  htf  mem       REG                8,4   761912 6160770 /opt/php/php-5.6/lib/php/extensions/debug-zts-20131226/gd.so
php     26821  htf  mem       REG                8,4  2769230 2757968 /usr/local/lib/libcrypto.so.1.1
php     26821  htf  mem       REG                8,4   162632 6322346 /lib/x86_64-linux-gnu/ld-2.23.so
php     26821  htf  DEL       REG                0,5          7204959 /dev/zero
php     26821  htf    0u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    1u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    2u      CHR             136,20      0t0      23 /dev/pts/20
php     26821  htf    3r      CHR                1,9      0t0      11 /dev/urandom
php     26821  htf    4u     IPv4            7204948      0t0     TCP *:9501 (LISTEN)
php     26821  htf    5u     IPv4            7204949      0t0     UDP *:9502 
php     26821  htf    6u     IPv6            7204950      0t0     TCP *:9503 (LISTEN)
php     26821  htf    7u     IPv6            7204951      0t0     UDP *:9504 
php     26821  htf    8u     IPv4            7204952      0t0     TCP localhost:8000 (LISTEN)
php     26821  htf    9u     unix 0x0000000000000000      0t0 7204953 type=DGRAM
php     26821  htf   10u     unix 0x0000000000000000      0t0 7204954 type=DGRAM
php     26821  htf   11u     unix 0x0000000000000000      0t0 7204955 type=DGRAM
php     26821  htf   12u     unix 0x0000000000000000      0t0 7204956 type=DGRAM
php     26821  htf   13u  a_inode               0,11        0    9043 [eventfd]
php     26821  htf   14u     unix 0x0000000000000000      0t0 7204961 type=DGRAM
php     26821  htf   15u     unix 0x0000000000000000      0t0 7204962 type=DGRAM
php     26821  htf   16u     unix 0x0000000000000000      0t0 7204963 type=DGRAM
php     26821  htf   17u     unix 0x0000000000000000      0t0 7204964 type=DGRAM
php     26821  htf   18u  a_inode               0,11        0    9043 [eventpoll]
php     26821  htf   19u  a_inode               0,11        0    9043 [signalfd]
php     26821  htf   20u  a_inode               0,11        0    9043 [eventpoll]
php     26821  htf   22u     IPv4            7452776      0t0     TCP localhost:9501->localhost:59056 (ESTABLISHED)
```

* Les fichiers .so sont les bibliothèques dynamiques chargées par le processus
* Les IPv4/IPv6 TCP (LISTEN) représentent les ports sur lesquels le serveur est en attente d'connections
* Les UDP représentent les ports UDP sur lesquels le serveur est en attente d'connections
* Quand c'est de type unix et type=DGRAM, cela représente un [socket Unix](/learn?id=什么是IPC) créé par le processus
* Les IPv4 (ESTABLISHED) indiquent un client TCP connecté au serveur, incluant l'IP et le PORT du client, ainsi que l'état (ESTABLISHED)
* Les numéros de fichier (fd) 9u / 10u représentent la valeur du fd de ce handle de fichier (déscription du fichier)
* Pour plus d'informations, vous pouvez consulter la documentation de l'outil lsof


## perf

L'outil `perf` est une très puissante tool de traçage dynamique fournie par le noyau Linux. La commande `perf top` peut être utilisée pour analyser en temps réel les problèmes de performance d'un programme en cours d'exécution. Contrairement à des outils tels que `callgrind`, `xdebug`, `xhprof`, `perf` ne nécessite pas de modifier le code pour exporter les résultats du profile.


### Méthode d'utilisation

```shell
perf top -p [ID du processus]
```

### Résultat de l'exécution

![résultat de perf top](../_images/other/perf.png)

Les résultats de `perf` montrent clairement le temps de Execution de chaque fonction C en cours d'exécution du processus actuel, ce qui permet de comprendre quelle fonction C consomme le plus les ressources CPU.

Si vous êtes familier avec le VM Zend, trop de appels à certaines fonctions Zend peuvent indiquer que votre programme utilise massivement certaines fonctions, ce qui peut entraîner une forte occupation des ressources CPU. Dans ce cas, une optimisation ciblée peut être effectuée.
