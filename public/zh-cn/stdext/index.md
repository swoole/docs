# PHP 标准库扩展（`stdext`）

从`6.1.0`版本开始`Swoole`增加了一个`stdext`模块，用于扩展`PHP`标准库，对`PHP`官方迟迟未能改进，但社区又迫切希望改进的基础语法，进行一些扩展和改进。

## 安装
编译时需添加`--enable-swoole-stdext`选项启用该模块。

> 该模块同时支持`php-fpm`和`cli`模式

## 数组和字符串作为对象

在其他编程语言中，数组和字符串均是作为一个对象存在的，例如`C++`、`Java`、`JS`、`Python`、`Golang`等，数组和字符串均有内置的方法进行各种操作。而`PHP`中需要使用一个函数来对数组和字符串进行操作，各种方式更接近上古时代的`C`语言。

扩展语法将`string`和`array`作为一种内置的`final class`，可以直接使用对象方法来进行操作。
基础类型的内置方法实际上仍然是调用`PHP`标准库实现的，内置方法与`PHP`的`str_`或`array_`系列函数是一一对应关系，
例如`$text->replace()`对应的函数是`str_replace`。
底层仅调整了函数名称，少量方法调整了参数顺序，还有几个方法调整了参数和返回值类型。详情参考字符串方法列表、数组方法列表、Stream 方法列表。

### 数组方法

```php
$array = [1, 2, 3, 99];

$array->contains(99);
$index = $array->indexOf(3);
$string = $array->join(';');
```

### 字符串对象
```php
$str = "hello world!";

$true = $str->startsWith('hello');
$true = $str->endsWith('!');
$array = $str->split(' ');
```

## 强类型数组
可强制限定数组的`key`和`value`类型，解决使用数组导致的类型黑洞问题。语法类似于`C++`的泛型写法，允许多层嵌套。

### List 语法

```php
$array = typed_array('<int>', [1, 2, 3]);
$array = typed_array('<string>', ['hello', 'world'];
$array = typed_array('<TestObject>', [new TestObject];
$array = typed_array('<<stdClass>>');
```

### Map 语法
```php
$array = typed_array('<int, string>', [1 => 'a', 2 => 'b', 3 => 'c'];
$array = typed_array('<string, TestObject>', [ 'first' => new TestObject];
$array = typed_array('<string, <stdClass>>');
```


