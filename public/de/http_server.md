# Http\Server

?> `Http\Server` erbt von [Server](/server/init), sodass alle von `Server` bereitgestellten `APIs` und Konfigurationsmöglichkeiten verwendet werden können, und das Prozessmodell ist ebenfalls konsistent. Bitte beziehen Sie sich auf die [Server](/server/init)-Kapitel.

Die Unterstützung für den eingebauten `HTTP`-Dienst wird durch nur ein paar Zeilen Code ermöglicht, um einen hochkonzentrierten, hochleistungsfähigen, [asynchronen IO](/learn?id=同步io异步io)-orientierten Mehrprozess-`HTTP`-Dienst zu schreiben.

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    $response->end("<h1>Hallo Swoole. #".rand(1000, 9999)."</h1>");
});
$http->start();
```

Mit dem `Apache bench` Werkzeug unter Stress getestet, konnte der `Http\Server` auf einem gewöhnlichen PC mit `Inter Core-I5 4 Kerne + 8G RAM` fast `110.000 QPS` erreichen.

Das übertrifft bei weitem die mit `PHP-FPM`, `Golang` und `Node.js` mitgelieferten `HTTP`-Server. Die Leistung kommt fast einer statischen Dateiverarbeitung mit `Nginx` gleich.

```shell
ab -c 200 -n 200000 -k http://127.0.0.1:9501/
```

* **HTTP2-Protokoll verwenden**

  * Um das `HTTP2`-Protokoll unter `SSL` zu verwenden, muss `openssl` installiert sein, und eine höhere Version von `openssl` muss `TLS1.2`, `ALPN` und `NPN` unterstützen.
  * Bei der Kompilierung muss die Option [--enable-http2](/environment?id=编译选项) verwendet werden, um `HTTP2` zu aktivieren.
  * Ab Swoole5 ist das `HTTP2`-Protokoll standardmäßig aktiviert.

```shell
./configure --enable-openssl --enable-http2
```

Richten Sie die [open_http2_protocol](/http_server?id=open_http2_protocol) des `HTTP`-Servers auf `true` aus.

```php
$server = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$server->set([
    'ssl_cert_file' => $ssl_dir . '/ssl.crt',
    'ssl_key_file' => $ssl_dir . '/ssl.key',
    'open_http2_protocol' => true,
]);
```

* **Nginx + Swoole Konfiguration**

!> Da die Unterstützung des `Http\Server` für das `HTTP`-Protokoll nicht vollständig ist, wird empfohlen, ihn nur als Anwendungsserver zu verwenden, um dynamische Anforderungen zu verarbeiten, und vor der Frontend eine `Nginx` als Proxy hinzuzufügen.

```nginx
server {
    listen 80;
    server_name swoole.test;

    location / {
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        proxy_pass http://127.0.0.1:9501;
    }
}
```

?> Man kann die echte `IP` des Clients durch das Lesen von `$request->header['x-real-ip']` erhalten.


## Methoden


### on()

?> **Ereignishandlers registrieren.**

?> Ähnlich wie bei den [Server-Ereignishandlern](/server/events), jedoch unterscheidet sich dies darin:

  * `Http\Server->on` akzeptiert keine [onConnect](/server/events?id=onconnect)/[onReceive](/server/events?id=onreceive)-Ereignishandler-Einstellungen
  * `Http\Server->on` akzeptiert eine neue Ereignisart `onRequest`, bei der die von einem Client gesendeten Anforderungen im `Request`-Ereignis behandelt werden

```php
$http_server->on('request', function(\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
     $response->end("<h1>hello swoole</h1>");
});
```

Nach Erhalt eines vollständigen HTTP-Antrags wird diese Funktion aufgerufen. Der回调funktions hat zwei Parameter:

* [Swoole\Http\Request](/http_server?id=httpRequest), ein Objekt mit Informationen zum `HTTP`-Anfrage, das `header/get/post/cookie` und andere relevante Informationen enthält
* [Swoole\Http\Response](/http_server?id=httpResponse), ein Objekt für die `HTTP`-Antwort, das `cookie/header/status` und andere `HTTP`-Operationen unterstützt

!> Wenn der [onRequest](/http_server?id=on)-Callbackfunktions zurückkehrt, werden die Objekte `$request` und `$response` von unten zerstört


### start()

?> **HTTP-Server starten**

?> Nach dem Start hört der Server auf der Port auf und empfängt neue `HTTP`-Anforderungen.

```php
Swoole\Http\Server->start();
```


## Swoole\Http\Request

Ein `HTTP`-Anfrageobjekt, das relevante Informationen zu der `HTTP`-Anfrage des Clients保存, einschließlich `GET`, `POST`, `COOKIE`, `Header` usw.

!> Bitte verwenden Sie nicht das `&`-Zeichen, um auf das `Http\Request`-Objekt zu verweisen


### header

?> **Informationen zu den `HTTP`-Anfrageheadern. Typ ist ein Array, alle `keys` sind in Kleinbuchstaben. **

```php
Swoole\Http\Request->header: array
```

* **Beispiel**

```php
echo $request->header['host'];
echo $request->header['accept-language'];
```


### server

?> **Informationen zum `HTTP`-Anfrage-Server. **

?> Entsprechend dem `$_SERVER`-Array in `PHP`. Enthält Methoden der `HTTP`-Anfrage, URL-Pfad, IP-Adresse des Clients usw.

```php
Swoole\Http\Request->server: array
```

Alle `keys` des Arrays sind in Kleinbuchstaben und stimmen mit dem `$_SERVER`-Array in `PHP` überein

* **Beispiel**

```php
echo $request->server['request_time'];
```


Schlüssel | Beschreibung
---|---
query_string | Die `GET`-Parameter der Anfrage, wie: `id=1&cid=2`. Wenn es keine `GET`-Parameter gibt, existiert dieser Eintrag nicht
request_method | Die Anfragemethode, `GET/POST` usw.
request_uri | Die Zugriffsadresse ohne `GET`-Parameter, wie `/favicon.ico`
path_info | Ebenso wie `request_uri`
request_time | `request_time` wird vom `Worker` festgelegt und existiert im [SWOOLE_PROCESS](/learn?id=swoole_process)-Modus unter dem `dispatch`-Prozess, daher kann es zu einer Abweichung von der tatsächlichen Empfangszeit kommen. Dies ist besonders der Fall, wenn die Anfragemenge die Verarbeitungskapazität des Servers übersteigt, und `request_time` kann weit hinter der tatsächlichen Empfangszeit liegen. Die genaue Empfangszeit kann durch die Methode `$server->getClientInfo` mit dem Attribut `last_time` erhalten werden.
request_time_float | Die Zeitstempel, zu dem die Anfrage begonnen hat, in Mikrosekunden, `float` Typ, wie `1576220199.2725`
server_protocol | Die Version des Serverprotokolls, `HTTP` ist: `HTTP/1.0` oder `HTTP/1.1`, `HTTP2` ist: `HTTP/2`
server_port | Der Port, auf dem der Server lauscht
remote_port | Der Port des Clients
remote_addr | Die IP-Adresse des Clients
master_time | Die Zeit der letzten Kommunikation mit dem Master


### get

?> **Die `GET`-Parameter der `HTTP`-Anfrage, ähnlich wie `$_GET` in `PHP`, sind ein Array. **

```php
Swoole\Http\Request->get: array
```

* **Beispiel**

```php
// Zum Beispiel: index.php?hello=123
echo $request->get['hello'];
// Alle GET-Parameter abrufen
var_dump($request->get);
```

* **Hinweis**

!> Um `HASH`-Angriffe zu verhindern, darf die Anzahl der `GET`-Parameter nicht mehr als `128` betragen


### post

?> **Die `POST`-Parameter der `HTTP`-Anfrage, Format ist ein Array**

```php
Swoole\Http\Request->post: array
```

* **Beispiel**

```php
echo $request->post['hello'];
```

* **Hinweis**


!> - Die Größe von `POST` und `Header` darf nicht die im [package_max_length](/server/setting?id=package_max_length) festgelegte Größe überschreiten, sonst wird dies als bösartiger Antrag angesehen  
- Die Anzahl der `POST`-Parameter darf nicht mehr als `128` betragen


### cookie

?> **Die mit der `HTTP`-Anfrage transportierten `COOKIE`-Informationen, Format ist ein Schlüssel-Wert-Array. **

```php
Swoole\Http\Request->cookie: array
```

* **Beispiel**

```php
echo $request->cookie['username'];
```


### files

?> **Upload-Dateiinformationen. **

?> Typ ist ein zweidimensionales Array mit dem Namen des `form` als Schlüssel. Ähnlich wie `$_FILES` in `PHP`. Die maximale Dateigröße darf nicht die im [package_max_length](/server/setting?id=package_max_length) festgelegte Größe überschreiten. Da Swoole beim Parsen des Datagramms Memory verbraucht, steigt der Memory-Verbrauch mit zunehmender Größe des Datagramms. Daher sollten Sie nicht die `Swoole\Http\Server` verwenden, um große Dateian Uploads zu verarbeiten oder Funktionen zur Dateian完整性check oder Fortsetzung von Uploads von Benutzern selbst zu entwerfen.

```php
Swoole\Http\Request->files: array
```

* **Beispiel**

```php
Array
(
    [name] => facepalm.jpg // Der von der Browser hochgeladene Dateiname
    [type] => image/jpeg // MIME-Typ
    [tmp_name] => /tmp/swoole.upfile.n3FmFr // Die temporäre hochgeladene Datei, der Dateiname beginnt mit /tmp/swoole.upfile
    [error] => 0
    [size] => 15476 // Dateigröße
)
```

* **Hinweis**

!> Wenn das `Swoole\Http\Request`-Objekt zerstört wird, werden die hochgeladenen temporären Dateien automatisch gelöscht

### getContent()

!> Swoole版本 >= `v4.5.0` 可用, 在低版本可使用别名`rawContent` (此别名将永久保留, 即向下兼容)

?> **获取原始的`POST`包体。**

?> 用于非`application/x-www-form-urlencoded`格式的HTTP `POST`请求。返回原始`POST`数据，此函数等同于`PHP`的`fopen('php://input')`

```php
Swoole\Http\Request->getContent(): string|false
```

  * **返回值**

    * 执行成功返回报文，如果上下文连接不存在返回`false`

!> 有些情况下服务器不需要解析HTTP `POST`请求参数，通过[http_parse_post](/http_server?id=http_parse_post) 配置，可以关闭`POST`数据解析。


### getData()

?> **获取完整的原始`Http`请求报文，注意`Http2`下无法使用。包括`Http Header`和`Http Body`**

```php
Swoole\Http\Request->getData(): string|false
```

  * **返回值**

    * 执行成功返回报文，如果上下文连接不存在或者在`Http2`模式下返回`false`


### create()

?> **创建一个`Swoole\Http\Request`对象。**

!> Swoole版本 >= `v4.6.0` 可用

```php
Swoole\Http\Request->create(array $options): Swoole\Http\Request
```

  * **参数**

    * **`array $options`**
      * **功能**：可选参数，用于设置 `Request` 对象的配置

| 参数                                              | 默认值 | 说明                                                                |
| ------------------------------------------------- | ------ | ----------------------------------------------------------------- |
| [parse_cookie](/http_server?id=http_parse_cookie) | true   | 设置是否解析`Cookie`                                                |
| [parse_body](/http_server?id=http_parse_post)      | true   | 设置是否解析`Http Body`                                             |
| [parse_files](/http_server?id=http_parse_files)   | true   | 设置上传文件解析开关                                                 |
| enable_compression                                | true，如果服务器不支持压缩报文，默认值为false   | 设置是否启用压缩                                                    |
| compression_level                                 | 1      | 设置压缩级别，范围是 1-9，等级越高压缩后的尺寸越小，但 CPU 消耗更多        |
| upload_tmp_dir                                 | /tmp      | 临时文件存储位置，文件上传用        |

  * **返回值**

    * 返回一个`Swoole\Http\Request`对象

* **示例**
```php
Swoole\Http\Request::create([
    'parse_cookie' => true,
    'parse_body' => true,
    'parse_files' => true,
    'enable_compression' => true,
    'compression_level' => 1,
    'upload_tmp_dir' => '/tmp',
]);
```


### parse()

?> **解析`HTTP`请求数据包，会返回成功解析的数据包长度。**

!> Swoole版本 >= `v4.6.0` 可用

```php
Swoole\Http\Request->parse(string $data): int|false
```

  * **参数**

    * **`string $data`**
      * 要解析的报文

  * **返回值**

    * 解析成功返回解析的报文长度，连接上下文不存在或者上下文已经结束返回`false`


### isCompleted()

?> **获取当前的`HTTP`请求数据包是否已到达结尾。**

!> Swoole版本 >= `v4.6.0` 可用

```php
Swoole\Http\Request->isCompleted(): bool
```

  * **返回值**

    * `true`表示已经是结尾，`false`表示连接上下文已经结束或者未到结尾

* **示例**

```php
use Swoole\Http\Request;

