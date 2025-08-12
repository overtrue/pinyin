<?php

require __DIR__ . '/../vendor/autoload.php';

use Overtrue\Pinyin\Pinyin;
use Overtrue\Pinyin\ConverterFactory;
use Overtrue\Pinyin\Converters\CachedConverter;
use Overtrue\Pinyin\Converters\SmartConverter;

use function Termwind\{render};

// 测试数据集
$testSets = [
    'short' => [
        'name' => 'Short Text (< 10 chars)',
        'texts' => [
            '你好',
            '世界',
            '中国',
            '北京',
            '上海',
        ],
    ],
    'medium' => [
        'name' => 'Medium Text (10-50 chars)',
        'texts' => [
            '带着希望去旅行，比到达终点更美好',
            '人生就像一盒巧克力，你永远不知道下一颗是什么味道',
            '生活不止眼前的苟且，还有诗和远方',
            '愿你出走半生，归来仍是少年',
            '岁月不居，时节如流',
        ],
    ],
    'long' => [
        'name' => 'Long Text (> 100 chars)',
        'texts' => [
            str_repeat('中华人民共和国成立于1949年10月1日，', 5),
            str_repeat('春眠不觉晓，处处闻啼鸟。夜来风雨声，花落知多少。', 4),
            file_get_contents(__DIR__ . '/input.txt'),
        ],
    ],
    'mixed' => [
        'name' => 'Mixed Content',
        'texts' => [
            '你好2024！',
            'Hello世界123',
            '【重要】明天下午3:00开会',
            'Email: test@example.com 邮箱',
            '😀开心每一天🎉',
        ],
    ],
];

// 测试场景
$scenarios = [
    'cold' => [
        'name' => 'Cold Start',
        'description' => 'First conversion (no cache)',
        'prepare' => function() {
            CachedConverter::clearCache();
            SmartConverter::clearCache();
        },
        'iterations' => 1,
    ],
    'warm' => [
        'name' => 'Warm Cache',
        'description' => 'Repeated conversions (cache warmed up)',
        'prepare' => function($texts, $strategy) {
            // 预热缓存
            foreach ($texts as $text) {
                Pinyin::sentence($text);
            }
        },
        'iterations' => 10,
    ],
    'batch' => [
        'name' => 'Batch Processing',
        'description' => 'Processing multiple texts in sequence',
        'prepare' => function() {
            CachedConverter::clearCache();
            SmartConverter::clearCache();
        },
        'iterations' => 1,
        'batch' => true,
    ],
];

// 策略配置
$strategies = [
    'memory' => [
        'name' => 'Memory Optimized',
        'setup' => function() { Pinyin::useMemoryOptimized(); },
        'color' => 'blue',
    ],
    'cached' => [
        'name' => 'Cached',
        'setup' => function() { Pinyin::useCached(); },
        'color' => 'green',
    ],
    'smart' => [
        'name' => 'Smart',
        'setup' => function() { Pinyin::useSmart(); },
        'color' => 'yellow',
    ],
];

// 执行测试
$results = [];

foreach ($scenarios as $scenarioKey => $scenario) {
    render(sprintf(
        '<div class="mt-2 text-cyan-500">Running scenario: %s...</div>',
        $scenario['name']
    ));
    
    foreach ($testSets as $setKey => $testSet) {
        foreach ($strategies as $strategyKey => $strategy) {
            $strategy['setup']();
            $scenario['prepare']($testSet['texts'] ?? [], $strategyKey);
            
            $times = [];
            
            if (isset($scenario['batch']) && $scenario['batch']) {
                // 批处理模式
                $start = microtime(true);
                for ($i = 0; $i < $scenario['iterations']; $i++) {
                    foreach ($testSet['texts'] as $text) {
                        Pinyin::sentence($text);
                    }
                }
                $elapsed = (microtime(true) - $start) * 1000;
                $times[] = $elapsed / count($testSet['texts']);
            } else {
                // 单个处理模式
                foreach ($testSet['texts'] as $text) {
                    $start = microtime(true);
                    for ($i = 0; $i < $scenario['iterations']; $i++) {
                        Pinyin::sentence($text);
                    }
                    $elapsed = (microtime(true) - $start) * 1000 / $scenario['iterations'];
                    $times[] = $elapsed;
                }
            }
            
            $avgTime = array_sum($times) / count($times);
            $results[$scenarioKey][$setKey][$strategyKey] = $avgTime;
        }
    }
}

// 清理
CachedConverter::clearCache();
SmartConverter::clearCache();

// 生成报告
$html = [];

