# Thread Management

## Thread::join()

Wait for the child thread to exit. If the child thread is still running, `join()` will block.

```php
$thread = Thread::exec(__FILE__, $i);
$thread->join();
```

## Thread::joinable()

Check if the child thread has exited.

### Return Value
- `true` indicates the child thread has exited, calling `join()` at this point will not block
- `false` indicates it has not exited

```php
$thread = Thread::exec(__FILE__, $i);
var_dump($thread->joinable());
```

## Thread::detach()

Detach the child thread from the control of the parent thread, allowing it to exit without needing to call `join()` to wait for the thread to exit and reclaim resources.

```php
$thread = Thread::exec(__FILE__, $i);
$thread->detach();
unset($thread);
```

## Thread::getId()

Static method to get the `ID` of the current thread. It is called within a child thread.

```php
var_dump(Thread::getId());
```

## Thread::getArguments()

Static method to get the arguments of the current thread. It is called within a child thread and is passed by the parent thread with `Thread::exec()`.

```php
var_dump(Thread::getArguments());
```

## Thread::$id

Use this object property to get the `ID` of the child thread.

```php
$thread = Thread::exec(__FILE__, $i);
var_dump($thread->id);
```