$data = "GET /index.html?hello=world&test=2123 HTTP/1.1\r\n";
$data .= "Host: 127.0.0.1\r\n";
$data .= "Connection: keep-alive\r\n";
$data .= "Pragma: no-cache\r\n";
$data .= "Cache-Control: no-cache\r\n";
$data .= "Upgrade-Insecure-Requests: \r\n";
$data .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36\r\n";
$data .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9\r\n";
$data .= "Accept-Encoding: gzip, deflate, br\r\n";
$data .= "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,ja;q=0.6\r\n";
$data .= "Cookie: env=pretest; phpsessid=fcccs2af8673a2f343a61a96551c8523d79ea; username=hantianfeng\r\n";

/** @var Request $req */
$req = Request::create(['parse_cookie' => false]);
var_dump($req);

var_dump($req->isCompleted());
var_dump($req->parse($data));

var_dump($req->parse("\r\n"));
var_dump($req->isCompleted());

var_dump($req);
// 关闭了解析cookie，所以会是null
var_dump($req->cookie);
```


### getMethod()

?> **获取当前的`HTTP`请求的请求方式。**

!> Swoole版本 >= `v4.6.2` 可用

```php
Swoole\Http\Request->getMethod(): string|false
```
  * **返回值**

    * 成返回大写的请求方式，`false`表示连接上下文不存在

```php
var_dump($request->server['request_method']);
var_dump($request->getMethod());
```


## Swoole\Http\Response

`HTTP`响应对象，通过调用此对象的方法，实现`HTTP`响应发送。

?> 当`Response`对象销毁时，如果未调用[end](/http_server?id=end)发送`HTTP`响应，底层会自动执行`end("")`;

!> 请勿使用`&`符号引用`Http\Response`对象


### header() :id=setheader

?> **设置HTTP响应的Header信息**【别名`setHeader`】

```php
Swoole\Http\Response->header(string $key, string $value, bool $format = true): bool;
```

* **参数** 

  * **`string $key`**
    * **功能**：`HTTP`头的`Key`
    * **默认值**：无
    * **其它值**：无

  * **`string $value`**
    * **功能**：`HTTP`头的`value`
    * **默认值**：无
    * **其它值**：无

  * **`bool $format`**
    * **功能**：是否需要对`Key`进行`HTTP`约定格式化【默认`true`会自动格式化】
    * **默认值**：`true`
    * **其它值**：无

* **返回值** 

  * 设置失败，返回`false`
  * 设置成功，返回`true`
* **注意**

   -`header`设置必须在`end`方法之前
   -`$key`必须完全符合`HTTP`的约定，每个单词首字母大写，不得包含中文，下划线或者其他特殊字符  
   -`$value`必须填写  
   -`$ucwords` 设为 `true`，底层会自动对`$key`进行约定格式化  
   -重复设置相同`$key`的`HTTP`头会覆盖，取最后一次  
   -如果客户端设置了`Accept-Encoding`，那么服务端不能设置`Content-Length`响应, `Swoole`检测到这种情况会忽略`Content-Length`的值，并且抛出一个警告   
   -设置了`Content-Length`响应不能调用`Swoole\Http\Response::write()`，`Swoole`检测到这种情况会忽略`Content-Length`的值，并且抛出一个警告

!> Swoole 版本 >= `v4.6.0`时，支持重复设置相同`$key`的`HTTP`头，并且`$value`支持多种类型，如`array`、`object`、`int`、`float`，底层会进行`toString`转换，并且会移除末尾的空格以及换行。

* **示例**

```php
$response->header('content-type', 'image/jpeg', true);

