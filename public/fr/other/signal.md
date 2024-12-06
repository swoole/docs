# Liste des signaux Linux


## Tableau de correspondance complet

| Signal    | Valeur | Action par défaut | Signification (raison de l'émission du signal) |
| --------- | ------- | ---------------- | ------------------------------------------- |
| SIGHUP    | 1       | Terminer         | Déconnexion de la terminal ou mort du processus |
| SIGINT    | 2       | Terminer         | Signal d'interruption venant de la souris      |
| SIGQUIT   | 3       | Core             | Signal de départ venant de la souris         |
| SIGILL    | 4       | Core             | Commande illégale                            |
| SIGABRT   | 6       | Core             | Signal d'exception abort                     |
| SIGFPE    | 8       | Core             | Exception flottante                          |
| SIGKILL   | 9       | Terminer         | Kill                                        |
| SIGSEGV   | 11      | Core             | Erreur de segment (référence mémoire invalide) |
| SIGPIPE   | 13      | Terminer         | Pipe endommagée : écriture dans une pipe sans processus lecteur |
| SIGALRM   | 14      | Terminer         | Signal de temps écoulé venant de l'alarme     |
| SIGTERM   | 15      | Terminer         | Terminer                                    |
| SIGUSR1   | 30,10,16 | Terminer         | Signal d'utilisateur personnalisé 1           |
| SIGUSR2   | 31,12,17 | Terminer         | Signal d'utilisateur personnalisé 2           |
| SIGCHLD   | 20,17,18 | Ignorer          | Subprocessus arrêté ou terminé               |
| SIGCONT   | 19,18,25 | Continuer        | Si arrêté, continuer l'exécution              |
| SIGSTOP   | 17,19,23 | Arrêter          | Signal d'arrêt non venant de la terminal      |
| SIGTSTP   | 18,20,24 | Arrêter          | Signal d'arrêt venant de la terminal        |
| SIGTTIN   | 21,21,26 | Arrêter          | Processus en arrière-plan lit la terminal    |
| SIGTTOU   | 22,22,27 | Arrêter          | Processus en arrière-plan écrit sur la terminal |
|           |          |                  |                                             |
| SIGBUS    | 10,7,10  | Core             | Erreur de bus (erreur d'accès mémoire)       |
| SIGPOLL   |          | Terminer         | Événement pollable (Sys V), synonyme de SIGIO  |
| SIGPROF   | 27,27,29 | Terminer         | Timeout pour les statistiques (timer)        |
| SIGSYS    | 12,-,12  | Core             | Appel système illégal (SVr4)                |
| SIGTRAP   | 5       | Core             | Trappe/point de rupture                     |
| SIGURG    | 16,23,21 | Ignorer          | Signal d'urgence (socket)                   |
| SIGVTALRM | 26,26,28 | Terminer         | Alarme de temps virtuel (4.2BSD)             |
| SIGXCPU   | 24,24,30 | Core             | Dépassement de la limite CPU (4.2BSD)        |
| SIGXFSZ   | 25,25,31 | Core             | Dépassement de la limite de taille de fichier (4.2BSD) |
|           |          |                  |                                             |
| SIGIOT    | 6       | Core             | Trappe IOT, synonyme de SIGABRT               |
| SIGEMT    | 7,-,7    |                  | Terminer                                    |
| SIGSTKFLT | -,16,-   | Terminer         | Erreur de pile du coprocesseur (non utilisé)  |
| SIGIO     | 23,29,22 | Terminer         | Opération I/O possible sur le descripteur      |
| SIGCLD    | -,-,18   | Ignorer          | Synonyme de SIGCHLD                         |
| SIGPWR    | 29,30,19 | Terminer         | Électrique défaillance/redémarrage          |
| SIGINFO   | 29,-,-   |                  | Synonyme de SIGPWR                          |
| SIGLOST   | -,-,-    | Terminer         | Perte de verrouillage de fichier            |
| SIGWINCH  | 28,28,20 | Ignorer          | Changement de taille de fenêtre terminal      |
| SIGUNUSED | -,31,-   | Terminer         | Signal inutilisé (sera SIGSYS)              |


## Signaux non fiables

| Nom       | Description                  |
| --------- | --------------------------- |
| SIGHUP    | Déconnexion                 |
| SIGINT    | Symbol de fin de session     |
| SIGQUIT   | Symbol de sortie de session  |
| SIGILL    | Commande hardware illégale   |
| SIGTRAP  |故障 matériel               |
| SIGABRT   | Fin de processus anormal     |
| SIGBUS    | Erreur matériel              |
| SIGFPE    | Exception mathématique       |
| SIGKILL   | Fin de processus             |
| SIGUSR1   | Signal d'utilisateur défini  |
| SIGUSR2   | Signal d'utilisateur défini  |
| SIGSEGV   | Référence mémoire invalide  |
| SIGPIPE   | Écriture dans une pipe sans processus lecteur |
| SIGALRM   | Timeout de l'alarme           |
| SIGTERM   | Fin de processus             |
| SIGCHLD   | Changement d'état du processus fils |
| SIGCONT   | Continuer le processus arrêté |
| SIGSTOP   | Arrêter le processus         |
| SIGTSTP   | Symbol de fin de session     |
| SIGTTIN   | Lecture contrôle tty en arrière-plan |
| SIGTTOU   | Écriture contrôle tty en arrière-plan |
| SIGURG    | Urgence (socket)            |
| SIGXCPU   | Dépassement de la limite CPU |
| SIGXFSZ   | Dépassement de la limite de taille de fichier |
| SIGVTALRM | Alarme de temps virtuel      |
| SIGPROF   | Timeout pour les statistiques |
| SIGWINCH  | Changement de taille de fenêtre terminal |
| SIGIO     | I/O asynchrone              |
| SIGPWR    | Électrique défaillance/redémarrage |
| SIGSYS    | Appel système illégal        |

## Signaux fiables

| Nom       | Utilisation par l'utilisateur |
| ----------- | --------------------------- |
| SIGRTMIN   |                              |
| SIGRTMIN+1 |                              |
| SIGRTMIN+2 |                              |
| SIGRTMIN+3 |                              |
| SIGRTMIN+4 |                              |
| SIGRTMIN+5 |                              |
| SIGRTMIN+6 |                              |
| SIGRTMIN+7 |                              |
| SIGRTMIN+8 |                              |
| SIGRTMIN+9 |                              |
| SIGRTMIN+10 |                             |
| SIGRTMIN+11 |                             |
| SIGRTMIN+12 |                             |
| SIGRTMIN+13 |                             |
| SIGRTMIN+14 |                             |
| SIGRTMIN+15 |                             |
| SIGRTMAX-14 |                             |
| SIGRTMAX-13 |                             |
| SIGRTMAX-12 |                             |
| SIGRTMAX-11 |                             |
| SIGRTMAX-10 |                             |
| SIGRTMAX-9  |                             |
| SIGRTMAX-8  |                             |
| SIGRTMAX-7  |                             |
| SIGRTMAX-6  |                             |
| SIGRTMAX-5  |                             |
| SIGRTMAX-4  |                             |
| SIGRTMAX-3  |                             |
| SIGRTMAX-2  |                             |
| SIGRTMAX-1  |                             |
| SIGRTMAX   |                             |
