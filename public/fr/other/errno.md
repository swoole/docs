# Étiquettes d'erreur

Utilisez `swoole_last_error()` pour obtenir l'étiquette d'erreur actuelle ;

Utilisez `swoole_strerror(int $errno, 9);` pour transformer l'étiquette d'erreur sous-jacente de `Swoole` en message d'erreur textuel ;

```php
echo swoole_strerror(swoole_last_error(), 9) . PHP_EOL;
echo swoole_strerror(SWOOLE_ERROR_MALLOC_FAIL, 9) . PHP_EOL;
```

## Liste des étiquettes d'erreur Linux :id=linux

| Nom C           | Valeur | Description                                  | Signification                   |
| --------------- | ----- | -------------------------------------------- | ------------------------------ |
| Success         | 0     | Success                                      | Succès                          |
| EPERM           | 1     | Operation not permitted                      | Opération non autorisée        |
| ENOENT          | 2     | No such file or directory                    | Aucun tel fichier ou dossier    |
| ESRCH           | 3     | No such process                              | Aucun processus                 |
| EINTR           | 4     | Interrupted system call                      | Appel système interrompu       |
| EIO             | 5     | I/O error                                    | Erreur I/O                       |
| ENXIO           | 6     | No such device or address                    | Aucun tel dispositif ou adresse   |
| E2BIG           | 7     | Arg list too long                            | Liste des arguments trop longue  |
| ENOEXEC         | 8     | Exec format error                            | Erreur de format d'exécution    |
| EBADF           | 9     | Bad file number                              | Numéro de fichier incorrect      |
| ECHILD          | 10    | No child processes                           | Aucun processus fils            |
| EAGAIN          | 11    | Try again                                    | Réessayez plus tard              |
| ENOMEM          | 12    | Out of memory                                | Mémoire épuisée                |
| EACCES          | 13    | Permission denied                            | Autorisation refusée            |
| EFAULT          | 14    | Bad address                                  | Adresse incorrecte              |
| ENOTBLK         | 15    | Block device required                        | Dispositif bloc requis           |
| EBUSY           | 16    | Device or resource busy                      | Dispositif ou ressource occupé  |
| EEXIST          | 17    | File exists                                  | Fichier déjà présent            |
| EXDEV           | 18    | Cross-device link                            | Lien inter-device invalide      |
| ENODEV          | 19    | No such device                               | Dispositif inexistant           |
| ENOTDIR         | 20    | Not a directory                              | Pas un dossier                  |
| EISDIR          | 21    | Is a directory                               | Est un dossier                  |
| EINVAL          | 22    | Invalid argument                             | Argument invalide               |
| ENFILE          | 23    | File table overflow                          | Tableau de fichiers débordant    |
| EMFILE          | 24    | Too many open files                          | Trop de fichiers ouverts        |
| ENOTTY          | 25    | Not a tty device                             | Pas un dispositif TTY           |
| ETXTBSY         | 26    | Text file busy                               | Fichier texte occupé            |
| EFBIG           | 27    | File too large                               | Fichier trop grand              |
| ENOSPC          | 28    | No space left on device                      | Pas d'espace sur le dispositif   |
| ESPIPE          | 29    | Illegal seek                                 | Recherche illégale              |
| EROFS           | 30    | Read-only file system                        | Système de fichiers en lecture seule |
| EMLINK          | 31    | Too many links                               | Trop de liens                   |
| EPIPE           | 32    | Broken pipe                                  | Pipe cassée                     |
| EDOM            | 33    | Math argument out of domain                  | Argument mathématique hors domaine |
| ERANGE          | 34    | Math result not representable                | Résultat mathématique irrépresentable |
| EDEADLK         | 35    | Resource deadlock would occur                | Entropie de ressources bloquée    |
| ENAMETOOLONG    | 36    | Filename too long                            | Nom de fichier trop long         |
| ENOLCK          | 37    | No record locks available                    | Pas de verrous disponibles      |
| ENOSYS          | 38    | Function not implemented                     | Fonction non implémentée        |
| ENOTEMPTY       | 39    | Directory not empty                          | Dossier non vide                 |
| ELOOP           | 40    | Too many symbolic links encountered          | Trop de liens symboliques rencontrés |
| EWOULDBLOCK     | 41    | Same as EAGAIN                               | Comme EAGAIN                    |
| ENOMSG          | 42    | No message of desired type                   | Pas de message de type souhaité |
| EIDRM           | 43    | Identifier removed                           | Identificateur supprimé         |
| ECHRNG          | 44    | Channel number out of range                  | Numéro de canal hors plage       |
| EL2NSYNC        | 45    | Level 2 not synchronized                     | Niveau 2 non synchronisé        |
| EL3HLT          | 46    | Level 3 halted                               | Niveau 3 arrêté                 |
| EL3RST          | 47    | Level 3 reset                                | Niveau 3 réinitialisé            |
| ELNRNG          | 48    | Link number out of range                     | Numéro de lien hors plage       |
| EUNATCH         | 49    | Protocol driver not attached                 | Driver de protocole non attaché  |
| ENOCSI          | 50    | No CSI structure available                   | Pas de structure CSI disponible |
| EL2HLT          | 51    | Level 2 halted                               | Niveau 2 arrêté                 |
| EBADE           | 52    | Invalid exchange                             | Échange invalide               |
| EBADR           | 53    | Invalid request descriptor                   | Description de demande invalide |
| EXFULL          | 54    | Exchange full                                | Échange plein                   |
| ENOANO          | 55    | No anode                                     | Pas d'anode                     |
| EBADRQC         | 56    | Invalid request code                         | Code de demande invalide        |
| EBADSLT         | 57    | Invalid slot                                 | Slot invalide                   |
| EDEADLOCK       | 58    | Same as EDEADLK                              | Comme EDEADLK                  |
| EBFONT          | 59    | Bad font file format                         | Format de police incorrect       |
| ENOSTR          | 60    | Device not a stream                          | Dispositif pas un flux           |
| ENODATA         | 61    | No data available                            | Pas de données disponibles     |
| ETIME           | 62    | Timer expired                                | Compteur à l'heure épuisé       |
| ENOSR           | 63    | Out of streams resources                     | Ressources de flux épuisées      |
| ENONET          | 64    | Machine is not on the network                | Machine hors réseau            |
| ENOPKG          | 65    | Package not installed                        | Paquet non installé             |
| EREMOTE         | 66    | Object is remote                             | Objet distant                   |
| ENOLINK         | 67    | Link has been severed                        | Lien interrompu                 |
| EADV            | 68    | Advertise error                              | Erreur d'annonce               |
| ESRMNT          | 69    | Srmount error                                | Erreur de montage SRM           |
| ECOMM           | 70    | Communication error on send                  | Erreur de communication lors de l'envoi |
| EPROTO          | 71    | Protocol error                               | Erreur de protocole             |
| EMULTIHOP       | 72    | Multihop attempted                           | Tentative de multihop            |
| EDOTDOT         | 73    | RFS specific error                           | Erreur spécifique à RFS         |
| EBADMSG         | 74    | Not a data message                           | Message non daté                |
| EOVERFLOW       | 75    | Value too large for defined data type        | Valeur trop grande pour le type de données défini |
| ENOTUNIQ        | 76    | Name not unique on network                   | Nom non unique sur le réseau   |
| EBADFD          | 77    | File descriptor in bad state                 | Descriptor de fichier en état incorrect |
| EREMCHG         | 78    | Remote address changed                       | Adresse remote modifiée          |
| ELIBACC         | 79    | Cannot access a needed shared library        | Incapacité d'accès à une bibliothèque partagée nécessaire |
| ELIBBAD         | 80    | Accessing a corrupted shared library         | Accès à une bibliothèque partagée corrompue |
| ELIBSCN         | 81    | A .lib section in an .out is corrupted       | Section .lib dans un .out est corrompue |
| ELIBMAX         | 82    | Linking in too many shared libraries         | Enlacement de trop nombreuses bibliothèques partagées |
| ELIBEXEC        | 83    | Cannot exec a shared library directly        | Incapacité à exécuter directement une bibliothèque partagée |
| EILSEQ          | 84    | Illegal byte sequence                        |序列 d'octets illégale           |
| ERESTART        | 85    | Interrupted system call should be restarted  | Appel système interrompu qui doit être redémarré |
| ESTRPIPE        | 86    | Streams pipe error                           | Erreur de pipe de flux           |
| EUSERS          | 87    | Too many users                               | Trop de utilisateurs             |
| ENOTSOCK        | 88    | Socket operation on non-socket               | Opération socket sur un non-socket |
| EDESTADDRREQ    | 89    | Destination address required                | Adresse de destination requise   |
| EMSGSIZE        | 90    | Message too long                             | Message trop long               |
| EPROTOTYPE      | 91    | Protocol wrong type for socket               | Type de protocole incorrect pour socket |
| ENOPROTOOPT     | 92    | Protocol not available                       | Protocole non disponible        |
| EPROTONOSUPPORT | 93    | Protocol not supported                       | Protocole non pris en charge    |
| ESOCKTNOSUPPORT | 94    | Socket type not supported                    | Type de socket non pris en charge |
| EOPNOTSUPP      | 95    | Operation not supported on transport         | Opération non prise en charge sur le transport |
| EPFNOSUPPORT    | 96    | Protocol family not supported                | Famille de protocole non prise en charge |
| EAFNOSUPPORT    | 97    | Address family not supported by protocol     | Famille d'adresse non prise en charge par le protocole |
| EADDRINUSE      | 98    | Address already in use                       | Adresse déjà utilisée           |
| EADDRNOTAVAIL   | 99    | Cannot assign requested address              | Incapacité à assigner l'adresse demandée |
| ENETDOWN        | 100   | Network is down                              | Réseau déconnecté               |
| ENETUNREACH     | 101   | Network is unreachable                       | Réseau inaccessible             |
| ENETRESET       | 102   | Network dropped                              | Connexion réseau abandonnée     |
| ECONNABORTED    | 103   | Software caused connection                   | Connexion abandonnée par le logiciel |
| ECONNRESET      | 104   | Connection reset by                          | Connexion réinitialisée par     |
| ENOBUFS         | 105   | No buffer space available                    | Pas assez d'espace tampon disponible |
| EISCONN         | 106   | Transport endpoint is already connected      | Point de terminaison de transport déjà connecté |
| ENOTCONN        | 107   | Transport endpoint is not connected          | Point de terminaison de transport pas connecté |
| ESHUTDOWN       | 108   | Cannot send after transport endpoint shutdown| Incapacité à envoyer après fermeture du point de terminaison de transport |
| ETOOMANYREFS    | 109   | Too many references: cannot splice           | Trop de références : impossibilité de scinder |
| ETIMEDOUT       | 110   | Connection timed                             | Connexion dépassée                |
| ECONNREFUSED    | 111   | Connection refused                           | Connexion refusée              |
| EHOSTDOWN       | 112   | Host is down                                 | hôte éteint                     |
| EHOSTUNREACH    | 113   | No route to host                             | Pas de route vers l'hôte         |
| EALREADY        | 114   | Operation already                            | Opération déjà en cours         |
| EINPROGRESS     | 115   | Operation now in                             | Opération actuellement en cours |
| ESTALE          | 116   | Stale NFS file handle                        | Handle de fichier NFS obsolète    |
| EUCLEAN         | 117   | Structure needs cleaning                     | Structure nécessitant un nettoyage |
| ENOTNAM         | 118   | Not a XENIX-named                            | Pas un nom XENIX                |
| ENAVAIL         | 119   | No XENIX semaphores                          | Pas de sémaphores XENIX           |
| EISNAM          | 120   | Is a named type file                         | Est un type de fichier nommé      |
| EREMOTEIO       | 121   | Remote I/O error                             | Erreur I/O distant              |
| EDQUOT          | 122   | Quota exceeded                               | Dépassement de quota            |
| ENOMEDIUM       | 123   | No medium found                              | Aucun média trouvé              |
| EMEDIUMTYPE     | 124   | Wrong medium type                            | Type de média incorrect          |
| ECANCELED       | 125   | Operation Canceled                           | Opération annulée               |
| ENOKEY          | 126   | Required key not available                   | Clé requise non disponible      |
| EKEYEXPIRED     | 127   | Key has expired                              | Clé expirée                     |
| EKEYREVOKED     | 128   | Key has been revoked                         | Clé révoquée                   |
| EKEYREJECTED    | 129   | Key was rejected by service                  | Clé refusée par le service      |
| EOWNERDEAD      | 130   | Owner died                                   | propriétaire décédé             |
| ENOTRECOVERABLE | 131   | State not recoverable                        | État irréparable               |
| ERFKILL         | 132   | Operation not possible due to RF-kill        | Opération impossible en raison de RF-kill |
| EHWPOISON       | 133   | Memory page has hardware error               | Page de mémoire avec erreur hardware |
## Liste des codes d'erreur Swoole :id=swoole