$response->header('Content-Length', '100002 ');
$response->header('Test-Value', [
    "a\r\n",
    'd5678',
    "e  \n ",
    null,
    5678,
    3.1415926,
]);
$response->header('Foo', new SplFileInfo('bar'));
```
### trailer()

?> **Füge Informationen zu `Header` an das Ende der `HTTP`-Antwort hinzu, nur in `HTTP2` verfügbar, zum Überprüfen der Nachrichtenintegrität, digitaler Signatur usw.**

```php
Swoole\Http\Response->trailer(string $key, string $value): bool;
```

* **Parameter** 

  * **`string $key`**
    * **Funktion**：Schlüssel der `HTTP`-Header
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

  * **`string $value`**
    * **Funktion**：Wert der `HTTP`-Header
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

* **Rückgabewert** 

  * Setup fehlgeschlagen, zurückgeben `false`
  * Setup erfolgreich, zurückgeben `true`

* **Hinweis**

  !> Das wiederholte Einstellen desselben `$key` der `HTTP`-Header wird überschrieben und nimmt den letzten Wert an.

* **Beispiel**

```php
$response->trailer('grpc-status', 0);
$response->trailer('grpc-message', '');
```


### cookie()

?> **Stellt Informationen zu `HTTP`-Cookies in der `HTTP`-Antwort ein. Synonym `setCookie`. Die Parameter dieser Methode entsprechen denen von `PHP`s `setcookie`.**

```php
Swoole\Http\Response->cookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false, string $samesite = '', string $priority = ''): bool;
```

  * **Parameter** 

    * **`string $key`**
      * **Funktion**：Schlüssel des `Cookie`
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`string $value`**
      * **Funktion**：Wert des `Cookie`
      * **Standardwert**：Keine
      * **Andere Werte**：Keine
  
    * **`int $expire`**
      * **Funktion**：Ablaufzeit des `Cookie`
      * **Standardwert**：0, nicht abgelaufen
      * **Andere Werte**：Keine

    * **`string $path`**
      * **Funktion**：Gibt den Serverpfad für das Cookie an.
      * **Standardwert**：/
      * **Andere Werte**：Keine

    * **`string $domain`**
      * **Funktion**：Gibt den Domänenname für das Cookie an.
      * **Standardwert**：''
      * **Andere Werte**：Keine

    * **`bool $secure`**
      * **Funktion**：Gibt an, ob das Cookie über eine sichere HTTPS-Verbindung übertragen werden soll.
      * **Standardwert**：''
      * **Andere Werte**：Keine

    * **`bool $httponly`**
      * **Funktion**：Gibt an, ob JavaScript im Browser den mit HttpOnly-Attribute versehenen Cookie访问ieren darf. `true` bedeutet nicht erlaubt, `false` bedeutet erlaubt.
      * **Standardwert**：false
      * **Andere Werte**：Keine

    * **`string $samesite`**
      * **Funktion**： Beschränkt Drittpartei-Cookies, um Sicherheitsrisiken zu minimieren.可选 values sind `Strict`, `Lax`, `None`.
      * **Standardwert**：''
      * **Andere Werte**：Keine

    * **`string $priority`**
      * **Funktion**：Cookie-Priorität. Wenn die Anzahl der Cookies das Limit überschreitet, werden die mit niedrigerer Priorität zuerst gelöscht.可选 values sind `Low`, `Medium`, `High`.
      * **Standardwert**：''
      * **Andere Werte**：Keine
  
  * **Rückgabewert** 

    * Setup fehlgeschlagen, zurückgeben `false`
    * Setup erfolgreich, zurückgeben `true`

* **Hinweis**

  !> - Das `cookie`-Setup muss vor der [end](/http_server?id=end) Methode erfolgen  
  - Der `$samesite` Parameter wird ab Version `v4.4.6` unterstützt, der `$priority` Parameter ab Version `v4.5.8`  
  - `Swoole`会自动对`$value`进行`urlencode`编码，可以使用`rawCookie()`方法关闭对`$value`的编码处理  
  - `Swoole` erlaubt das Einstellen mehrerer `COOKIE` mit dem gleichen `$key`


### rawCookie()

?> **Stellt Informationen zu `HTTP`-Cookies in der `HTTP`-Antwort ein**

!> Die Parameter von `rawCookie()` entsprechen denen von `cookie()` oben, nur dass keine Kodierung処理 durchgeführt wird


### status()

?> **Sendet einen `Http`-Statuscode. Synonym `setStatusCode()`**

```php
Swoole\Http\Response->status(int $http_status_code, string $reason = ''): bool
```

* **Parameter** 

  * **`int $http_status_code`**
    * **Funktion**：Legt den `HttpCode` fest
    * **Standardwert**：Keine
    * **Andere Werte**：Keine

  * **`string $reason`**
    * **Funktion**：Grund des Statuscodes
    * **Standardwert**：''
    * **Andere Werte**：Keine

  * **Rückgabewert** 

    * Setup fehlgeschlagen, zurückgeben `false`
    * Setup erfolgreich, zurückgeben `true`

* **Hinweis**

  * Wenn nur der erste Parameter `$http_status_code` übergeben wird, muss es ein gültiger `HttpCode` sein, wie `200`, `502`, `301`, `404` usw., sonst wird der Statuscode auf `200` festgelegt
  * Wenn der zweite Parameter `$reason` festgelegt ist, kann `$http_status_code` jeder beliebige Wert sein, einschließlich nicht definierter `HttpCode`, wie `499`
  * Die `status` Methode muss vor der [$response->end()](/http_server?id=end) Methode ausgeführt werden


### gzip()

!> Diese Methode wurde in Version `4.1.0` oder höheren Versionen eingestellt, bitte wechseln Sie zu [http_compression](/http_server?id=http_compression); In neuen Versionen wird der `http_compression` Konfigurationsbereich anstelle der `gzip` Methode verwendet.  
Der Hauptgrund dafür ist, dass die `gzip()` Methode nicht den von der Browser-Client gesendeten `Accept-Encoding` Header überprüft. Wenn der Client die Gzip-Kompression nicht unterstützt und diese zwangsweise verwendet wird, kann dies dazu führen, dass der Client den compressed Content nicht entpacken kann.  
Der neue `http_compression` Konfigurationsbereich entscheidet automatisch, ob eine Kompression durchgeführt wird, basierend auf dem `Accept-Encoding` Header des Clients, und wählt automatisch das beste Komprimierungsalgorithmus aus.

?> **Aktiviert die `Http GZIP` Kompression. Die Kompression kann die Größe des `HTML` Inhalts verringern, den Netzwerkbandbreiteneffektiv sparen und die Antwortzeit verbessern. Die `gzip` Methode muss vor dem Senden von `write/end` Content ausgeführt werden, sonst wird ein Fehler抛出. **
```php
Swoole\Http\Response->gzip(int $level = 1);
```

* **Parameter** 
   
     * **`int $level`**
       * **Funktion**：Kompressionsstufe, je höher der Wert, desto kleiner die Größe nach der Kompression, aber mehr `CPU` Verbrauch.
       * **Standardwert**：1
       * **Andere Werte**：`1-9`

!> Nach dem Aufrufen der `gzip` Methode wird automatisch ein `Http`-Header für die Kompression hinzugefügt, und es sollte in der PHP-Code keine weiteren `Http`-Header für die Kompression festgelegt werden; Bilder im Format `jpg/png/gif` sind bereits comprimiert und müssen nicht erneut komprimiert werden

!> Die `gzip` Funktion ist von der `zlib` Bibliothek abhängig, und bei der编译swoole wird auf dem Boden überprüft, ob die `zlib` Bibliothek auf dem System vorhanden ist. Wenn sie nicht vorhanden ist, ist die `gzip` Methode nicht verfügbar. Sie können die `zlib` Bibliothek mit `yum` oder `apt-get` installieren:

```shell
sudo apt-get install libz-dev
```


### redirect()

?> **Sendet eine `Http`-Umleitung. Das Aufrufen dieser Methode beendet automatisch das Senden und die Antwort.**

```php
Swoole\Http\Response->redirect(string $url, int $http_code = 302): bool
```

  * **Parameter** 
* **参数** 
  * **参数** 
  * **参数** 
  * **参数** 

    * **`string $url`**
      * **Funktion**：Die neue Adresse für die Umleitung, wird als `Location` Header gesendet
      * **Standardwert**：Keine
      * **Andere Werte**：Keine

    * **`int $http_code`**
      * **Funktion**：Statuscode【Standardmäßig `302` für temporäre Umleitung, übergeben Sie `301` für dauerhafte Umleitung】
      * **Standardwert**：`302`
      * **Andere Werte**：Keine

  * **Rückgabewert** 

    * Erfolg beim Aufrufen, zurückgeben `true`, Aufruf fehlgeschlagen oder Kontext der Verbindung nicht vorhanden, zurückgeben `false`

* **Beispiel**

```php
$http = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_BASE);

