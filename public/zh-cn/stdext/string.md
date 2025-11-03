# String 方法

## 映射关系列表
例如`$str = "hello world"` 使用 `$str->length()` 获取字符串长度，等同于调用 `strlen($str)`。

| 方法名                      | 说明                            | 对应的 PHP 函数                |
|--------------------------|-------------------------------|---------------------------|
| length()                 | 获取字符串长度                       | strlen()                  |
| isEmpty()                | 判断字符串是否为空                     | empty()                   |
| lower()                  | 将字符串转换为小写                     | strtolower()              |
| upper()                  | 将字符串转换为大写                     | strtoupper()              |
| lowerFirst()             | 将字符串的首字母转换为小写                 | lcfirst()                 |
| upperFirst()             | 将字符串的首字母转换为大写                 | ucfirst()                 |
| upperWords()             | 将字符串中每个单词的首字母转换为大写            | ucwords()                 |
| addCSlashes()            | 返回在预定义字符前添加反斜杠的字符串            | addcslashes()             |
| addSlashes()             | 返回在预定义字符前添加反斜杠的字符串            | addslashes()              |
| chunkSplit()             | 将字符串分割成小块并用分隔符连接              | chunk_split()             |
| countChars()             | 统计字符串中每个字符的出现次数               | count_chars()             |
| htmlEntityDecode()       | 将 HTML 实体转换为对应的字符             | html_entity_decode()      |
| htmlEntityEncode()       | 将字符转换为对应的 HTML 实体             | htmlentities()            |
| htmlSpecialCharsEncode() | 将特殊字符转换为 HTML 实体              | htmlspecialchars()        |
| htmlSpecialCharsDecode() | 将 HTML 实体转换为对应的特殊字符           | htmlspecialchars_decode() |
| trim()                   | 去除字符串两端的空白字符                  | trim()                    |
| lTrim()                  | 去除字符串左端的空白字符                  | ltrim()                   |
| rTrim()                  | 去除字符串右端的空白字符                  | rtrim()                   |
| parseStr()               | 将查询字符串解析为变量                   | parse_str()               |
| parseUrl()               | 解析 URL 并返回其组成部分               | parse_url()               |
| contains()               | 检查字符串是否包含指定子字符串               | str_contains()            |
| incr()                   | 将字符串中的数字部分递增                  | str_increment()           |
| decr()                   | 将字符串中的数字部分递减                  | str_decrement()           |
| pad()                    | 使用指定的填充字符串填充字符串到指定长度          | str_pad()                 |
| repeat()                 | 重复字符串指定次数                     | str_repeat()              |
| replace()                | 替换字符串中的指定子字符串                 | str_replace()             |
| iReplace()               | 替换字符串中的指定子字符串（不区分大小写）         | str_ireplace()            |
| shuffle()                | 随机打乱字符串的字符顺序                  | str_shuffle()             |
| split()                  | 将字符串分割为数组                     | explode()                 |
| startsWith()             | 检查字符串是否以指定子字符串开头              | str_starts_with()         |
| endsWith()               | 检查字符串是否以指定子字符串结尾              | str_ends_with()           |
| wordCount()              | 计算字符串中的单词数                    | str_word_count()          |
| iCompare()               | 比较两个字符串（不区分大小写）               | strcasecmp()              |
| compare()                | 比较两个字符串（区分大小写）                | strcmp()                  |
| find()                   | 查找字符串中第一次出现指定子字符串的位置          | strstr()                  |
| iFind()                  | 查找字符串中第一次出现指定子字符串的位置（不区分大小写）  | stristr()                 |
| stripTags()              | 去除字符串中的 HTML 和 PHP 标签         | strip_tags()              |
| stripCSlashes()          | 去除字符串中的反斜杠                    | stripcslashes()           |
| stripSlashes()           | 去除字符串中的反斜杠                    | stripslashes()            |
| iIndexOf()               | 查找字符串中第一次出现指定子字符串的位置（不区分大小写）  | stripos()                 |
| indexOf()                | 查找字符串中第一次出现指定子字符串的位置          | strpos()                  |
| lastIndexOf()            | 查找字符串中最后一次出现指定子字符串的位置         | strrpos()                 |
| iLastIndexOf()           | 查找字符串中最后一次出现指定子字符串的位置（不区分大小写） | strripos()                |
| lastCharIndexOf()        | 查找字符串中最后一次出现指定字符的位置           | strrchr()                 |
| substr()                 | 返回字符串的子字符串                    | substr()                  |
| substrCompare()          | 比较字符串的子字符串                    | substr_compare()          |
| substrCount()            | 计算子字符串在字符串中出现的次数              | substr_count()            |
| substrReplace()          | 用指定字符串替换字符串的子字符串              | substr_replace()          |
| reverse()                | 反转字符串                         | strrev()                  |
| md5()                    | 计算字符串的 MD5 哈希值                | md5()                     |
| sha1()                   | 计算字符串的 SHA1 哈希值               | sha1()                    |
| crc32()                  | 计算字符串的 CRC32 校验值              | crc32()                   |
| hash()                   | 计算字符串的哈希值                     | hash()                    |
| hashCode()               | 计算字符串的哈希码                     | swoole_hashcode()         |
| base64Decode()           | 对字符串进行 Base64 解码              | base64_decode()           |
| base64Encode()           | 对字符串进行 Base64 编码              | base64_encode()           |
| urlDecode()              | 对字符串进行 URL 解码                 | urldecode()               |
| urlEncode()              | 对字符串进行 URL 编码                 | urlencode()               |
| rawUrlEncode()           | 对字符串进行原始 URL 编码               | rawurlencode()            |
| rawUrlDecode()           | 对字符串进行原始 URL 解码               | rawurldecode()            |
| match()                  | 使用正则表达式匹配字符串                  | preg_match()              |
| matchAll()               | 使用正则表达式匹配字符串中的所有子串            | preg_match_all()          |
| isNumeric()              | 检查字符串是否为数字                    | is_numeric()              |

