# Liste der Linux-Signale


## Vollständige Tabelle

| Signal    | Wert | Standardaktion | Bedeutung (Grund für Signalausgabe) |
| --------- | ---- | -------------- | ----------------------------------- |
| SIGHUP    | 1    | Term           | Verbindung getrennt oder Prozess gestorben |
| SIGINT    | 2    | Term           | Interruptsignal vom keyboard          |
| SIGQUIT   | 3    | Core           | Verlassen-Signal vom keyboard         |
| SIGILL    | 4    | Core           | Unerlaubte Anweisung                |
| SIGABRT   | 6    | Core           | Ausnahmsignal von abort              |
| SIGFPE    | 8    | Core           | Flussfehler                          |
| SIGKILL   | 9    | Term           | Killed                              |
| SIGSEGV   | 11   | Core           | Segmentfehler (ungültige Memory-Referenz) |
| SIGPIPE   | 13   | Term           | Rohr beschädigt: Daten in ein Rohr ohne Lesender Prozess schreiben |
| SIGALRM   | 14   | Term           | Timer-Auslöser-Signal von alarm       |
| SIGTERM   | 15   | Term           | Beenden                              |
| SIGUSR1   | 30,10,16 | Term           | Benutzerdefiniertes Signal 1         |
| SIGUSR2   | 31,12,17 | Term           | Benutzerdefiniertes Signal 2         |
| SIGCHLD   | 20,17,18 | Ign            | Unterprozess beendet oder gestorben   |
| SIGCONT   | 19,18,25 | Cont           | Wenn gestoppt, fortsetzen der Ausführung |
| SIGSTOP   | 17,19,23 | Stop           | Nicht vom Terminal stammendes Stop-Signal |
| SIGTSTP   | 18,20,24 | Stop           | Stop-Signal vom Terminal              |
| SIGTTIN   | 21,21,26 | Stop           | Hintergrundprozess liest Terminal      |
| SIGTTOU   | 22,22,27 | Stop           | Hintergrundprozess schreibt Terminal   |
|           |      |                 |                                      |
| SIGBUS    | 10,7,10  | Core           | Busfehler (Memory-Access-Fehler)    |
| SIGPOLL   |          | Term           | Pollable-Ereignis aufgetreten (Sys V), synonym zu SIGIO |
| SIGPROF   | 27,27,29 | Term           | Timer für Profiler-Diagramme abgelaufen |
| SIGSYS    | 12,-,12  | Core           | Unerlaubte Systeman调用 (SVr4)      |
| SIGTRAP   | 5    | Core           | Trace/Breakpoint-Trap                 |
| SIGURG    | 16,23,21 | Ign            | Emergency-Signal für socket (4.2BSD)  |
| SIGVTALRM | 26,26,28 | Term           | Virtualer Timer abgelaufen (4.2BSD)   |
| SIGXCPU   | 24,24,30 | Core           | Über CPU-Limit (setrlimit) überschritten |
| SIGXFSZ   | 25,25,31 | Core           | Über File-Length-Limit (setrlimit) überschritten |
|           |          |                 |                                      |
| SIGIOT    | 6    | Core           | IOT-Trap, synonym zu SIGABRT         |
| SIGEMT    | 7,-,7    |                | Term                                 |
| SIGSTKFLT | -,16,-   | Term           | Coprozessor-Stapelfehler (nicht verwendet) |
| SIGIO     | 23,29,22 | Term           | I/O-Operationen auf Descriptor möglich |
| SIGCLD    | -,-,18   | Ign            | Synonym zu SIGCHLD                   |
| SIGPWR    | 29,30,19 | Term           | Stromausfall/Wiederstart              |
| SIGINFO   | 29,-,-   |                | Synonym zu SIGPWR                   |
| SIGLOST   | -,-,-    | Term           | Verlust von File-Lock               |
| SIGWINCH  | 28,28,20 | Ign            | Größe des Terminalfensters geändert (4.3BSD, Sun) |
| SIGUNUSED | -,31,-   | Term           | Nicht verwendetes Signal (wird zu SIGSYS) |


## Unzuverlässige Signale

| Name      | Beschreibung                        |
| --------- | ----------------------------------- |
| SIGHUP    | Verbindung getrennt                 |
| SIGINT    | Terminal-Interrupt-Zeichen          |
| SIGQUIT   | Terminal-Ausgang-Zeichen            |
| SIGILL    | Unerlaubte Hardware-Anweisung       |
| SIGTRAP  | Hardware-Fehler                    |
| SIGABRT   | Ausnahmender Abbruch (abort)          |
| SIGBUS    | Hardware-Fehler                    |
| SIGFPE    | Arithmetischer Fehler               |
| SIGKILL   | Beenden                             |
| SIGUSR1   | Benutzerdefiniertes Signal           |
| SIGUSR2   | Benutzerdefiniertes Signal           |
| SIGSEGV   | Unzulässige Memory-Referenz          |
| SIGPIPE   | Schreiben in ein Rohr ohne Lesender Prozess |
| SIGALRM   | Timer-Überlauf (alarm)              |
| SIGTERM   | Beenden                             |
| SIGCHLD   | Veränderung des Zustands von Unterprozessen |
| SIGCONT   | Ausgehaltene Prozesse fortsetzen      |
| SIGSTOP   | Stoppen                             |
| SIGTSTP   | Terminal-Stop-Zeichen               |
| SIGTTIN   | Hintergrundprozess liest Terminal     |
| SIGTTOU   | Hintergrundprozess schreibt Terminal  |
| SIGURG    | Notfall (Socket)                   |
| SIGXCPU   | Über CPU-Limit (setrlimit) überschritten |
| SIGXFSZ   | Über File-Length-Limit (setrlimit) überschritten |
| SIGVTALRM | Virtualer Timer abgelaufen (setitimer) |
| SIGPROF   | Timer für Profiler-Diagramme abgelaufen |
| SIGWINCH  | Größe des Terminalfensters geändert |
| SIGIO     | Asynchrones I/O                     |
| SIGPWR    | Stromausfall/Wiederstart              |
| SIGSYS    | Unerlaubte Systeman调用               |

## Zuverlässige Signale

| Name        | Benutzerdefiniert |
| ----------- | ------------------ |
| SIGRTMIN    |                    |
| SIGRTMIN+1  |                    |
| SIGRTMIN+2  |                    |
| SIGRTMIN+3  |                    |
| SIGRTMIN+4  |                    |
| SIGRTMIN+5  |                    |
| SIGRTMIN+6  |                    |
| SIGRTMIN+7  |                    |
| SIGRTMIN+8  |                    |
| SIGRTMIN+9  |                    |
| SIGRTMIN+10 |                    |
| SIGRTMIN+11 |                    |
| SIGRTMIN+12 |                    |
| SIGRTMIN+13 |                    |
| SIGRTMIN+14 |                    |
| SIGRTMIN+15 |                    |
| SIGRTMAX-14 |                    |
| SIGRTMAX-13 |                    |
| SIGRTMAX-12 |                    |
| SIGRTMAX-11 |                    |
| SIGRTMAX-10 |                    |
| SIGRTMAX-9  |                    |
| SIGRTMAX-8  |                    |
| SIGRTMAX-7  |                    |
| SIGRTMAX-6  |                    |
| SIGRTMAX-5  |                    |
| SIGRTMAX-4  |                    |
| SIGRTMAX-3  |                    |
| SIGRTMAX-2  |                    |
| SIGRTMAX-1  |                    |
| SIGRTMAX    |                    |