$http->on('request', function ($req, Swoole\Http\Response $resp) {
    $resp->redirect("http://www.baidu.com/", 301);
});

$http->start();
```
### write()

?> **启用了`Http Chunk`分段向浏览器发送相应内容。**

?> 有关`Http Chunk`的详细信息，请参阅`Http`协议标准文档。

```php
Swoole\Http\Response->write(string $data): bool
```

  * **参数** 

    * **`string $data`**
      * **功能**：要发送的数据内容【最大长度不得超过`2M`，受[buffer_output_size](/server/setting?id=buffer_output_size)配置项控制】
      * **默认值**：无
      * **其他值**：无

  * **返回值** 
  
    * 调用成功时返回`true`，调用失败或连接上下文不存在时返回`false`

* **提示**

  * 使用`write`分段发送数据后，[end](/http_server?id=end)方法将不接受任何参数，调用`end`只是会发送一个长度为`0`的`Chunk`表示数据传输完毕
  * 如果通过Swoole\Http\Response::header()方法设置了`Content-Length`，然后又调用这个方法，`Swoole`会忽略`Content-Length`的设置，并抛出一个警告
  * `Http2`不能使用这个函数，否则会抛出一个警告
  * 如果客户端支持响应压缩，`Swoole\Http\Response::write()`会强制关闭压缩


### sendfile()

?> **将文件发送到浏览器。**

```php
Swoole\Http\Response->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

  * **参数** 

    * **`string $filename`**
      * **功能**：要发送的文件名称【如果文件不存在或没有访问权限，`sendfile`会失败】
      * **默认值**：无
      * **其他值**：无

    * **`int $offset`**
      * **功能**：上传文件的偏移量【可以指定从文件的中间部分开始传输数据。此特性可用于支持断点续传】
      * **默认值**：`0`
      * **其他值**：无

    * **`int $length`**
      * **功能**：发送数据的尺寸
      * **默认值**：文件的尺寸
      * **其他值**：无

  * **返回值** 

      * 调用成功时返回`true`，调用失败或连接上下文不存在时返回`false`