## 多字节字符串方法映射关系
| 方法名                      | 说明                               | 对应的 PHP 函数                |
|--------------------------|----------------------------------|---------------------------|
| mbUpperFirst()           | 将多字节字符串的首字母转换为大写                 | mb_ucfirst()              |
| mbLowerFirst()           | 将多字节字符串的首字母转换为小写                 | mb_lcfirst()              |
| mbTrim()                 | 去除多字节字符串两端的空白字符                  | mb_trim()                 |
| mbSubstrCount()          | 计算多字节字符串中子字符串出现的次数               | mb_substr_count()         |
| mbSubstr()               | 返回多字节字符串的子字符串                    | mb_substr()               |
| mbUpper()                | 将多字节字符串转换为大写                     | mb_strtoupper()           |
| mbLower()                | 将多字节字符串转换为小写                     | mb_strtolower()           |
| mbFind()                 | 查找多字节字符串中第一次出现指定子字符串的位置          | mb_strstr()               |
| mbIndexOf()              | 查找多字节字符串中第一次出现指定子字符串的位置          | mb_strpos()               |
| mbLastIndexOf()          | 查找多字节字符串中最后一次出现指定子字符串的位置         | mb_strrpos()              |
| mbILastIndexOf()         | 查找多字节字符串中最后一次出现指定子字符串的位置（不区分大小写） | mb_strripos()             |
| mbLastCharIndexOf()      | 查找多字节字符串中最后一次出现指定字符的位置           | mb_strrchr()              |
| mbILastCharIndex()       | 查找多字节字符串中最后一次出现指定字符的位置（不区分大小写）   | mb_strrichr()             |
| mbLength()               | 获取多字节字符串的长度                      | mb_strlen()               |
| mbIFind()                | 查找多字节字符串中第一次出现指定子字符串的位置（不区分大小写）  | mb_stristr()              |
| mbIIndexOf()             | 查找多字节字符串中第一次出现指定子字符串的位置（不区分大小写）  | mb_stripos()              |
| mbCut()                  | 返回多字节字符串的子字符串（按字节数）              | mb_strcut()               |
| mbRTrim()                | 去除多字节字符串右端的空白字符                  | mb_rtrim()                |
| mbLTrim()                | 去除多字节字符串左端的空白字符                  | mb_ltrim()                |

## 序列化方法
| 方法名                  | 说明                | 对应的 PHP 函数    |
|----------------------|-------------------|---------------|
| jsonDecode()         | 将 JSON 字符串解码为关联数组 | json_decode() |
| jsonDecodeToObject() | 将 JSON 字符串解码为对象   | json_decode() |
| unmarshal()          | unserialize的别名    | unserialize() |
| unserialize()        | 将变量反序列化为 PHP 变量   | unserialize() |


## 差异说明
- `replace` 和 `iReplace` 与 PHP 函数相比调整了参数顺序，将被操作的字符串调整为第一个参数
- `jsonDecode` 方法始终返回关联数组，而非对象，`jsonDecodeToObject()`方法始终返回`stdClass`对象
- `split` 方法等同于`explode`函数，调整了参数顺序，被操作的字符串作为第一个参数
- `match()`和`matchAll()`方法，直接返回匹配结果数组，而非匹配数量，可使用`count($result[1])`获得数量

```php
$str = 'foobarbaz';
$regex1 = '/(foo)(bar)(baz)/';
$matches = $str->match($regex1, PREG_OFFSET_CAPTURE);

preg_match($regex1, $str, $matches2, PREG_OFFSET_CAPTURE);
Assert::eq($matches, $matches2);
```
