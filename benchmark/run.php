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

// 收集总时间数据（供后面使用）
$totalTimes = [];
foreach ($strategies as $strategyKey => $strategy) {
    $totalTimes[$strategyKey] = $results[$strategyKey]['total'];
}

// 计算内存使用情况
$memoryInfo = [];
foreach (['memory', 'cached', 'smart'] as $strategyKey) {
    $converter = ConverterFactory::make($strategyKey);
    $converter->convert('测试'); // 触发加载
    $info = $converter->getMemoryUsage();
    $memoryInfo[$strategyKey] = $info;
}

// 不再需要单独的内存表格

// 创建综合对比表格
$summaryHtml = [];
$baselineTime = $totalTimes['memory']; // 使用Memory作为基准
$minTime = min($totalTimes);
$maxTime = max($totalTimes);

// 解析内存值（提取数字）
function parseMemory($memStr)
{
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

    // 简化的适用场景描述
    $scenario = '';
    switch ($strategyKey) {
        case 'memory':
            $scenario = 'Web请求、内存受限';
            break;
        case 'cached':
            $scenario = '批量处理、重复转换';
            break;
        case 'smart':
            $scenario = '通用场景、自适应';
            break;
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
        $scenario
    );
}

$summaryTable = implode("\n", $summaryHtml);

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

        <div class="mt-1 mb-1 text-yellow-500">📊 策略性能对比:</div>
        <table>
            <thead>
                <tr>
                    <th>策略</th>
                    <th class="text-center">内存占用</th>
                    <th class="text-center">总耗时</th>
                    <th class="text-center">速度倍率</th>
                    <th>适用场景</th>
                </tr>
            </thead>
            {$summaryTable}
        </table>

        <div class="text-gray-500">
            <div>* 速度倍率以 Memory Optimized 为基准 (1.0x)</div>
        </div>

        <div class="mt-1 py-1 text-white">
            <div class="font-bold">🎯 如何选择：</div>
            <div>• Web请求 → <span class="text-blue-400">Memory Optimized</span> (省内存)</div>
            <div>• 批量处理 → <span class="text-green-400">Cached</span> (最快)</div>
            <div>• 通用场景 → <span class="text-yellow-400">Smart</span> (平衡)</div>
        </div>

        <div class="mt-1">
            <div>Total benchmark time: <span class="text-green-500">{$totalUsage}</span>ms</div>
        </div>
    </div>
HTML);

// 清理缓存
CachedConverter::clearCache();
SmartConverter::clearCache();