* **提示**

  * 底层无法推断要发送文件的MIME格式，因此需要应用代码指定`Content-Type`
  * 在调用`sendfile`之前不得使用`write`方法发送`Http-Chunk`
  * 在调用`sendfile`之后底层会自动执行`end`
  * `sendfile`不支持`gzip`压缩

* **示例**

```php
$response->header('Content-Type', 'image/jpeg');
$response->sendfile(__DIR__.$request->server['request_uri']);
```


### end()

?> **发送`Http`响应体，并结束请求处理。**

```php
Swoole\Http\Response->end(string $html): bool
```

  * **参数** 
  
    * **`string $html`**
      * **功能**：要发送的内容
      * **默认值**：无
      * **其他值**：无

  * **返回值** 

    * 调用成功时返回`true`，调用失败或连接上下文不存在时返回`false`

* **提示**

  * `end`只能调用一次，如果需要分多次向客户端发送数据，请使用[write](/http_server?id=write)方法
  * 如果客户端开启了[KeepAlive](/coroutine_client/http_client?id=keep_alive)，连接将会保持，服务器会等待下一次请求
  * 如果客户端未开启`KeepAlive`，服务器将会切断连接
  * `end`要发送的内容，由于受到[output_buffer_size](/server/setting?id=buffer_output_size)的限制，默认为`2M`，如果大于这个限制则会响应失败，并抛出如下错误：