| Nom de la constante                           | Valeur | Description                      |
| --------------------------------------------- | ------- | -------------------------------- |
| SWOOLE_ERROR_MALLOC_FAIL                       | 501     | Échec de la malloc                |
| SWOOLE_ERROR_SYSTEM_CALL_FAIL                  | 502     | Échec de la system call            |
| SWOOLE_ERROR_PHP_FATAL_ERROR                   | 503     | Échec fatal PHP                   |
| SWOOLE_ERROR_NAME_TOO_LONG                     | 504     | Nom trop long                     |
| SWOOLE_ERROR_INVALID_PARAMS                    | 505     | Paramètres invalides              |
| SWOOLE_ERROR_QUEUE_FULL                        | 506     | Queue pleine                     |
| SWOOLE_ERROR_OPERATION_NOT_SUPPORT             | 507     | Opération non supportée           |
| SWOOLE_ERROR_PROTOCOL_ERROR                    | 508     | Échec du protocole                |
| SWOOLE_ERROR_WRONG_OPERATION                   | 509     | Opération incorrecte              |
| -                                             |        |                                   |
| SWOOLE_ERROR_FILE_NOT_EXIST                    | 700     | Fichier不存在                     |
| SWOOLE_ERROR_FILE_TOO_LARGE                    | 701     | Fichier trop grand                |
| SWOOLE_ERROR_FILE_EMPTY                        | 702     | Fichier vide                      |
| SWOOLE_ERROR_DNSLOOKUP_DUPLICATE_REQUEST       | 710     | Demande de recherche DNS répétée   |
| SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED          | 711     | Échec de la résolution DNS         |
| SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT         | 712     | Défaillance du timeout de résolution DNS |
| SWOOLE_ERROR_DNSLOOKUP_UNSUPPORTED             | 713     | Recherche DNS non supportée       |
| SWOOLE_ERROR_DNSLOOKUP_NO_SERVER               | 714     | Aucun serveur de recherche DNS      |
| SWOOLE_ERROR_BAD_IPV6_ADDRESS                  | 720     | Adresse IPv6 incorrecte           |
| SWOOLE_ERROR_UNREGISTERED_SIGNAL               | 721     | Signal non enregistré             |
| -                                             |        |                                   |
| SWOOLE_ERROR_EVENT_SOCKET_REMOVED              | 800     | Sockets d'événement retirés        |
| -                                             |        |                                   |
| SWOOLE_ERROR_SESSION_CLOSED_BY_SERVER          | 1001    | Session fermée par le serveur     |
| SWOOLE_ERROR_SESSION_CLOSED_BY_CLIENT          | 1002    | Session fermée par le client      |
| SWOOLE_ERROR_SESSION_CLOSING                   | 1003    | Session en cours de fermeture     |
| SWOOLE_ERROR_SESSION_CLOSED                    | 1004    | Session fermée                   |
| SWOOLE_ERROR_SESSION_NOT_EXIST                 | 1005    | Session inexistante               |
| SWOOLE_ERROR_SESSION_INVALID_ID                | 1006    | ID de session invalide             |
| SWOOLE_ERROR_SESSION_DISCARD_TIMEOUT_DATA      | 1007    | Données de session abandonnées par timeout |
| SWOOLE_ERROR_SESSION_DISCARD_DATA              | 1008    | Données de session abandonnées     |
| SWOOLE_ERROR_OUTPUT_BUFFER_OVERFLOW            | 1009    | Buffer de sortie débordé           |
| SWOOLE_ERROR_OUTPUT_SEND_YIELD                 | 1010    | Yield de sortie bloqué            |
| SWOOLE_ERROR_SSL_NOT_READY                     | 1011    | SSL non prêt                      |
| SWOOLE_ERROR_SSL_CANNOT_USE_SENFILE            | 1012    | SSL ne peut pas utiliser senfile    |
| SWOOLE_ERROR_SSL_EMPTY_PEER_CERTIFICATE        | 1013    | Certificat d'peer vide pour SSL    |
| SWOOLE_ERROR_SSL_VERIFY_FAILED                 | 1014    | Vérification SSL échouée          |
| SWOOLE_ERROR_SSL_BAD_CLIENT                    | 1015    | Client SSL mauvais               |
| SWOOLE_ERROR_SSL_BAD_PROTOCOL                  | 1016    | Protocole SSL mauvais             |
| SWOOLE_ERROR_SSL_RESET                         | 1017    | Réinitialisation SSL              |
| SWOOLE_ERROR_SSL_HANDSHAKE_FAILED              | 1018    | Échec du handshake SSL            |
| -                                             |        |                                   |
| SWOOLE_ERROR_PACKAGE_LENGTH_TOO_LARGE          | 1201    | Longueur du paquet trop grande     |
| SWOOLE_ERROR_PACKAGE_LENGTH_NOT_FOUND          | 1202    | Longueur du paquet non trouvée     |
| SWOOLE_ERROR_DATA_LENGTH_TOO_LARGE             | 1203    | Longueur des données trop grande   |
| -                                             |        |                                   |
| SWOOLE_ERROR_TASK_PACKAGE_TOO_BIG              | 2001    | Paquet de tâche trop grand          |
| SWOOLE_ERROR_TASK_DISPATCH_FAIL                | 2002    | Échec du déploiement de tâche      |
| SWOOLE_ERROR_TASK_TIMEOUT                      | 2003    | Timeout de tâche                  |
| -                                             |        |                                   |
| SWOOLE_ERROR_HTTP2_STREAM_ID_TOO_BIG           | 3001    | ID de flux HTTP2 trop grand        |
| SWOOLE_ERROR_HTTP2_STREAM_NO_HEADER            | 3002    | Flux HTTP2 sans en-tête           |
| SWOOLE_ERROR_HTTP2_STREAM_NOT_FOUND            | 3003    | Flux HTTP2 non trouvé             |
| SWOOLE_ERROR_HTTP2_STREAM_IGNORE               | 3004    | Ignorer le flux HTTP2             |
| SWOOLE_ERROR_HTTP2_SEND_CONTROL_FRAME_FAILED   | 3005    | Échec d'envoi du cadre de contrôle HTTP2 |
| -                                             |        |                                   |
| SWOOLE_ERROR_AIO_BAD_REQUEST                   | 4001    | Demande AIO incorrecte             |
| SWOOLE_ERROR_AIO_CANCELED                      | 4002    | AIO annulé                       |
| SWOOLE_ERROR_AIO_TIMEOUT                       | 4003    | AIO timeout                       |
| -                                             |        |                                   |
| SWOOLE_ERROR_CLIENT_NO_CONNECTION              | 5001    | Client sans connexion             |
| -                                             |        |                                   |
| SWOOLE_ERROR_SOCKET_CLOSED                     | 6001    | Socket fermé                     |
| SWOOLE_ERROR_SOCKET_POLL_TIMEOUT               | 6002    | Défaillance du timeout de sondage du socket |
| -                                             |        |                                   |
| SWOOLE_ERROR_SOCKS5_UNSUPPORT_VERSION          | 7001    | Version socks5 non supportée       |
| SWOOLE_ERROR_SOCKS5_UNSUPPORT_METHOD           | 7002    | Méthode socks5 non supportée       |
| SWOOLE_ERROR_SOCKS5_AUTH_FAILED                | 7003    | Authentification socks5 échouée   |
| SWOOLE_ERROR_SOCKS5_SERVER_ERROR               | 7004    | Échec du serveur socks5            |
| SWOOLE_ERROR_SOCKS5_HANDSHAKE_FAILED           | 7005    | Échec du handshake socks5          |
| -                                             |        |                                   |
| SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_ERROR        | 7101    | Échec du handshake proxy HTTP      |
| SWOOLE_ERROR_HTTP_INVALID_PROTOCOL             | 7102    | Protocole HTTP invalide            |
| SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_FAILED       | 7103    | Échec du handshake proxy HTTP      |
| SWOOLE_ERROR_HTTP_PROXY_BAD_RESPONSE           | 7104    | Réponse proxy HTTP incorrecte      |
| -                                             |        |                                   |
| SWOOLE_ERROR_WEBSOCKET_BAD_CLIENT              | 8501    | Client WebSocket incorrect         |
| SWOOLE_ERROR_WEBSOCKET_BAD_OPCODE              | 8502    | Opération WebSocket incorrecte     |
| SWOOLE_ERROR_WEBSOCKET_UNCONNECTED             | 8503    | WebSocket non connecté            |
| SWOOLE_ERROR_WEBSOCKET_HANDSHAKE_FAILED        | 8504    | Échec du handshake WebSocket       |
| SWOOLE_ERROR_WEBSOCKET_PACK_FAILED             | 8505    | Échec de l'envoi de paquet WebSocket |
| -                                             |        |                                   |
| SWOOLE_ERROR_SERVER_MUST_CREATED_BEFORE_CLIENT | 9001    | Le serveur doit être créé avant le client |
| SWOOLE_ERROR_SERVER_TOO_MANY_SOCKET            | 9002    | Le serveur a trop de sockets        |
| SWOOLE_ERROR_SERVER_WORKER_TERMINATED          | 9003    | Le travailleur du serveur s'est terminé |
| SWOOLE_ERROR_SERVER_INVALID_LISTEN_PORT        | 9004    | Port d'écoute invalide pour le serveur |
| SWOOLE_ERROR_SERVER_TOO_MANY_LISTEN_PORT       | 9005    | Le serveur a trop de ports d'écoute  |
| SWOOLE_ERROR_SERVER_PIPE_BUFFER_FULL           | 9006    | Buffer de pipe du serveur plein     |
| SWOOLE_ERROR_SERVER_NO_IDLE_WORKER             | 9007    | Le serveur n'a pas de travailleur inactif |
| SWOOLE_ERROR_SERVER_ONLY_START_ONE             | 9008    | Le serveur ne peut commencer qu'une seule instance |
| SWOOLE_ERROR_SERVER_SEND_IN_MASTER             | 9009    | Le serveur envoie dans le maître     |
| SWOOLE_ERROR_SERVER_INVALID_REQUEST            | 9010    | Demande du serveur invalide         |
| SWOOLE_ERROR_SERVER_CONNECT_FAIL               | 9011    | Échec de la connexion serveur       |
| SWOOLE_ERROR_SERVER_INVALID_COMMAND            | 9012    | Commande du serveur invalide        |
| SWOOLE_ERROR_SERVER_IS_NOT_REGULAR_FILE        | 9013    | Le serveur n'est pas un fichier régulier |
| -                                             |        |                                   |
| SWOOLE_ERROR_SERVER_WORKER_EXIT_TIMEOUT        | 9101    | Défaillance du timeout de sortie du travailleur du serveur |
| SWOOLE_ERROR_SERVER_WORKER_ABNORMAL_PIPE_DATA  | 9102    | Données de pipe anormales du travailleur du serveur |
| SWOOLE_ERROR_SERVER_WORKER_UNPROCESSED_DATA    | 9103    | Données non traitées par le travailleur du serveur |
| -                                             |        |                                   |
| SWOOLE_ERROR_CO_OUT_OF_COROUTINE               | 10001   | Coroutine hors de coroutine        |
| SWOOLE_ERROR_CO_HAS_BEEN_BOUND                 | 10002   | Coroutine déjà lié                |
| SWOOLE_ERROR_CO_HAS_BEEN_DISCARDED             | 10003   | Coroutine déjà abandonnée          |
| SWOOLE_ERROR_CO_MUTEX_DOUBLE_UNLOCK            | 10004   | Double déverrouillage du mutex de coroutine |
| SWOOLE_ERROR_CO_BLOCK_OBJECT_LOCKED            | 10005   | Objet bloqué par la coroutine      |
| SWOOLE_ERROR_CO_BLOCK_OBJECT_WAITING           | 10006   | Coroutine attendant sur l'objet bloqué |
| SWOOLE_ERROR_CO_YIELD_FAILED                   | 10007   | Échec du yield de coroutine        |
| SWOOLE_ERROR_CO_GETCONTEXT_FAILED              | 10008   | Échec de getcontext de coroutine    |
| SWOOLE_ERROR_CO_SWAPCONTEXT_FAILED             | 10009   | Échec de swapcontext de coroutine   |
| SWOOLE_ERROR_CO_MAKECONTEXT_FAILED             | 10010   | Échec de makecontext de coroutine   |
| SWOOLE_ERROR_CO_IOCPINIT_FAILED                | 10011   | Échec d'initialisation IOCP de coroutine |
| SWOOLE_ERROR_CO_PROTECT_STACK_FAILED           | 10012   | Échec de protection de pile de coroutine |
| SWOOLE_ERROR_CO_STD_THREAD_LINK_ERROR          | 10013   | Échec de lien thread standard de coroutine |
| SWOOLE_ERROR_CO_DISABLED_MULTI_THREAD          | 10014   | Multi-threading désactivé pour la coroutine |
| SWOOLE_ERROR_CO_CANNOT_CANCEL                  | 10015   | Incapacité à annuler la coroutine    |
| SWOOLE_ERROR_CO_NOT_EXISTS                     | 10016   | Coroutine inexistante              |
