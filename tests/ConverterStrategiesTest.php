<?php

namespace Overtrue\Pinyin\Tests;

use Overtrue\Pinyin\Collection;
use Overtrue\Pinyin\ConverterFactory;
use Overtrue\Pinyin\Converters\CachedConverter;
use Overtrue\Pinyin\Converters\MemoryOptimizedConverter;
use Overtrue\Pinyin\Converters\SmartConverter;
use PHPUnit\Framework\TestCase;

class ConverterStrategiesTest extends TestCase
{
    private array $testCases = [
        '你好世界' => ['nǐ', 'hǎo', 'shì', 'jiè'],
        '重庆' => ['chóng', 'qìng'],
        '中国' => ['zhōng', 'guó'],
        '带着希望去旅行' => ['dài', 'zhe', 'xī', 'wàng', 'qù', 'lǚ', 'xíng'],
    ];

    protected function tearDown(): void
    {
        // 清理缓存
        CachedConverter::clearCache();
        SmartConverter::clearCache();
    }

    /**
     * 测试所有策略的基本转换功能
     */
    public function test_all_strategies_produce_same_results()
    {
        $strategies = [
            ConverterFactory::MEMORY_OPTIMIZED,
            ConverterFactory::CACHED,
            ConverterFactory::SMART,
        ];

        foreach ($this->testCases as $input => $expected) {
            $results = [];

            foreach ($strategies as $strategy) {
                $converter = ConverterFactory::make($strategy);
                $result = $converter->convert($input);
                $results[$strategy] = $result->toArray();

                // 每个策略都应该产生正确的结果
                $this->assertEquals($expected, $result->toArray(),
                    "Strategy {$strategy} failed for input: {$input}");
            }

            // 所有策略应该产生相同的结果
            $firstResult = reset($results);
            foreach ($results as $strategy => $result) {
                $this->assertEquals($firstResult, $result,
                    "Strategy {$strategy} produced different result");
            }
        }
    }

    /**
     * 测试内存优化策略
     */
    public function test_memory_optimized_converter()
    {
        $converter = new MemoryOptimizedConverter;

        // 测试基本转换
        $result = $converter->convert('你好世界');
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(['nǐ', 'hǎo', 'shì', 'jiè'], $result->toArray());

        // 测试内存信息
        $memoryInfo = $converter->getMemoryUsage();
        $this->assertEquals('memory_optimized', $memoryInfo['strategy']);
        $this->assertFalse($memoryInfo['persistent_cache']);

        // 测试多音字
        $result = $converter->heteronym()->convert('重庆');
        $this->assertArrayHasKey('重', $result->toArray());
        $this->assertIsArray($result->toArray()['重']);
        $this->assertContains('chóng', $result->toArray()['重']);
        $this->assertContains('zhòng', $result->toArray()['重']);
    }

    /**
     * 测试缓存策略
     */
    public function test_cached_converter()
    {
        $converter = new CachedConverter;

        // 第一次转换（冷启动）
        $result1 = $converter->convert('你好世界');
        $this->assertEquals(['nǐ', 'hǎo', 'shì', 'jiè'], $result1->toArray());

        // 第二次转换（缓存生效）
        $result2 = $converter->convert('你好');
        $this->assertEquals(['nǐ', 'hǎo'], $result2->toArray());

        // 测试内存信息
        $memoryInfo = $converter->getMemoryUsage();
        $this->assertEquals('cached', $memoryInfo['strategy']);
        $this->assertTrue($memoryInfo['persistent_cache']);

        // 测试缓存清理
        CachedConverter::clearCache();

        // 清理后再次转换应该仍然正常工作
        $result3 = $converter->convert('世界');
        $this->assertEquals(['shì', 'jiè'], $result3->toArray());
    }

    /**
     * 测试智能策略
     */
    public function test_smart_converter()
    {
        $converter = new SmartConverter;

        // 短文本
        $shortText = '你好';
        $result = $converter->convert($shortText);
        $this->assertEquals(['nǐ', 'hǎo'], $result->toArray());

        // 中等文本
        $mediumText = '带着希望去旅行，比到达终点更美好';
        $result = $converter->convert($mediumText);
        $this->assertInstanceOf(Collection::class, $result);

        // 长文本
        $longText = str_repeat('中华人民共和国', 20); // 140字
        $result = $converter->convert($longText);
        $this->assertInstanceOf(Collection::class, $result);

        // 测试内存信息
        $memoryInfo = $converter->getMemoryUsage();
        $this->assertEquals('smart', $memoryInfo['strategy']);
        $this->assertEquals('partial', $memoryInfo['persistent_cache']);
    }