!> 解决方法为：使用[sendfile](/http_server?id=sendfile)、[write](/http_server?id=write)或调整[output_buffer_size](/server/setting?id=buffer_output_size)

```bash
WARNING finish (ERRNO 1203): Die Länge der Daten [262144] überschreitet die Größe des Ausgabebuffers [131072], bitte verwenden Sie sendfile, die segmentierte Übertragungsweise oder passen Sie die Größe des Ausgabebuffers an
```


### detach()

?> **分离响应对象。** 使用此方法后，`$response`对象销毁时不会自动[end](/http_server?id=httpresponse)，与 [Http\Response::create](/http_server?id=create) 和 [Server->send](/server/methods?id=send) 配合使用。

```php
Swoole\Http\Response->detach(): bool
```

  * **返回值** 

    * 调用成功时返回`true`，调用失败或连接上下文不存在时返回`false`

* **示例** 

  * **跨进程响应**

  ?> 在某些情况下，需要在 [Task进程](/learn?id=taskworker进程)中对客户端发出响应。这时可以利用`detach`使`$response`对象独立。在 [Task进程](/learn?id=taskworker进程)可以重新构建`$response`，发起`Http`请求响应。 

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->set(['task_worker_num' => 1, 'worker_num' => 1]);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->task(strval($resp->fd));
  });

  $http->on('finish', function () {
      echo "task finish";
  });

  $http->on('task', function ($serv, $task_id, $worker_id, $data) {
      var_dump($data);
      $resp = Swoole\Http\Response::create($data);
      $resp->end("in task");
      echo "async task\n";
  });

  $http->start();
  ```

  * **发送任意内容**

  ?> 在某些特殊的场景下，需要对客户端发送特殊的响应内容。`Http\Response`对象自带的`end`方法无法满足需求，可以使用`detach`分离响应对象，然后自行组装HTTP协议响应数据，并使用`Server->send`发送数据。

  ```php
  $http = new Swoole\Http\Server("0.0.0.0", 9501);

  $http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
      $resp->detach();
      $http->send($resp->fd, "HTTP/1.1 200 OK\r\nServer: server\r\n\r\nHello World\n");
  });

  $http->start();
  ```


### create()

?> **构造新的`Swoole\Http\Response`对象。**

!> 使用此方法前请务必调用`detach`方法将旧的`$response`对象分离，否则可能会造成对同一个请求发送两次响应内容。

```php
Swoole\Http\Response::create(object|array|int $server = -1, int $fd = -1): Swoole\Http\Response
```

  * **参数** 

    * **`int $server`**
      * **功能**：`Swoole\Server`或者`Swoole\Coroutine\Socket`对象，数组（数组只能有两个参数，第一个是`Swoole\Server`对象，第二个是`Swoole\Http\Request`对象），或者文件描述符
      * **默认值**：-1
      * **其他值**：无

    * **`int $fd`**
      * **功能**：文件描述符。如果参数`$server`是`Swoole\Server`对象，`$fd`是必填的
      * **默认值**：-1
      * 
      * **其他值**：无

  * **返回值** 

    * 调用成功返回一个新的`Swoole\Http\Response`对象，调用失败返回`false`

* **示例**

```php
$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function ($req, Swoole\Http\Response $resp) use ($http) {
    $resp->detach();
    // 示例1
    $resp2 = Swoole\Http\Response::create($req->fd);
    // 示例2
    $resp2 = Swoole\Http\Response::create($http, $req->fd);
    // 示例3
    $resp2 = Swoole\Http\Response::create([$http, $req]);
    // 示例4
    $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, IPPROTO_IP);
    $socket->connect('127.0.0.1', 9501)
    $resp2 = Swoole\Http\Response::create($socket);
    $resp2->end("hello world");
});

