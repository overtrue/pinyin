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
$methods = ['sentence','fullSentence','name','passportName','phrase','permalink','polyphones','chars','abbr','nameAbbr'];

// 使用默认策略（内存优化）运行原有测试
foreach ($methods as $method) {
    $start = microtime(true);
    $result = call_user_func(Pinyin::class.'::'.$method, $text);
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
        'setup' => function() { Pinyin::useMemoryOptimized(); },
        'color' => 'text-blue-500',
    ],
    'cached' => [
        'name' => 'Cached',
        'setup' => function() { Pinyin::useCached(); },
        'color' => 'text-green-500',
    ],
    'smart' => [
        'name' => 'Smart',
        'setup' => function() { Pinyin::useSmart(); },
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
        $result = call_user_func(Pinyin::class.'::'.$method, $text);
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
    $comparisonHtml[] = '<th class="text-center">' . $strategy['name'] . '</th>';
}
$comparisonHtml[] = '<th class="text-center">Best</th>';
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
    $bestStrategy = array_search($minTime, $times);
    
    $comparisonHtml[] = '<td class="text-center ' . $strategies[$bestStrategy]['color'] . '">' . 
             $strategies[$bestStrategy]['name'] . '</td>';
    $comparisonHtml[] = '</tr>';
}

// 总计行
$comparisonHtml[] = '<tr class="border-t">';
$comparisonHtml[] = '<td class="font-bold">Total</td>';
$totalTimes = [];
foreach ($strategies as $strategyKey => $strategy) {
    $totalTime = $results[$strategyKey]['total'];
    $totalTimes[$strategyKey] = $totalTime;
    $comparisonHtml[] = '<td class="text-center font-bold">' . sprintf('%.2f ms', $totalTime) . '</td>';
}

$minTotal = min($totalTimes);
$bestTotal = array_search($minTotal, $totalTimes);
$comparisonHtml[] = '<td class="text-center font-bold ' . $strategies[$bestTotal]['color'] . '">' . 
         $strategies[$bestTotal]['name'] . '</td>';
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

// 性能提升总结
$speedup = '';
if (isset($totalTimes['cached']) && isset($totalTimes['memory'])) {
    $cacheSpeedup = round($totalTimes['memory'] / $totalTimes['cached'], 2);
    $speedup = sprintf(
        'Cached strategy is <span class="text-green-500">%.2fx faster</span> than Memory Optimized for this benchmark.',
        $cacheSpeedup
    );
}

$totalUsage = round(microtime(true) - $totalStart, 5) * 1000;

render(<<<"HTML"
    <div class="m-2">
        <div class="px-1 bg-green-600 text-white">Pinyin Benchmark</div>
        
        <div class="py-1">
            Converted <span class="text-teal-500">{$textLength}</span> chars with following methods:
        </div>
        
        <div class="mt-2 mb-1 text-yellow-500">Standard Test (Memory Optimized Strategy):</div>
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
        
        <div class="mt-4 mb-1 text-yellow-500">Strategy Comparison:</div>
        <table>
            <thead>
                {$comparisonTable}
            </thead>
        </table>
        
        <div class="mt-3 mb-1 text-yellow-500">Memory Usage:</div>
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
        
        <div class="mt-3">
            <div>Total benchmark time: <span class="text-green-500">{$totalUsage}</span>ms</div>
            <div class="mt-1">{$speedup}</div>
        </div>
        
        <div class="mt-3 text-gray-500">
            <div>Tips:</div>
            <div>• <span class="text-blue-500">Memory Optimized</span>: Best for web requests with limited memory</div>
            <div>• <span class="text-green-500">Cached</span>: Best for batch processing and repeated conversions</div>
            <div>• <span class="text-yellow-500">Smart</span>: Balanced approach with adaptive optimization</div>
        </div>
    </div>
HTML);

// 清理缓存
CachedConverter::clearCache();
SmartConverter::clearCache();
