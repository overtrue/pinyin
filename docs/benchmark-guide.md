# 基准测试指南

## 运行基准测试

```bash
php benchmark/run.php
```

## 输出说明

### Strategy Comparison 表格

这个表格对比了三种转换策略的性能：

```
Method          Memory    Cached    Smart    Fastest   Speedup
sentence        20.5 ms   8.2 ms    12.3 ms  Cached    2.5x
fullSentence    18.3 ms   7.5 ms    11.2 ms  Cached    2.4x
...
────────────────────────────────────────────────
TOTAL           190.26ms  119.33ms  194.38ms Cached    1.6x
```

#### 列说明：

- **Method**: 测试的方法名称
- **Memory**: 内存优化策略的执行时间
- **Cached**: 缓存策略的执行时间  
- **Smart**: 智能策略的执行时间
- **Fastest**: 该方法最快的策略名称（用颜色高亮）
- **Speedup**: 最慢与最快之间的速度差异倍数

#### TOTAL 行：

- 使用分隔线与数据行区分
- 显示所有方法的总执行时间
- 最快的策略会用对应颜色高亮显示
- Speedup 显示总体的加速比

### Performance Summary

性能总结部分提供了策略之间的直观对比：

```
📊 Performance Summary:
• Cached strategy is 1.59x faster than Memory Optimized
• Smart strategy is 0.98x faster than Memory, 1.63x slower than Cached
```

这让您可以快速了解：
- Cached 策略比 Memory Optimized 快多少倍
- Smart 策略相对于其他两种策略的性能表现

### Memory Usage 表格

显示每种策略的内存使用情况：

```
Strategy           Peak Memory    Description
Memory Optimized   2.5 MB        Minimal memory, loads on demand
Cached            15.8 MB        All data cached, fastest repeated access
Smart              8.2 MB        Adaptive loading based on text complexity
```

## 如何解读结果

1. **选择合适的策略**：
   - 如果内存有限（如 Web 请求）：选择 Memory Optimized
   - 如果需要批量处理大量文本：选择 Cached
   - 如果需要平衡性能和内存：选择 Smart

2. **Speedup 值的含义**：
   - 1.5x = 快 50%
   - 2.0x = 快 1 倍（速度是原来的 2 倍）
   - 3.0x = 快 2 倍（速度是原来的 3 倍）

3. **TOTAL 行的重要性**：
   - 这是所有方法执行的总时间
   - 最能反映实际使用场景的整体性能
   - 用于评估策略的整体效果