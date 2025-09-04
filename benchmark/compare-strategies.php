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
        'name' => '短文本（小于10个字符）',
        'texts' => [
            '你好',
            '世界',
            '中国',
            '北京',
            '上海',
        ],
    ],
    'medium' => [
        'name' => '中等文本（10-50个字符）',
        'texts' => [
            '带着希望去旅行，比到达终点更美好',
            '人生就像一盒巧克力，你永远不知道下一颗是什么味道',
            '生活不止眼前的苟且，还有诗和远方',
            '愿你出走半生，归来仍是少年',
            '岁月不居，时节如流',
        ],
    ],
    'long' => [
        'name' => '长文本（大于100个字符）',
        'texts' => [
            str_repeat('中华人民共和国成立于1949年10月1日，', 5),
            str_repeat('春眠不觉晓，处处闻啼鸟。夜来风雨声，花落知多少。', 4),
            file_get_contents(__DIR__ . '/input.txt'),
        ],
    ],
    'mixed' => [
        'name' => '混合内容',
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
        'name' => '冷启动',
        'description' => '首次转换（无缓存）',
        'prepare' => function () {
            Pinyin::clearCache();
        },
        'iterations' => 1,
    ],
    'warm' => [
        'name' => '缓存预热',
        'description' => '重复转换（缓存已预热）',
        'prepare' => function ($texts, $strategy) {
            // 预热缓存
            foreach ($texts as $text) {
                Pinyin::sentence($text);
            }
        },
        'iterations' => 10,
    ],
    'batch' => [
        'name' => '批量处理',
        'description' => '顺序处理多个文本',
        'prepare' => function () {
            Pinyin::clearCache();
        },
        'iterations' => 1,
        'batch' => true,
    ],
];

// 策略配置
$strategies = [
    'memory' => [
        'name' => '内存优化',
        'setup' => function () {
            Pinyin::useMemoryOptimized();
        },
        'color' => 'blue',
    ],
    'cached' => [
        'name' => '缓存',
        'setup' => function () {
            Pinyin::useCached();
        },
        'color' => 'green',
    ],
    'smart' => [
        'name' => '智能',
        'setup' => function () {
            Pinyin::useSmart();
        },
        'color' => 'yellow',
    ],
];

// 执行测试
$results = [];

foreach ($scenarios as $scenarioKey => $scenario) {
    render(sprintf(
        '<div class="text-cyan-500">Running scenario: %s...</div>',
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
Pinyin::clearCache();

// 生成报告
$html = [];

foreach ($scenarios as $scenarioKey => $scenario) {
    $html[] = sprintf(
        '<div class="mt-1">
            <div class="font-bold text-white bg-gray-700 px-2">%s</div>
            <div class="text-gray-400 px-2">%s</div>
        </div>',
        $scenario['name'],
        $scenario['description']
    );

    // 表格头
    $html[] = '<table class="mt-1">';
    $html[] = '<thead><tr>';
    $html[] = '<th>文本类型</th>';
    foreach ($strategies as $strategy) {
        $html[] = sprintf('<th class="text-center text-%s-500">%s</th>', $strategy['color'], $strategy['name']);
    }
    $html[] = '<th class="text-center">最快方案</th>';
    $html[] = '<th class="text-center">加速比</th>';
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
            $class = $time == $minTime ? 'font-bold text-green-500' : ($time == $maxTime ? 'text-red-500' : '');
            $html[] = sprintf('<td class="text-center %s">%.2f ms</td>', $class, $time);
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
$html[] = '<div class="mt-1">';
$html[] = '<div class="font-bold text-white bg-gray-700 px-2">内存使用对比</div>';
$html[] = '</div>';

$html[] = '<table>';
$html[] = '<thead><tr>';
$html[] = '<th>策略</th>';
$html[] = '<th>峰值内存</th>';
$html[] = '<th>速度</th>';
$html[] = '<th>最适用场景</th>';
$html[] = '</tr></thead>';
$html[] = '<tbody>';

foreach ($strategies as $strategyKey => $strategy) {
    // 运行时内存监控
    $initialMemory = memory_get_usage();
    $converter = ConverterFactory::make($strategyKey);
    $converter->convert('测试文本'); // 触发加载
    $memoryGrowth = memory_get_usage() - $initialMemory;

    $html[] = '<tr>';
    $html[] = sprintf('<td class="text-%s-500">%s</td>', $strategy['color'], $strategy['name']);
    $html[] = sprintf('<td>%.1f KB</td>', $memoryGrowth / 1024);
    $html[] = sprintf('<td>%s</td>', $strategy['speed']);
    $html[] = sprintf('<td>%s</td>', $strategy['use_case']);
    $html[] = '</tr>';
}

$html[] = '</tbody></table>';

// 建议
$recommendations = [
    'Web应用' => [
        'strategy' => '内存优化',
        'reason' => '内存占用最小，无内存累积',
        'color' => 'blue',
    ],
    '命令行批处理' => [
        'strategy' => '缓存',
        'reason' => '重复转换时性能最佳',
        'color' => 'green',
    ],
    '混合负载' => [
        'strategy' => '智能',
        'reason' => '根据文本长度自适应优化',
        'color' => 'yellow',
    ],
    '内存受限' => [
        'strategy' => '内存优化',
        'reason' => '最低内存使用（峰值约400KB）',
        'color' => 'blue',
    ],
    '性能敏感' => [
        'strategy' => '缓存',
        'reason' => '预热后最快，约2-3倍加速',
        'color' => 'green',
    ],
];

$html[] = '<div class="mt-1">';
$html[] = '<div class="font-bold text-white bg-gray-700 px-2">推荐</div>';
$html[] = '</div>';

$html[] = '<div class="mt-1">';
foreach ($recommendations as $useCase => $rec) {
    $html[] = sprintf(
        '<div>
            <span class="font-bold mr-1">%s: </span>
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
        <div class="px-1 bg-green-600 text-white">拼音策略性能对比</div>
        %s
    </div>',
    implode("\n", $html)
));