    /**
     * 测试不同音调风格
     */
    public function test_tone_styles_across_strategies()
    {
        $strategies = [
            ConverterFactory::MEMORY_OPTIMIZED,
            ConverterFactory::CACHED,
            ConverterFactory::SMART,
        ];

        $input = '你好';
        $expectedSymbol = ['nǐ', 'hǎo'];
        $expectedNone = ['ni', 'hao'];
        $expectedNumber = ['ni3', 'hao3'];

        foreach ($strategies as $strategy) {
            $converter = ConverterFactory::make($strategy);

            // 符号音调（默认）
            $result = $converter->convert($input);
            $this->assertEquals($expectedSymbol, $result->toArray(),
                "Symbol tone failed for strategy: {$strategy}");

            // 无音调
            $result = $converter->noTone()->convert($input);
            $this->assertEquals($expectedNone, $result->toArray(),
                "No tone failed for strategy: {$strategy}");

            // 数字音调
            $converter = ConverterFactory::make($strategy); // 重新创建避免状态污染
            $result = $converter->useNumberTone()->convert($input);
            $this->assertEquals($expectedNumber, $result->toArray(),
                "Number tone failed for strategy: {$strategy}");
        }
    }

    /**
     * 测试姓氏处理
     */
    public function test_surname_handling_across_strategies()
    {
        $strategies = [
            ConverterFactory::MEMORY_OPTIMIZED,
            ConverterFactory::CACHED,
            ConverterFactory::SMART,
        ];

        foreach ($strategies as $strategy) {
            $converter = ConverterFactory::make($strategy);

            // 不使用姓氏模式
            $result = $converter->convert('单单单');
            $this->assertEquals(['dān', 'dān', 'dān'], $result->toArray());

            // 使用姓氏模式
            $converter = ConverterFactory::make($strategy);
            $result = $converter->surname()->convert('单单单');
            $this->assertEquals(['shàn', 'dān', 'dān'], $result->toArray());
        }
    }

    /**
     * 测试链式调用
     */
    public function test_method_chaining_across_strategies()
    {
        $strategies = [
            ConverterFactory::MEMORY_OPTIMIZED,
            ConverterFactory::CACHED,
            ConverterFactory::SMART,
        ];

        foreach ($strategies as $strategy) {
            $converter = ConverterFactory::make($strategy);

            $result = $converter
                ->noTone()
                ->noPunctuation()
                ->convert('你好，世界！');

            $this->assertEquals(['ni', 'hao', 'shi', 'jie'], $result->toArray());
        }
    }

    /**
     * 测试性能对比
     */
    public function test_performance_comparison()
    {
        $text = '带着希望去旅行，比到达终点更美好';
        $iterations = 100;

        $results = [];

        // 内存优化策略
        $converter = new MemoryOptimizedConverter;
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $converter->convert($text);
        }
        $results['memory'] = microtime(true) - $start;

        // 缓存策略
        $converter = new CachedConverter;
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $converter->convert($text);
        }
        $results['cached'] = microtime(true) - $start;

        // 智能策略
        $converter = new SmartConverter;
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $converter->convert($text);
        }
        $results['smart'] = microtime(true) - $start;

        // 缓存策略在重复调用时应该更快
        $this->assertLessThan($results['memory'], $results['cached'],
            'Cached strategy should be faster for repeated calls');

        // 记录性能数据（用于调试）
        foreach ($results as $strategy => $time) {
            $avgTime = round($time / $iterations * 1000, 3);
            // echo "\n{$strategy}: {$avgTime}ms per conversion";
        }
    }

    /**
     * 测试边界情况
     */
    public function test_edge_cases_across_strategies()
    {
        $strategies = [
            ConverterFactory::MEMORY_OPTIMIZED,
            ConverterFactory::CACHED,
            ConverterFactory::SMART,
        ];

        $edgeCases = [
            '' => [],                    // 空字符串
            '123' => ['123'],            // 纯数字
            'ABC' => ['ABC'],            // 纯英文
            '！@#' => [],                // 纯符号（被过滤）
            '你' => ['nǐ'],              // 单个汉字
            '你123好' => ['nǐ', '123', 'hǎo'], // 混合内容
        ];

        foreach ($strategies as $strategy) {
            foreach ($edgeCases as $input => $expected) {
                $converter = ConverterFactory::make($strategy);
                $result = $converter->convert($input);
                $this->assertEquals($expected, $result->toArray(),
                    "Edge case '{$input}' failed for strategy: {$strategy}");
            }
        }
    }

    /**
     * 测试并发安全性（静态缓存不应相互影响）
     */
    public function test_concurrent_safety()
    {
        // 创建两个缓存转换器
        $converter1 = new CachedConverter;
        $converter2 = new CachedConverter;

        // 分别设置不同的选项
        $result1 = $converter1->noTone()->convert('你好');
        $result2 = $converter2->useNumberTone()->convert('你好');

        // 结果应该不同
        $this->assertEquals(['ni', 'hao'], $result1->toArray());
        $this->assertEquals(['ni3', 'hao3'], $result2->toArray());

        // 确保选项不会相互影响
        $result3 = $converter1->convert('世界');
        $result4 = $converter2->convert('世界');

        $this->assertEquals(['shi', 'jie'], $result3->toArray());
        $this->assertEquals(['shi4', 'jie4'], $result4->toArray());
    }
}
