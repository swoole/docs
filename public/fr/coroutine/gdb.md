# Débogage des coroutines

Lorsque vous utilisez les coroutines Swoole, vous pouvez utiliser les méthodes suivantes pour effectuer le débogage

## Débogage avec GDB


### Entrer dans gdb <!-- {docsify-ignore} -->

```shell
gdb php test.php
```


### gdbinit <!-- {docsify-ignore} -->

```shell
(gdb) source /path/to/swoole-src/gdbinit
```


### Établir un point d'arrêt <!-- {docsify-ignore} -->

Par exemple, pour la fonction `co::sleep`

```shell
(gdb) b zim_swoole_coroutine_util_sleep
```


### Afficher toutes les coroutines et états du processus actuel <!-- {docsify-ignore} -->

```shell
(gdb) co_list 
coroutine 1 SW_CORO_YIELD
coroutine 2 SW_CORO_RUNNING
```


### Afficher l'appel de pile de la coroutine en cours d'exécution <!-- {docsify-ignore} -->

```shell
(gdb) co_bt 
coroutine cid:[2]
[0x7ffff148a100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff148a0a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:7 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10
```


### Afficher l'appel de pile de la coroutine spécifiée par son id <!-- {docsify-ignore} -->

``` shell
(gdb) co_bt 1
[0x7ffff1487100] Swoole\Coroutine->sleep(0.500000) [internal function]
[0x7ffff14870a0] {closure}() /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:3 
[0x7ffff141e0c0] go(object[0x7ffff141e110]) [internal function]
[0x7ffff141e030] (main) /home/shiguangqi/php/swoole-src/examples/coroutine/exception/test.php:10 
```


### Afficher l'état des coroutines globales <!-- {docsify-ignore} -->

```shell
(gdb) co_status 
	 stack_size: 2097152
	 call_stack_size: 1
	 active: 1
	 coro_num: 2
	 max_coro_num: 3000
	 peak_coro_num: 2
```

## Débogage du code PHP

Itérer à travers toutes les coroutines du processus actuel et afficher l'appel de pile.

```php
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> Nécessite la version `4.1.0` ou supérieure

* Retourne un itérateur, qui peut être parcouru avec `foreach`, ou converti en tableau avec `iterator_to_array`

```php
use Swoole\Coroutine;
$coros = Coroutine::listCoroutines();
foreach($coros as $cid)
{
	var_dump(Coroutine::getBackTrace($cid));
}
```
