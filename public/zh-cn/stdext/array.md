# Array 方法

## 映射关系列表
例如`$array = [1, 2, 3]` 使用 `$array->count()` 获取数组长度，等同于调用 `count($array)`。

| 方法名              | 说明                     | 对应的 PHP 函数              |
|------------------|------------------------|-------------------------| 
| all()            | 检查数组中的所有元素是否都满足回调函数的条件 | array_all()             |
| any()            | 检查数组中是否有任意元素满足回调函数的条件  | array_any()             |
| changeKeyCase()  | 将数组的键名转换为指定的大小写        | array_change_key_case() |
| chunk()          | 将数组分割成多个小块             | array_chunk()           |
| column()         | 返回数组中指定列的值             | array_column()          |
| countValues()    | 统计数组中每个值的出现次数          | array_count_values()    |
| diff()           | 计算数组的差集                | array_diff()            |
| diffAssoc()      | 计算数组的关联差集              | array_diff_assoc()      |
| diffKey()        | 计算数组的键名差集              | array_diff_key()        |
| filter()         | 使用回调函数过滤数组元素           | array_filter()          |
| find()           | 查找数组中第一个满足回调函数条件的元素    | array_find()            |
| flip()           | 交换数组的键和值               | array_flip()            |
| intersect()      | 计算数组的交集                | array_intersect()       |
| intersectAssoc() | 计算数组的关联交集              | array_intersect_assoc() |
| isList()         | 检查数组是否为列表数组            | array_is_list()         |
| keyExists()      | 检查数组中是否存在指定的键名         | array_key_exists()      |
| keyFirst()       | 获取数组中的第一个键名            | array_key_first()       |
| keyLast()        | 获取数组中的最后一个键名           | array_key_last()        |
| keys()           | 返回数组中的所有键名             | array_keys()            |
| map()            | 使用回调函数对数组的每个元素进行处理     | array_map()             |
| pad()            | 使用指定的值填充数组到指定长度        | array_pad()             |
| product()        | 计算数组中所有值的乘积            | array_product()         |
| rand()           | 从数组中随机取出一个或多个元素        | array_rand()            |
| reduce()         | 使用回调函数将数组简化为单一值        | array_reduce()          |
| replace()        | 使用一个或多个数组替换数组中的元素      | array_replace()         |
| reverse()        | 反转数组中的元素顺序             | array_reverse()         |
| search()         | 在数组中搜索指定的值并返回对应的键名     | array_search()          |
| slice()          | 从数组中提取一段子数组            | array_slice()           |
| sum()            | 计算数组中所有值的和             | array_sum()             |
| unique()         | 移除数组中的重复值              | array_unique()          |
| values()         | 返回数组中的所有值              | array_values()          |
| count()          | 获取数组中的元素个数             | count()                 |
| merge()          | 合并一个或多个数组              | array_merge()           |
| contains()       | 检查数组中是否包含指定的值          | in_array()              |
| join()           | 将数组元素连接成一个字符串          | implode()               |
| isEmpty()        | 检查数组是否为空               | empty()                 |

## 数组写操作方法
| 方法名           | 说明                        | 对应的 PHP 函数      |
|---------------|---------------------------|-----------------| 
| sort()        | 对数组进行排序                   | sort()          |
| pop()         | 弹出数组的最后一个元素               | array_pop()     |
| push()        | 向数组末尾添加一个或多个元素            | array_push()    |
| shift()       | 弹出数组的第一个元素                | array_shift()   |
| unshift()     | 向数组开头添加一个或多个元素            | array_unshift() |
| splice()      | 从数组中移除指定位置的元素并可插入新元素      | array_splice()  | 
| walk()        | 使用回调函数对数组的每个元素进行处理        | array_walk()    |
| replaceStr()  | 替换数组中字符串元素的指定子字符串         | str_replace()   |
| iReplaceStr() | 替换数组中字符串元素的指定子字符串（不区分大小写） | str_ireplace()  |

请注意由于`PHP`数组写操作函数，第一个参数类型为引用，无法直接对数组使用上述方法。需要先转为引用变量，再调用。

```php
$array = [ 'apple', 'banana', 'cherry' ];
$ref = &$array;
$ref->push('orange');
var_dump($ref， $array); // 两者均为 [ 'apple', 'banana', 'cherry', 'orange' ]
```

## 数组序列化方法
| 方法名          | 说明                | 对应的 PHP 函数    |
|--------------|-------------------|---------------| 
| serialize()  | 将数组序列化为字符串        | serialize()   |
| marshal()    | 将数组序列化为字符串（别名）    | serialize()   |
| jsonEncode() | 将数组编码为 `JSON` 字符串 | json_encode() |


## 差异说明
- `keyExists`、`map`、`replaceStr` 和 `iReplaceStr` 调整了 PHP 函数的参数顺序，使被操作数组为第一个参数
- `join` 方法名称替代了 PHP 函数 `implode`
- `contains` 方法名称替代了 PHP 函数 `in_array`
