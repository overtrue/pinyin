<?php

require __DIR__ . '/../vendor/autoload.php';

use Overtrue\Pinyin\Pinyin;
use Overtrue\Pinyin\ConverterFactory;

// 示例文本
$shortText = "你好世界";
$mediumText = "带着希望去旅行，比到达终点更美好";
$longText = "中华人民共和国是世界上人口最多的国家，拥有悠久的历史和灿烂的文化。";

echo "=== Pinyin Converter 策略对比 ===\n\n";

// 1. 内存优化策略（默认）
echo "1. 内存优化策略（适合 Web 请求）\n";
echo "----------------------------------------\n";
Pinyin::useMemoryOptimized();
$start = microtime(true);
$result = Pinyin::sentence($mediumText);
$time = round((microtime(true) - $start) * 1000, 2);
echo "结果: " . $result->join(' ') . "\n";
echo "耗时: {$time}ms\n";
echo "内存特性: 峰值 ~400KB，每次加载一个段\n\n";

// 2. 缓存策略
echo "2. 缓存策略（适合批处理）\n";
echo "----------------------------------------\n";
Pinyin::useCached();
$start = microtime(true);
$result = Pinyin::sentence($mediumText);
$time1 = round((microtime(true) - $start) * 1000, 2);

// 第二次调用会更快
$start = microtime(true);
$result = Pinyin::sentence($shortText);
$time2 = round((microtime(true) - $start) * 1000, 2);

echo "首次调用: {$time1}ms\n";
echo "第二次调用: {$time2}ms （缓存生效）\n";
echo "内存特性: 峰值 ~4MB，全部缓存\n\n";

// 3. 智能策略
echo "3. 智能策略（自动优化）\n";
echo "----------------------------------------\n";
Pinyin::useSmart();

// 短文本
$start = microtime(true);
$result = Pinyin::sentence($shortText);
$time = round((microtime(true) - $start) * 1000, 2);
echo "短文本 ({$shortText}): {$time}ms\n";
echo "  -> " . $result->join(' ') . "\n";

// 长文本
$start = microtime(true);
$result = Pinyin::sentence($longText);
$time = round((microtime(true) - $start) * 1000, 2);
echo "长文本: {$time}ms\n";
echo "内存特性: 600KB-1.5MB，根据文本长度调整\n\n";

// 4. 自动选择策略
echo "4. 自动选择策略（根据环境）\n";
echo "----------------------------------------\n";
Pinyin::useAutoStrategy();
$recommended = ConverterFactory::recommend();
echo "当前环境推荐: {$recommended}\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "内存限制: " . ini_get('memory_limit') . "\n\n";

// 5. 直接使用特定策略的 Converter
echo "5. 直接使用 Converter（更灵活）\n";
echo "----------------------------------------\n";

// 批处理场景示例
$converter = ConverterFactory::make(ConverterFactory::CACHED);
$texts = [$shortText, $mediumText, $longText];
$totalTime = 0;

foreach ($texts as $i => $text) {
    $start = microtime(true);
    $result = $converter->convert($text);
    $time = round((microtime(true) - $start) * 1000, 2);
    $totalTime += $time;
    echo "文本 " . ($i + 1) . ": {$time}ms\n";
}
echo "总耗时: {$totalTime}ms\n\n";

// 6. 获取策略信息
echo "6. 可用策略信息\n";
echo "----------------------------------------\n";
$strategies = ConverterFactory::getStrategiesInfo();
foreach ($strategies as $key => $info) {
    echo "策略: {$key}\n";
    echo "  名称: {$info['name']}\n";
    echo "  内存: {$info['memory']}\n";
    echo "  速度: {$info['speed']}\n";
    echo "  场景: {$info['use_case']}\n\n";
}

// 7. 内存使用对比
echo "7. 内存使用对比\n";
echo "----------------------------------------\n";

$strategies = [
    ConverterFactory::MEMORY_OPTIMIZED => '内存优化',
    ConverterFactory::CACHED => '全缓存',
    ConverterFactory::SMART => '智能',
];

foreach ($strategies as $strategy => $name) {
    $converter = ConverterFactory::make($strategy);
    $converter->convert($mediumText); // 触发加载
    $memoryInfo = $converter->getMemoryUsage();
    
    echo "{$name}策略:\n";
    foreach ($memoryInfo as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
    echo "\n";
}