<?php

require __DIR__ . '/../vendor/autoload.php';

use Overtrue\Pinyin\Pinyin;
use Overtrue\Pinyin\ConverterFactory;
use Overtrue\Pinyin\Converters\CachedConverter;
use Overtrue\Pinyin\Converters\SmartConverter;

use function Termwind\{render};

$totalStart = microtime(true);
$text = file_get_contents(__DIR__ . '/input.txt');
$textLength = mb_strlen($text);

// ========== 原有的单策略测试 ==========
$html = [];
$methods = ['sentence', 'fullSentence', 'name', 'passportName', 'phrase', 'permalink', 'polyphones', 'chars', 'abbr', 'nameAbbr'];

// 使用默认策略（内存优化）运行原有测试
foreach ($methods as $method) {
    $start = microtime(true);
    $result = call_user_func(Pinyin::class . '::' . $method, $text);
    $usage = round(microtime(true) - $start, 5) * 1000;
    $sample = mb_substr(is_array($result) ? implode(' ', $result) : (string) $result, 0, 30);

    $html[] = "<tr>
                <td><span class=\"text-teal-500\">{$method}</span></td>
                <td><span class=\"text-green-500\">{$usage} ms</span></td>
                <td>{$sample}...</td>
               </tr>
        ";
}
$defaultTotalUsage = round(microtime(true) - $totalStart, 5) * 1000;
$htmlOriginal = implode("\n", $html);

// ========== 新增的多策略对比 ==========
$strategies = [
    'memory' => [
        'name' => 'Memory Optimized',
        'short_name' => 'Memory',
        'setup' => function () {
            Pinyin::useMemoryOptimized();
        },
        'color' => 'text-blue-500',
    ],
    'cached' => [
        'name' => 'Cached',
        'short_name' => 'Cached',
        'setup' => function () {
            Pinyin::useCached();
        },
        'color' => 'text-green-500',
    ],
    'smart' => [
        'name' => 'Smart',
        'short_name' => 'Smart',
        'setup' => function () {
            Pinyin::useSmart();
        },
        'color' => 'text-yellow-500',
    ],
];

$results = [];

// 测试每个策略
foreach ($strategies as $strategyKey => $strategy) {
    $strategy['setup']();
    $strategyStart = microtime(true);

    foreach ($methods as $method) {
        $start = microtime(true);
        $result = call_user_func(Pinyin::class . '::' . $method, $text);
        $usage = round(microtime(true) - $start, 5) * 1000;

        $results[$strategyKey][$method] = [
            'time' => $usage,
        ];
    }

    $results[$strategyKey]['total'] = round(microtime(true) - $strategyStart, 5) * 1000;
}

// 清理缓存
CachedConverter::clearCache();
SmartConverter::clearCache();

// 生成策略对比表格
$comparisonHtml = [];

// 标题行
$comparisonHtml[] = '<tr>';
$comparisonHtml[] = '<th>Method</th>';
foreach ($strategies as $strategy) {
    $comparisonHtml[] = '<th class="text-center">' . $strategy['short_name'] . '</th>';
}
$comparisonHtml[] = '<th class="text-center">Fastest</th>';
$comparisonHtml[] = '<th class="text-center">Speedup</th>';
$comparisonHtml[] = '</tr>';

// 数据行
foreach ($methods as $method) {
    $comparisonHtml[] = '<tr>';
    $comparisonHtml[] = '<td class="text-teal-500">' . $method . '</td>';

    $times = [];
    foreach ($strategies as $strategyKey => $strategy) {
        $time = $results[$strategyKey][$method]['time'];
        $times[$strategyKey] = $time;
        $comparisonHtml[] = '<td class="text-center">' . sprintf('%.2f ms', $time) . '</td>';
    }

    // 找出最快的策略
    $minTime = min($times);
    $maxTime = max($times);
    $bestStrategy = array_search($minTime, $times);

    $comparisonHtml[] = '<td class="text-center ' . $strategies[$bestStrategy]['color'] . '">' .
        $strategies[$bestStrategy]['short_name'] . '</td>';

    // 计算加速比
    $speedup = $maxTime > 0 ? sprintf('%.1fx', $maxTime / $minTime) : '-';
    $comparisonHtml[] = '<td class="text-center">' . $speedup . '</td>';
    $comparisonHtml[] = '</tr>';
}

// 分隔线
$comparisonHtml[] = '<tr><td colspan="6" class="text-gray-500">────────────────────────────────────────────────</td></tr>';

// 总计行标题（添加列说明）
$comparisonHtml[] = '<tr class="text-gray-400">';
$comparisonHtml[] = '<td></td>';
$comparisonHtml[] = '<td class="text-center text-blue-400">Memory</td>';
$comparisonHtml[] = '<td class="text-center text-green-400">Cached</td>';
$comparisonHtml[] = '<td class="text-center text-yellow-400">Smart</td>';
$comparisonHtml[] = '<td></td>';
$comparisonHtml[] = '<td></td>';
$comparisonHtml[] = '</tr>';

