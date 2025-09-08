## [6.0.0] - 2025-09-09

### 🚀 Major Features

- **性能优化策略系统**: 全新的转换策略架构，针对不同使用场景提供最优性能
  - **内存优化策略** (Memory Optimized): 占用 ~400KB 内存，适合 Web 请求和内存受限环境
  - **缓存策略** (Cached): 占用 ~4MB 内存，重复转换性能提升 2-3 倍，适合批处理任务
  - **智能策略** (Smart): 占用 600KB-1.5MB 内存，根据文本复杂度自适应加载
- **ConverterFactory**: 新的工厂模式管理转换器实例和策略选择
- **自动策略选择**: 根据运行环境（Web/CLI/内存限制）自动推荐最佳策略
- **性能基准测试工具**: 内置的策略性能对比和监控工具
- **内存监控**: 支持实时监控内存使用情况和性能指标

### 🔧 API Changes

- 新增 `Pinyin::useMemoryOptimized()` - 切换到内存优化策略
- 新增 `Pinyin::useCached()` - 切换到缓存策略  
- 新增 `Pinyin::useSmart()` - 切换到智能策略
- 新增 `Pinyin::useAutoStrategy()` - 自动选择最佳策略
- 新增 `Pinyin::clearCache()` - 清理所有转换器缓存
- 新增 `ConverterFactory::make($strategy)` - 创建指定策略的转换器
- 新增 `ConverterFactory::recommend()` - 获取推荐策略
- 新增 `ConverterFactory::getStrategiesInfo()` - 获取所有策略信息

### ⚡ Performance Improvements

- **内存使用优化**: 默认内存占用从 ~4MB 降低到 ~400KB
- **转换速度提升**: 缓存策略下重复转换速度提升 2-3 倍
- **智能加载**: 根据文本复杂度动态调整数据加载策略
- **按需加载**: 内存优化策略仅在需要时加载转换数据

### 📊 Benchmark & Monitoring

- 新增 `php benchmark/run.php` - 运行性能基准测试
- 新增 `php benchmark/compare-strategies.php` - 策略对比测试
- 新增基准测试文档 `docs/benchmark-guide.md`
- 支持内存使用情况实时监控

#### 运行基准测试
```bash
# 运行标准基准测试，显示所有方法的性能表现
php benchmark/run.php

# 详细的策略对比测试，对比三种策略的性能差异
php benchmark/compare-strategies.php
```

基准测试会显示：
- 每种策略的执行时间和内存使用情况
- 不同文本长度下的性能表现
- 策略之间的性能对比和推荐场景

### 🔄 Breaking Changes

- **默认策略变更**: 从全缓存改为内存优化策略，降低默认内存占用
- **转换器架构重构**: 引入策略模式，旧的直接实例化转换器方式仍兼容
- **性能特征变化**: 首次转换可能略慢，但内存占用显著降低

### 🔧 Backward Compatibility

- 完全兼容 5.x API，现有代码无需修改即可使用
- `heteronym()` 方法（5.3.3+ 引入）继续保持兼容
- 所有原有的转换方法 (`sentence`, `phrase`, `chars` 等) 保持不变

### 📚 Documentation

- 更新 README.md 增加性能优化策略说明
- 新增性能对比表格和使用建议
- 新增基准测试指南
- 新增性能优化最佳实践

### 🔧 Development Tools

- 新增性能基准测试脚本
- 新增策略对比工具
- 增强命令行工具支持策略选择

### 🛠️ Migration Guide

从 5.x 升级到 6.0 非常简单，所有现有代码都能正常工作：

#### 无需任何修改（推荐）
```php
// 5.x 和 6.x 都能正常工作
Pinyin::sentence('你好世界');
// 6.x 默认使用内存优化策略，内存占用更低
```

#### 保持 5.x 完全相同的性能特征
```php
// 如果你需要与 5.x 完全相同的高性能（高内存占用）
Pinyin::useCached();  // 一次设置，全局生效
Pinyin::sentence('你好世界');
```

#### 使用新的性能优化特性
```php
// 自动选择最佳策略（推荐用于新项目）
Pinyin::useAutoStrategy();

// 或者根据场景手动选择：
// Web 应用（内存受限）
Pinyin::useMemoryOptimized();

// 批处理任务（性能优先）
Pinyin::useCached();

// 通用场景（平衡）
Pinyin::useSmart();
```

#### 性能监控和优化
```php
// 监控内存使用
$initialMemory = memory_get_usage();
$result = Pinyin::sentence('测试文本');
$memoryUsed = memory_get_usage() - $initialMemory;
echo "内存使用: " . round($memoryUsed / 1024, 2) . " KB";

// 批处理完成后清理缓存
Pinyin::useCached();
// ... 批量处理 ...
Pinyin::clearCache();  // 释放内存
```

### 💡 Performance Comparison

| 策略 | 内存占用 | 首次转换 | 重复转换 | 推荐场景 |
|-----|---------|---------|---------|---------|
| Memory Optimized | ~400KB | 中等 | 中等 | Web 请求、内存受限环境 |
| Cached | ~4MB | 慢 | **最快** (2-3x) | 批处理、长时运行进程 |
| Smart | 600KB-1.5MB | 快 | 快 | 通用场景、自动优化 |

基于 1000 次转换的基准测试结果：

| 文本长度 | Memory Optimized | Cached | Smart |
|---------|-----------------|--------|-------|
| 短文本 (<10字) | 1.2ms | 0.5ms | 0.8ms |
| 中等文本 (10-50字) | 3.5ms | 1.2ms | 2.1ms |
| 长文本 (>100字) | 8.7ms | 3.1ms | 5.2ms |

## [5.3.4] - 2025-03-16

### 🚀 Features

- Resolved #211

### ⚙️ Miscellaneous Tasks

- Format
## [5.3.3] - 2024-08-01

### 🚀 Features

- 使用 heteronym 代替 polyphonic 多音字 #184
- 取首字母时，能否保留完整的英文 #199

### 🐛 Bug Fixes

- 修复 琢 的音频顺序 #207
- 补充案例 #207
- Tests
- Tests
## [5.3.2] - 2024-03-19

### 🚀 Features

- 取首字母时，能否保留完整的英文 #199
## [5.3.1] - 2024-03-19

### 🐛 Bug Fixes

- 「仆区」应该读 pú ōu 而非 pú qū #200
- Tests #200
## [5.3.0] - 2023-10-27

### 🚀 Features

- 添加sentenceFull，支持保留其他字符 (#198)
- Full sentence, #198
## [5.2.2] - 2023-09-27

### ⚙️ Miscellaneous Tasks

- Bin
## [5.2.1] - 2023-06-17

### 🐛 Bug Fixes

- Bin
## [5.2.0] - 2023-06-12

### 🚀 Features

- 增加 Pinyin::polyphonesAsArray. fixed #195

### 📚 Documentation

- 更新文档提示
- 更新文档提示
- 更新文档提示
## [5.1.0] - 2023-04-27

### 💼 Other

- 移除错误语法 (#190)
## [5.0.0] - 2022-07-24

### 🐛 Bug Fixes

- 优化符号匹配规则
## [1.0-beta] - 2014-07-16
