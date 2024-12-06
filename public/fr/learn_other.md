# Autres connaissances


## Établir une timeout et des tentatives de résolution DNS

Dans la programmation réseau, les fonctions `gethostbyname` et `getaddrinfo` sont souvent utilisées pour réaliser la résolution de noms de domaine, mais ces deux fonctions `C` ne fournissent pas de paramètres de timeout. En fait, il est possible de modifier le fichier `/etc/resolv.conf` pour établir la logique de timeout et de redémarrage.

!> Pour référence, veuillez consulter la documentation `man resolv.conf`


### Plusieurs Serveurs de Noms <!-- {docsify-ignore} -->

```
nameserver 192.168.1.3
nameserver 192.168.1.5
option rotate
```

Il est possible de configurer plusieurs `nameserver`, et le système fonctionnera automatiquement en cycle de ces serveurs, passant au deuxième serveur pour redémarrer en cas d'échec de la première demande de résolution.

L'option `rotate` est conçue pour réaliser un équilibrage de charge entre les serveurs de noms, en utilisant un mode de cycle.


### Contrôle du Timeout <!-- {docsify-ignore} -->

```
option timeout:1 attempts:2
```

* `timeout` : contrôle le temps de réponse `UDP`, en secondes, par défaut est de `5` secondes
* `attempts` : contrôle le nombre de tentatives,Configuration de `2` signifie qu'il y aura jusqu'à `2` tentatives, par défaut est de `5` tentatives

Supposons qu'il y ait `2` serveurs de noms et que `attempts` soit de `2`, avec un timeout de `1`, alors en cas de réponse inexistante de tous les serveurs DNS, la durée maximale d'attente serait de `4` secondes (`2x2x1`).

### Suivi des Appels <!-- {docsify-ignore} -->

Il est possible d'utiliser [strace](/other/tools?id=strace) pour suivre et confirmer.

Mettez les serveurs de noms à deux IP non existantes, et utilisez le code PHP `var_dump(gethostbyname('www.baidu.com'));` pour résoudre le nom de domaine.

```
socket(AF_INET, SOCK_DGRAM|SOCK_CLOEXEC|SOCK_NONBLOCK, IPPROTO_IP) = 3
connect(3, {sa_family=AF_INET, sin_port=htons(53), sin_addr=inet_addr("10.20.128.16")}, 16) = 0
poll([{fd=3, events=POLLOUT}], 1, 0)    = 1 ([{fd=3, revents=POLLOUT}])
sendto(3, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=3, events=POLLIN}], 1, 1000

)  = 0 (Timeout)
socket(AF_INET, SOCK_DGRAM|SOCK_CLOEXEC|SOCK_NONBLOCK, IPPROTO_IP) = 4
connect(4, {sa_family=AF_INET, sin_port=htons(53), sin_addr=inet_addr("10.20.128.18")}, 16) = 0
poll([{fd=4, events=POLLOUT}], 1, 0)    = 1 ([{fd=4, revents=POLLOUT}])
sendto(4, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=4, events=POLLIN}], 1, 1000


)  = 0 (Timeout)
poll([{fd=3, events=POLLOUT}], 1, 0)    = 1 ([{fd=3, revents=POLLOUT}])
sendto(3, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=3, events=POLLIN}], 1, 1000


)  = 0 (Timeout)
poll([{fd=4, events=POLLOUT}], 1, 0)    = 1 ([{fd=4, revents=POLLOUT}])
sendto(4, "\346\5\1\0\0\1\0\0\0\0\0\0\3www\5baidu\3com\0\0\1\0\1", 31, MSG_NOSIGNAL, NULL, 0) = 31
poll([{fd=4, events=POLLIN}], 1, 1000



)  = 0 (Timeout)
close(3)                                = 0
close(4)                                = 0
```

On peut voir qu'il y a eu un total de `4` tentatives de redémarrage, avec un timeout de `poll` fixé à `1000ms` (1 seconde).