foreach ($scenarios as $scenarioKey => $scenario) {
    $html[] = sprintf(
        '<div class="mt-4">
            <div class="text-lg font-bold text-white bg-gray-700 px-2">%s</div>
            <div class="text-sm text-gray-400 px-2">%s</div>
        </div>',
        $scenario['name'],
        $scenario['description']
    );
    
    // 表格头
    $html[] = '<table class="mt-2">';
    $html[] = '<thead><tr>';
    $html[] = '<th class="text-left">Text Type</th>';
    foreach ($strategies as $strategy) {
        $html[] = sprintf('<th class="text-center text-%s-500">%s</th>', $strategy['color'], $strategy['name']);
    }
    $html[] = '<th class="text-center">Winner</th>';
    $html[] = '<th class="text-center">Speedup</th>';
    $html[] = '</tr></thead>';
    $html[] = '<tbody>';
    
    // 数据行
    foreach ($testSets as $setKey => $testSet) {
        $html[] = '<tr>';
        $html[] = sprintf('<td>%s</td>', $testSet['name']);
        
        $times = $results[$scenarioKey][$setKey];
        $minTime = min($times);
        $maxTime = max($times);
        
        foreach ($strategies as $strategyKey => $strategy) {
            $time = $times[$strategyKey];
            $class = $time == $minTime ? 'font-bold text-green-500' : 
                    ($time == $maxTime ? 'text-red-500' : '');
            $html[] = sprintf(
                '<td class="text-center %s">%.2f ms</td>',
                $class,
                $time
            );
        }
        
        // Winner
        $winner = array_search($minTime, $times);
        $html[] = sprintf(
            '<td class="text-center text-%s-500">%s</td>',
            $strategies[$winner]['color'],
            $strategies[$winner]['name']
        );
        
        // Speedup
        $speedup = $maxTime / $minTime;
        $html[] = sprintf(
            '<td class="text-center">%.1fx</td>',
            $speedup
        );
        
        $html[] = '</tr>';
    }
    
    $html[] = '</tbody></table>';
}

// 内存使用对比
$html[] = '<div class="mt-6">';
$html[] = '<div class="text-lg font-bold text-white bg-gray-700 px-2">Memory Usage Comparison</div>';
$html[] = '</div>';

$html[] = '<table class="mt-2">';
$html[] = '<thead><tr>';
$html[] = '<th>Strategy</th>';
$html[] = '<th>Peak Memory</th>';
$html[] = '<th>Persistent Cache</th>';
$html[] = '<th>Best For</th>';
$html[] = '</tr></thead>';
$html[] = '<tbody>';

foreach ($strategies as $strategyKey => $strategy) {
    $converter = ConverterFactory::make($strategyKey);
    $converter->convert('测试'); // 触发加载
    $info = $converter->getMemoryUsage();
    
    $html[] = '<tr>';
    $html[] = sprintf('<td class="text-%s-500">%s</td>', $strategy['color'], $strategy['name']);
    $html[] = sprintf('<td>%s</td>', $info['peak_memory']);
    $html[] = sprintf('<td>%s</td>', $info['persistent_cache'] ? 'Yes' : 'No');
    $html[] = sprintf('<td class="text-sm">%s</td>', $info['description']);
    $html[] = '</tr>';
}

$html[] = '</tbody></table>';

// 建议
$recommendations = [
    'Web Applications' => [
        'strategy' => 'Memory Optimized',
        'reason' => 'Minimal memory footprint, no memory accumulation',
        'color' => 'blue',
    ],
    'CLI Batch Processing' => [
        'strategy' => 'Cached',
        'reason' => 'Best performance for repeated conversions',
        'color' => 'green',
    ],
    'Mixed Workloads' => [
        'strategy' => 'Smart',
        'reason' => 'Adaptive optimization based on text length',
        'color' => 'yellow',
    ],
    'Memory Constrained' => [
        'strategy' => 'Memory Optimized',
        'reason' => 'Lowest memory usage (~400KB peak)',
        'color' => 'blue',
    ],
    'Performance Critical' => [
        'strategy' => 'Cached',
        'reason' => 'Fastest after warm-up, ~2-3x speedup',
        'color' => 'green',
    ],
];

$html[] = '<div class="mt-6">';
$html[] = '<div class="text-lg font-bold text-white bg-gray-700 px-2">Recommendations</div>';
$html[] = '</div>';

$html[] = '<div class="mt-2">';
foreach ($recommendations as $useCase => $rec) {
    $html[] = sprintf(
        '<div class="mb-1">
            <span class="font-bold">%s:</span> 
            <span class="text-%s-500">%s</span> 
            <span class="text-gray-500">- %s</span>
        </div>',
        $useCase,
        $rec['color'],
        $rec['strategy'],
        $rec['reason']
    );
}
$html[] = '</div>';

// 渲染结果
render(sprintf(
    '<div class="m-2">
        <div class="px-1 bg-green-600 text-white">Pinyin Strategy Performance Comparison</div>
        %s
    </div>',
    implode("\n", $html)
));