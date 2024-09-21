# 注意事项
- `Markdown List` 末尾不添加句号`。`或分号`;`
- 文档中描述类名时不添加`Swoole\`根命名空间，例如：`Swoole\Coroutine\System`文档中应简写为`Coroutine\System`
- 在特定模块下的文档还可以进一步简写为 `System`，但跨模块时不简写，例如在`Coroutine`目录下，`Coroutine\System`亦可写为`System`，但在`Server`目录下，必须写为`Coroutine\System`
- 在代码示例中需要使用完整类型，不得缺失`Swoole\`根命名空间
- 示例代码确保可执行，仅依赖`swoole`扩展或其他PHP标准库、内置扩展、常用扩展，若额外以来其他非常见的`PECL`或第三方扩展、`Composer`包时需要进行备注