// 总计行
$comparisonHtml[] = '<tr>';
$comparisonHtml[] = '<td class="font-bold text-white">TOTAL</td>';

$totalTimes = [];
foreach ($strategies as $strategyKey => $strategy) {
    $totalTime = $results[$strategyKey]['total'];
    $totalTimes[$strategyKey] = $totalTime;
    $isFastest = false;

    // 预先检查是否是最快的
    $minTotal = min(
        array_values($results)['memory']['total'] ?? PHP_FLOAT_MAX,
        array_values($results)['cached']['total'] ?? PHP_FLOAT_MAX,
        array_values($results)['smart']['total'] ?? PHP_FLOAT_MAX
    );

    if ($totalTime == $minTotal) {
        $comparisonHtml[] = '<td class="text-center font-bold ' . $strategy['color'] . '">' . sprintf('%.2f ms', $totalTime) . '</td>';
    } else {
        $comparisonHtml[] = '<td class="text-center">' . sprintf('%.2f ms', $totalTime) . '</td>';
    }
}

$minTotal = min($totalTimes);
$maxTotal = max($totalTimes);
$bestTotal = array_search($minTotal, $totalTimes);

$comparisonHtml[] = '<td class="text-center font-bold ' . $strategies[$bestTotal]['color'] . '">' .
    $strategies[$bestTotal]['short_name'] . '</td>';

// 总体加速比
$totalSpeedup = $maxTotal > 0 ? sprintf('%.1fx', $maxTotal / $minTotal) : '-';
$comparisonHtml[] = '<td class="text-center font-bold">' . $totalSpeedup . '</td>';
$comparisonHtml[] = '</tr>';

// 添加说明行
$comparisonHtml[] = '<tr class="text-gray-500">';
$comparisonHtml[] = '<td colspan="6" class="text-center">↑ 三列数字分别是：内存优化策略、缓存策略、智能策略的总耗时</td>';
$comparisonHtml[] = '</tr>';

$comparisonTable = implode("\n", $comparisonHtml);

// 计算内存使用情况
$memoryInfo = [];
foreach (['memory', 'cached', 'smart'] as $strategyKey) {
    $converter = ConverterFactory::make($strategyKey);
    $converter->convert('测试'); // 触发加载
    $info = $converter->getMemoryUsage();
    $memoryInfo[$strategyKey] = $info;
}

// 生成内存使用对比
$memoryHtml = [];
foreach ($memoryInfo as $strategy => $info) {
    $memoryHtml[] = sprintf(
        '<tr>
            <td class="%s">%s</td>
            <td>%s</td>
            <td class="text-gray-500">%s</td>
        </tr>',
        $strategies[$strategy]['color'],
        $strategies[$strategy]['name'],
        $info['peak_memory'],
        $info['description']
    );
}
$memoryTable = implode("\n", $memoryHtml);

// 创建综合对比表格
$summaryHtml = [];
$baselineTime = $totalTimes['memory']; // 使用Memory作为基准
$minTime = min($totalTimes);
$maxTime = max($totalTimes);

// 解析内存值（提取数字）
function parseMemory($memStr) {
    preg_match('/[\d.]+/', $memStr, $matches);
    return floatval($matches[0] ?? 0);
}

$memoryValues = [];
foreach ($strategies as $key => $strategy) {
    $memoryValues[$key] = parseMemory($memoryInfo[$key]['peak_memory']);
}
$minMemory = min($memoryValues);
$maxMemory = max($memoryValues);

foreach ($strategies as $strategyKey => $strategy) {
    $time = $totalTimes[$strategyKey];
    $memory = $memoryInfo[$strategyKey]['peak_memory'];
    $memoryVal = $memoryValues[$strategyKey];
    $speedup = $baselineTime / $time;
    
    $isFastest = $time == $minTime;
    $isLeastMemory = $memoryVal == $minMemory;
    
    // 性能评级
    $performanceIcon = '';
    if ($isFastest && $isLeastMemory) {
        $performanceIcon = '🏆'; // 最佳：速度最快且内存最少
    } elseif ($isFastest) {
        $performanceIcon = '⚡'; // 速度最快
    } elseif ($isLeastMemory) {
        $performanceIcon = '💚'; // 内存最少
    } elseif ($speedup > 1.0) {
        $performanceIcon = '✨'; // 比基准快
    }
    
    $rowClass = $isFastest ? 'font-bold' : '';
    $memoryClass = $isLeastMemory ? 'text-green-500' : ($memoryVal == $maxMemory ? 'text-red-500' : '');
    $timeClass = $isFastest ? 'text-green-500' : ($time == $maxTime ? 'text-red-500' : '');
    
    $summaryHtml[] = sprintf(
        '<tr class="%s">
            <td class="%s">%s %s</td>
            <td class="text-center %s">%s</td>
            <td class="text-center %s">%.2f ms</td>
            <td class="text-center %s">%.2fx</td>
            <td class="text-gray-500">%s</td>
        </tr>',
        $rowClass,
        $strategy['color'],
        $performanceIcon,
        $strategy['name'],
        $memoryClass,
        $memory,
        $timeClass,
        $time,
        $speedup >= 1.2 ? 'text-green-500' : ($speedup <= 0.8 ? 'text-red-500' : ''),
        $speedup,
        $memoryInfo[$strategyKey]['description']
    );
}