$http->start();
```
### istWritable()

?> **Prüft, ob das `Swoole\Http\Response`-Objekt beendet (`end`) oder getrennt (`detach`) wurde.**

```php
Swoole\Http\Response->istWritable(): bool
```

  * **Rückgabewert** 

    * Wenn das `Swoole\Http\Response`-Objekt nicht beendet oder nicht getrennt wurde, wird `true` zurückgegeben, sonst `false`.


!> Swoole Version >= `v4.6.0` verfügbar

* **Beispiel**

```php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$http = new Server('0.0.0.0', 9501);

$http->on('request', function (Request $req, Response $resp) {
    var_dump($resp->istWritable()); // true
    $resp->end('hello');
    var_dump($resp->istWritable()); // false
    $resp->setStatusCode(403); // http response is unavailable (maybe it has been ended or detached)
});

$http->start();
```


## Konfigurationsoptionen


### http_parse_cookie

?> **Für das `Swoole\Http\Request`-Objekt konfiguriert, deaktiviert die Parsing von `Cookies` und behält die unbearbeiteten originalen `Cookies`-Informationen in den `header`. Standardmäßig eingeschaltet**

```php
$server->set([
    'http_parse_cookie' => false,
]);
```


### http_parse_post

?> **Für das `Swoole\Http\Request`-Objekt konfiguriert, setzt den Schalter für die Parsing von POST-Messages. Standardmäßig eingeschaltet**

* Wenn auf `true` gesetzt wird, wird der Request-Body mit `Content-Type: application/x-www-form-urlencoded` automatisch in das POST-Array geparsed.
* Wenn auf `false` gesetzt wird, wird die Parsing von POST-Messages deaktiviert.

```php
$server->set([
    'http_parse_post' => false,
]);
```


### http_parse_files

?> **Für das `Swoole\Http\Request`-Objekt konfiguriert, setzt den Schalter für die Parsing von hochgeladenen Dateien. Standardmäßig eingeschaltet**

```php
$server->set([
    'http_parse_files' => false,
]);
```


### http_compression

?> **Für das `Swoole\Http\Response`-Objekt konfiguriert, aktiviert die Kompression. Standardmäßig eingeschaltet.**


!> -`http-chunk` unterstützt keine segmentierte Einzelkompression, wenn die [write](/http_server?id=write)-Methode verwendet wird, wird die Kompression zwangsläufig deaktiviert.  
-`http_compression` ist in Version `v4.1.0` oder höher verfügbar

```php
$server->set([
    'http_compression' => false,
]);
```

Derzeit werden `gzip`, `br` und `deflate` als Kompressionsformate unterstützt. Die底层-Bibliothek wählt automatisch die Kompressionsmethode basierend auf dem in der `Accept-Encoding`-Kopf von dem Browser-Client übermittelten Wert aus (Priorität der Kompressionss算法: `br` > `gzip` > `deflate` ).

**Abhängigkeiten:**

`gzip` und `deflate` sind von der `zlib`-Bibliothek abhängig, und bei der Compilierung von `Swoole` wird die Existenz von `zlib` auf dem System überprüft.

可以使用`yum` oder `apt-get` um die `zlib`-Bibliothek zu installieren:

```shell
sudo apt-get install libz-dev
```

Die `br`-Kompressionsformate sind von der `google`-Bibliothek `brotli` abhängig. Bitte suchen Sie nach einer Anleitung zum Installieren von `brotli` unter Linux, und bei der Compilierung von `Swoole` wird die Existenz von `brotli` auf dem System überprüft.


### http_compression_level / compression_level / http_gzip_level

?> **Kompressionsebenen, für das `Swoole\Http\Response`-Objekt konfiguriert**  

!> `$level` Kompressionsebenen, Bereich von `1-9`, je höher die Ebene, desto kleiner die Größe nach der Kompression, aber mehr CPU-Consum. Standardmäßig ist es `1`, die höchste Ebene ist `9`



### http_compression_min_length / compression_min_length

?> **Legt den Mindestbyte für die Aktivierung der Kompression fest, für das `Swoole\Http\Response`-Objekt konfiguriert, nur wenn der Wert über diesem Wert liegt, wird die Kompression aktiviert. Standardmäßig 20 Byte.**  

!> Swoole Version >= `v4.6.3` verfügbar

```php
$server->set([
    'compression_min_length' => 128,
]);
```


### upload_tmp_dir

?> **Legt den temporären Verzeichnis für das Hochladen von Dateien fest.** Die Maximallänge des Verzeichnisses darf nicht länger als `220` Byte sein**  

```php
$server->set([
    'upload_tmp_dir' => '/data/uploadfiles/',
]);
```


### upload_max_filesize

?> **Legt den Höchstwert für das Hochladen von Dateien fest**  

```php
$server->set([
    'upload_max_filesize' => 5 * 1024,
]);
```


### enable_static_handler

Aktiviert die Funktion zum Behandeln statischer Dateianfragen, muss in Kombination mit `document_root` verwendet werden. Standardmäßig `false`.



### http_autoindex

Aktiviert die `http autoindex`-Funktion. Standardmäßig nicht aktiviert.


### http_index_files

In Kombination mit `http_autoindex` verwendet, um eine Liste der Dateien anzugeben, die indiziert werden sollen.

```php
$server->set([
    'document_root' => '/data/webroot/example.com',
    'enable_static_handler' => true,
    'http_autoindex' => true,
    'http_index_files' => ['indesx.html', 'index.txt'],
]);
```


### http_compression_types / compression_types

?> **Legt die zu komprimierenden Antworttypen fest, für das `Swoole\Http\Response`-Objekt konfiguriert**  

```php
$server->set([
        'http_compression_types' => [
            'text/html',
            'application/json'
        ],
    ]);
