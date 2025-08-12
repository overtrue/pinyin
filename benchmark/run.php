<?php

require __DIR__ . '/../vendor/autoload.php';

use Overtrue\Pinyin\Pinyin;
use Overtrue\Pinyin\ConverterFactory;
use Overtrue\Pinyin\Converters\CachedConverter;
use Overtrue\Pinyin\Converters\SmartConverter;

use function Termwind\{render};

$totalStart = microtime(true);
$text = file_get_contents(__DIR__ . '/input.txt');

// 测试方法列表
$methods = ['sentence','fullSentence','name','passportName','phrase','permalink','polyphones','chars','abbr','nameAbbr'];

// 不同策略的测试
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
$textLength = mb_strlen($text);

// 测试每个策略
foreach ($strategies as $strategyKey => $strategy) {
    $strategy['setup']();
    $strategyStart = microtime(true);
    
    foreach ($methods as $method) {
        $start = microtime(true);
        $result = call_user_func(Pinyin::class.'::'.$method, $text);
        $usage = round(microtime(true) - $start, 5) * 1000;
        $sample = mb_substr(is_array($result) ? implode(' ', $result) : (string) $result, 0, 30);
        
        $results[$strategyKey][$method] = [
            'time' => $usage,
            'sample' => $sample,
        ];
    }
    
    $results[$strategyKey]['total'] = round(microtime(true) - $strategyStart, 5) * 1000;
}

// 清理缓存
CachedConverter::clearCache();
SmartConverter::clearCache();

// 生成对比表格
$html = [];

// 标题行
$html[] = '<tr>';
$html[] = '<th class="text-left">Method</th>';
foreach ($strategies as $strategy) {
    $html[] = '<th class="text-center">' . $strategy['name'] . '</th>';
}
$html[] = '<th class="text-center">Best</th>';
$html[] = '</tr>';

// 数据行
foreach ($methods as $method) {
    $html[] = '<tr>';
    $html[] = '<td class="text-teal-500">' . $method . '</td>';
    
    $times = [];
    foreach ($strategies as $strategyKey => $strategy) {
        $time = $results[$strategyKey][$method]['time'];
        $times[$strategyKey] = $time;
        $html[] = '<td class="text-center">' . sprintf('%.2f ms', $time) . '</td>';
    }
    
    // 找出最快的策略
    $minTime = min($times);
    $bestStrategy = array_search($minTime, $times);
    $improvement = '';
    if ($bestStrategy === 'cached' && isset($times['memory'])) {
        $improvement = sprintf(' (%.1fx)', $times['memory'] / $times['cached']);
    }
    
    $html[] = '<td class="text-center ' . $strategies[$bestStrategy]['color'] . '">' . 
             $strategies[$bestStrategy]['name'] . $improvement . '</td>';
    $html[] = '</tr>';
}

// 总计行
$html[] = '<tr class="border-t">';
$html[] = '<td class="font-bold">Total</td>';
$totalTimes = [];
foreach ($strategies as $strategyKey => $strategy) {
    $totalTime = $results[$strategyKey]['total'];
    $totalTimes[$strategyKey] = $totalTime;
    $html[] = '<td class="text-center font-bold">' . sprintf('%.2f ms', $totalTime) . '</td>';
}

$minTotal = min($totalTimes);
$bestTotal = array_search($minTotal, $totalTimes);
$html[] = '<td class="text-center font-bold ' . $strategies[$bestTotal]['color'] . '">' . 
         $strategies[$bestTotal]['name'] . '</td>';
$html[] = '</tr>';

$htmlTable = implode("\n", $html);
$totalUsage = round(microtime(true) - $totalStart, 5) * 1000;

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
            <td>%s</td>
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
        'Cached strategy is <span class="text-green-500">%.2fx faster</span> than Memory Optimized for repeated conversions.',
        $cacheSpeedup
    );
}

render(<<<"HTML"
    <div class="m-2">
        <div class="px-1 bg-green-600 text-white">Pinyin Benchmark - Multi-Strategy Comparison</div>
        
        <div class="py-1">
            Converted <span class="text-teal-500">{$textLength}</span> chars with following methods across different strategies:
        </div>
        
        <div class="mt-2 mb-1 text-yellow-500">Performance Comparison:</div>
        <table>
            <thead>
                {$htmlTable}
            </thead>
        </table>
        
        <div class="mt-3 mb-1 text-yellow-500">Memory Usage Comparison:</div>
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