$summaryTable = implode("\n", $summaryHtml);

// 性能提升总结
$speedupSummary = '';
if (isset($totalTimes['cached']) && isset($totalTimes['memory'])) {
    $cacheSpeedup = round($totalTimes['memory'] / $totalTimes['cached'], 2);
    $speedupSummary = sprintf(
        '<div class="mt-2">📊 Performance Summary:</div>
        <div>• <span class="text-green-500">Cached strategy</span> is <span class="font-bold">%.2fx faster</span> than Memory Optimized</div>',
        $cacheSpeedup
    );

    if (isset($totalTimes['smart'])) {
        $smartVsMemory = round($totalTimes['memory'] / $totalTimes['smart'], 2);
        $smartVsCached = round($totalTimes['smart'] / $totalTimes['cached'], 2);
        $speedupSummary .= sprintf(
            '<div>• <span class="text-yellow-500">Smart strategy</span> is <span class="font-bold">%.2fx faster</span> than Memory, <span class="font-bold">%.2fx slower</span> than Cached</div>',
            $smartVsMemory,
            $smartVsCached
        );
    }
}

$totalUsage = round(microtime(true) - $totalStart, 5) * 1000;

render(<<<"HTML"
    <div class="m-2">
        <div class="px-1 bg-green-600 text-white">Pinyin Benchmark</div>

        <div class="py-1">
            Converted <span class="text-teal-500">{$textLength}</span> chars with following methods:
        </div>

        <div class="text-yellow-500">Standard Test (Memory Optimized Strategy):</div>
        <table>
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Time Usage</th>
                    <th>Result</th>
                </tr>
            </thead>
            {$htmlOriginal}
        </table>

        <div class="mt-1">
            Default strategy usage: <span class="text-green-500">{$defaultTotalUsage}</span>ms
        </div>

        <div class="my-1 text-yellow-500">Strategy Comparison:</div>
        <table>
            <thead>
                {$comparisonTable}
            </thead>
        </table>

        <div class="mt-3 mb-1 text-yellow-500">📊 综合性能对比（内存 + 耗时 + 倍率）:</div>
        <table>
            <thead>
                <tr>
                    <th>策略</th>
                    <th class="text-center">内存占用</th>
                    <th class="text-center">总耗时</th>
                    <th class="text-center">速度倍率</th>
                    <th>特点说明</th>
                </tr>
            </thead>
            {$summaryTable}
        </table>
        
        <div class="mt-2 text-gray-500">
            <div>速度倍率说明：以 Memory Optimized 为基准 (1.0x)</div>
            <div class="mt-1">图标说明：⚡速度最快 | 💚内存最少 | 🏆综合最优 | ✨比基准快</div>
        </div>
        
        <div class="mt-2 px-2 py-1 bg-blue-800 text-white">
            <div class="font-bold">🎯 快速选择建议：</div>
            <div>• 内存受限环境（如Web请求）→ 选择 <span class="text-blue-400">Memory Optimized</span></div>
            <div>• 批量处理大量文本 → 选择 <span class="text-green-400">Cached</span></div>
            <div>• 平衡性能和内存 → 选择 <span class="text-yellow-400">Smart</span></div>
        </div>

        {$speedupSummary}

        <div class="mt-3 mb-1 text-yellow-500">Memory Usage Details:</div>
        <table>
            <thead>
                <tr>
                    <th>Strategy</th>
                    <th>Peak Memory</th>
                    <th>Description</th>
                </tr>
            </thead>
            {$memoryTable}
        </table>

        <div class="mt-1">
            <div>Total benchmark time: <span class="text-green-500">{$totalUsage}</span>ms</div>
        </div>

        <div class="mt-3 text-gray-500">
            <div>💡 Tips:</div>
            <div>• <span class="text-blue-500">Memory Optimized</span>: Best for web requests with limited memory</div>
            <div>• <span class="text-green-500">Cached</span>: Best for batch processing and repeated conversions</div>
            <div>• <span class="text-yellow-500">Smart</span>: Balanced approach with adaptive optimization</div>
        </div>
    </div>
HTML);

// 清理缓存
CachedConverter::clearCache();
SmartConverter::clearCache();