```

!> Swoole Version >= `v4.8.12` verfügbar



### static_handler_locations

?> **Legt die Pfade für statische Handler fest. Typ ist ein Array, standardmäßig nicht aktiviert.**  

!> Swoole Version >= `v4.4.0` verfügbar

```php
$server->set([
    'static_handler_locations' => ['/static', '/app/images'],
]);
```

* Ähnlich wie die `location`-Anweisung in `Nginx`, kann eine oder mehrere Pfade als statische Pfade angegeben werden. Nur wenn die `URL` unter den angegebenen Pfaden liegt, wird der statische Dateihandler aktiviert, sonst wird es als dynamische Anfrage betrachtet.
* `location`-Items müssen mit `/` beginnen
* Unterstützt mehrstufige Pfade, wie `/app/images`
* Nachdem `static_handler_locations` aktiviert wurde, wird direkt ein 404-Fehler zurückgegeben, wenn die angeforderte Datei nicht existiert


### open_http2_protocol

?> **Aktiviert die Parsing des `HTTP2`-Protokolls**【Standardwert: `false`】

!> Um `HTTP2` zu aktivieren, muss bei der Compilierung die [--enable-http2](/environment?id=编译选项) Option verwendet werden, ab `Swoole5` ist `HTTP2` standardmäßig compiled.


### document_root

?> **Konfiguriert den Root-Verzeichnis für statische Dateien, in Kombination mit `enable_static_handler` verwendet.**  

!> Diese Funktion ist ziemlich einfach, bitte verwenden Sie sie nicht direkt im öffentlichen Netzwerkumfeld

```php
$server->set([
    'document_root' => '/data/webroot/example.com', // Für Versionen unter `v4.4.0` muss dies ein absoluter Pfad sein
    'enable_static_handler' => true,
]);
```

* Nachdem `document_root` festgelegt und `enable_static_handler` auf `true` gesetzt wurde, wird das底层-System beim Empfang einer `Http`-Anfrage zuerst überprüfen, ob die Datei im `document_root`-Verzeichnis existiert. Wenn sie existiert, wird der Dateiinhalt direkt an den Client gesendet, ohne die [onRequest](/http_server?id=on)-Callback zu aktivieren.
* Beim Verwenden der statischen Dateibehandlung sollten dynamische PHP-Code und statische Dateien isoliert werden, indem statische Dateien in einem bestimmten Verzeichnis abgelegt werden


### max_concurrency

?> **Kann die maximale Anzahl von gleichzeitigen Anfragen für `HTTP1/2`-Dienste einschränken. Wenn die Anzahl der Anfragen die Grenze überschreitet, wird eine `503`-Fehler zurückgegeben. Der Standardwert beträgt 4294967295, was der größte Wert für einen unsignierten Integer ist**  

```php
$server->set([
    'max_concurrency' => 1000,
]);
```


### worker_max_concurrency

?> **Nachdem die einfache Konversion zu Coroutine aktiviert wurde, werden die `worker`-Prozesse kontinuierlich Anfragen entgegennehmen. Um Überlastungen zu vermeiden, können wir die `worker_max_concurrency`-Einstellung verwenden, um die Anzahl der Anfragen zu beschränken, die von den `worker`-Prozessen ausgeführt werden. Wenn die Anzahl der Anfragen diese Grenze überschreitet, werden die überschüssigen Anfragen in einer Warteschlange abgelegt. Der Standardwert beträgt 4294967295, was der größte Wert für einen unsignierten Integer ist. Wenn `worker_max_concurrency` nicht festgelegt ist, aber `max_concurrency` festgelegt ist, wird der底层-System automatisch `worker_max_concurrency` auf den Wert von `max_concurrency` setzen**  

```php
$server->set([
    'worker_max_concurrency' => 1000,
]);
```

!> Swoole Version >= `v5.0.0` verfügbar
### http2_header_table_size

?> Definiert die maximale Größe der `header table` für HTTP/2-Netzwerkverbindungen.

```php
$server->set([
  'http2_header_table_size' => 0x1
])
```

### http2_enable_push

?> Diese Konfiguration dient zum Aktivieren oder Deaktivieren von HTTP/2-Push.

```php
$server->set([
  'http2_enable_push' => 0x2
])
```

### http2_max_concurrent_streams

?> Legt die maximale Anzahl von Multicast-Streams fest, die pro HTTP/2-Netzwerkverbindung akzeptiert werden können.

```php
$server->set([
  'http2_max_concurrent_streams' => 0x3
])
```

### http2_init_window_size

?> Legt die anfängliche Größe des HTTP/2-Flusssteuerungsfensters fest.

```php
$server->set([
  'http2_init_window_size' => 0x4
])
```

### http2_max_frame_size

?> Legt die maximale Größe des Hauptbodys eines einzelnen HTTP/2-Protokollframes fest, der über eine HTTP/2-Netzwerkverbindung gesendet wird.

```php
$server->set([
  'http2_max_frame_size' => 0x5
])
```

### http2_max_header_list_size

?> Legt die maximale Größe der Kopfliste fest, die in einem HTTP/2-Stream für eine Anforderung gesendet werden kann. 

```php
$server->set([
  'http2_max_header_list_size' => 0x6
])
```
