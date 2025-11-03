## 映射关系列表
例如`$fp = fopen($file, 'r+')` 使用 `$fp->read(1024)` 从流中读取数据，等同于调用 `fread($fp, 1024)`。

| 方法名        | 说明                | 对应的 PHP 函数  |
|------------|-------------------|-------------| 
| write()    | 向流中写入数据           | fwrite()    |
| read()     | 从流中读取数据           | fread()     |
| close()    | 关闭流               | fclose()    |
| dataSync() | 将流中的数据同步到存储设备     | fdatasync() |
| sync()     | 将流中的数据和元数据同步到存储设备 | fsync()     |
| truncate() | 截断流到指定长度          | ftruncate() |
| stat()     | 获取流的状态信息          | fstat()     |
| seek()     | 在流中定位到指定位置        | fseek()     |
| tell()     | 获取流中的当前位置         | ftell()     |
| lock()     | 对流加锁或解锁           | flock()     |
| eof()      | 检查是否到达流的末尾        | feof()      |
| getChar()  | 从流中获取单个字符         | fgetc()     |
| getLine()  | 从流中获取一行数据         | fgets()     |

## 示例
```php
$fp = fopen('example.txt', 'r+');
$fp->write("Hello, Swoole!\n");
$fp->seek(0);
while (!$fp->eof()) {
    $line = $fp->getLine();
    echo $line;
}
```

