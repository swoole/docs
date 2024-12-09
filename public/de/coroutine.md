# Coroutine <!-- {docsify-ignore-all} -->

Dieser Abschnitt stellt einige grundlegende Konzepte und häufige Fragen zu Cooperativen ein.

Seit Version 4.0 bietet Swoole vollständige Unterstützung für Cooperatives (Coroutine) + Channels (Kanal), was ein neues CSP-Programmiermodell mit sich bringt.

1. Entwickler können auf synchrone Code-Schreibweise achten und dennoch die Effekte und Leistung von [Asynchroner IO](/learn?id=同步io异步io) erreichen, ohne die durch traditionelle asynchrone Rückrufe verursachten diskreten Code-Logiken und das Eintauchen in mehrere Ebenen von Rückrufen, was die Code-Wartung erschwert.
2. Da der Cooperativen-Untergrund verpackt ist, müssen Entwickler im Vergleich zu traditionellen PHP-Schicht-Cooperativen-Frameworks nicht das [yield](https://www.php.net/manual/zh/language.generators.syntax.php)-Schlüsselwort verwenden, um einen cooperativen IO-Betrieb zu markieren. Daher ist es nicht mehr notwendig, die Semantik von `yield` tief zu verstehen und jede Ebene der Aufrufe zu ändern, um `yield` zu verwenden, was die Entwicklungsleistung erheblich steigert.
3. Es werden verschiedene Arten von vollständig ausgestatteten [Cooperativen-Clients](/coroutine_client/init) bereitgestellt, die die meisten Entwicklerbedürfnisse erfüllen können.

## Was ist eine Coprocess?

Eine Coprocess kann einfach als eine Art Thread betrachtet werden, nur dass dieser Thread im Benutzermodus ist, ohne die Beteiligung des Betriebssystems, und die Kosten für das Erstellen, Löschen und Swingen sehr niedrig sind. Im Gegensatz zu Threads kann eine Coprocess keine Multicore-CPU nutzen. Um die Multicore-CPU zu nutzen, muss man sich auf Swooles Multiprocess-Modell verlassen.

## Was ist ein Channel?

Ein Channel kann als eine Art Nachrichtenschlange betrachtet werden, nur dass es sich um eine Nachrichtenschlange zwischen Cooperativen handelt. Mehrere Cooperativen verwenden Push- und Pop-Operationen, um Nachrichten in der Schlange zu produzieren und zu verbrauchen, um Daten zu senden oder zu empfangen und Kommunikation zwischen Cooperativen zu ermöglichen. Es ist zu beachten, dass ein Channel nicht zwischen Prozessen übergreifen kann und nur innerhalb eines Swoole-Prozesses zwischen Cooperativen kommunizieren kann. Das typischste Beispiel ist die Verwendung von [Verbindungspools](/coroutine/conn_pool) und [paralleler Aufruf](/coroutine/multi_call).

## Was ist ein Coprocess-Container?

Cooperativen können mit der Methode `Coroutine::create` oder `go()` erstellt werden (siehe [Abschnitt über Aliase](/other/alias?id=coro-short-name)). Nur innerhalb einer创建的 Coprocess können Coprocess-APIs verwendet werden, und Cooperativen müssen innerhalb eines Coprocess-Containers erstellt werden, siehe [Coprocess-Container](/coroutine/scheduler).

## Coprocess-Planung

Hier wird versucht, das Konzept der Coprocess-Planung so verständlich wie möglich zu erklären. Zunächst einmal kann man sich eine Coprocess einfach als einen Thread vorstellen. Wie wir wissen, dient die Multithreading dazu, die Concurrency des Programms zu erhöhen. Ebenso dient die Multicoproccessing dazu, die Concurrency zu erhöhen.

Jeder Benutzeranfrage wird zu einer Coprocess, und wenn die Anfrage beendet ist, endet auch die Coprocess. Wenn es gleichzeitig tausende von parallelen Anfragen gibt, kann es in einem bestimmten Moment Tausende von Cooperativen innerhalb eines Prozesses geben. Dann sind jedoch die CPU-Ressourcen begrenzt. Welche Coprocess-Code sollte die CPU ausführen?

Der Prozess, der entscheidet, welcher Coprocess-Code von der CPU ausgeführt wird, wird als Coprocess-Planung bezeichnet. Wie ist das Planungsschema von Swoole?

- Zunächst einmal, wenn der Code einer Coprocess das Wort `Co::sleep()` trifft oder eine Netzwerk-IO erzeugt, wie zum Beispiel `MySQL->query()`, ist dies definitiv ein zeitaufwendiger Prozess. Swoole wird dann die Fd dieser MySQL-Verbindung in den [EventLoop](/learn?id=what-is-eventloop) legen.
      
    * Dann wird dieser Coprocess die CPU an andere Cooperativen abgeben: **d.h. `yield` (anhalten)**
    * Wenn die Daten von MySQL zurückkommen, wird der Coprocess fortgesetzt: **d.h. `resume` (wiederherstellen)**


- Zweitens, wenn der Code der Coprocess CPU-intensiv ist, kann man [enable_preemptive_scheduler](/other/config) aktivieren, und Swoole wird den Coprocess zwingen, die CPU freizugeben.


## Vorrang von Eltern- und Kindscopprocessen

Zuerst wird der Kindscoprocess (d.h. das Logik in `go()`) bevorzugt ausgeführt, bis ein Coprocess `yield` trifft (bei Co::sleep()), dann wird zur [Coprocess-Planung](/coroutine?id=coprocess-scheduling) auf den Elterncoprocess gewechselt.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

echo "main start\n";
run(function () {
    echo "coro " . Coroutine::getcid() . " start\n";
    Coroutine::create(function () {
        echo "coro " . Coroutine::getcid() . " start\n";
        Coroutine::sleep(.2);
        echo "coro " . Coroutine::getcid() . " end\n";
    });
    echo "coro " . Coroutine::getcid() . " does not wait for child coroutine\n";
    Coroutine::sleep(.1);
    echo "coro " . Coroutine::getcid() . " end\n";
});
echo "end\n";

/*
main start
coro 1 start
coro 2 start
coro 1 does not wait for child coroutine
coro 1 end
coro 2 end
end
*/
```
  

## Hinweise

Bevor Sie mit dem Swoole-Programmieren beginnen sollten Sie auf folgende Punkte achten:


### globale Variablen

Cooperativen führen dazu, dass der ursprüngliche asynchrone Logik synchronisiert wird, aber der Wechsel zwischen Cooperativen geschieht implizit. Daher kann man nicht garantieren, dass globale Variablen und `static` Variablen konsistent sind, bevor und nach dem Wechsel zwischen Cooperativen.

Unter PHP-FPM können Sie über globale Variablen Anfrageparameter, Serverparameter usw. abrufen. In Swoole können Sie **nicht** über `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER` und andere mit `_` beginnende Variablen irgendwelche Attributparameter abrufen.

Man kann [context](/coroutine/coroutine?id=getcontext) verwenden, um Coprocess-IDs zur Trennung zu nutzen und globale Variablen zu isolieren.

### Mehrere Cooperativen teilen sich eine TCP-Verbindung

[Referenz](/question/use?id=client-has-already-been-bound-to-another-coroutine)
