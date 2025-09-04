<?php

namespace Overtrue\Pinyin\Tests;

use Overtrue\Pinyin\ConverterFactory;
use Overtrue\Pinyin\Pinyin;
use PHPUnit\Framework\TestCase;

class PinyinStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        // 重置为默认策略
        Pinyin::useMemoryOptimized();
    }

    protected function tearDown(): void
    {
        // 清理缓存
        Pinyin::clearCache();

        // 重置为默认策略
        Pinyin::useMemoryOptimized();
    }

    /**
     * 测试策略切换
     */
    public function test_strategy_switching()
    {
        // 默认应该是内存优化策略
        $this->assertEquals(
            ConverterFactory::MEMORY_OPTIMIZED,
            ConverterFactory::getDefaultStrategy()
        );

        // 切换到缓存策略
        Pinyin::useCached();
        $this->assertEquals(
            ConverterFactory::CACHED,
            ConverterFactory::getDefaultStrategy()
        );

        // 切换到智能策略
        Pinyin::useSmart();
        $this->assertEquals(
            ConverterFactory::SMART,
            ConverterFactory::getDefaultStrategy()
        );

        // 切换回内存优化策略
        Pinyin::useMemoryOptimized();
        $this->assertEquals(
            ConverterFactory::MEMORY_OPTIMIZED,
            ConverterFactory::getDefaultStrategy()
        );
    }

    /**
     * 测试不同策略下的 Pinyin 静态方法
     */
    public function test_pinyin_methods_with_different_strategies()
    {
        $strategies = [
            'memory' => function () {
                Pinyin::useMemoryOptimized();
            },
            'cached' => function () {
                Pinyin::useCached();
            },
            'smart' => function () {
                Pinyin::useSmart();
            },
        ];

        foreach ($strategies as $name => $setup) {
            $setup();

            // 测试 sentence
            $result = Pinyin::sentence('你好世界');
            $this->assertEquals('nǐ hǎo shì jiè', $result->join(' '),
                "sentence() failed with {$name} strategy");

            // 测试 phrase
            $result = Pinyin::phrase('你好，世界！');
            $this->assertEquals('nǐ hǎo shì jiè', $result->join(' '),
                "phrase() failed with {$name} strategy");

            // 测试 name
            $result = Pinyin::name('单田芳');
            $this->assertEquals('shàn tián fāng', $result->join(' '),
                "name() failed with {$name} strategy");

            // 测试 abbr
            $result = Pinyin::abbr('带着希望去旅行');
            $this->assertEquals(['d', 'z', 'x', 'w', 'q', 'l', 'x'], $result->toArray(),
                "abbr() failed with {$name} strategy");

            // 测试 permalink
            $result = Pinyin::permalink('带着希望去旅行');
            $this->assertEquals('dai-zhe-xi-wang-qu-lv-xing', $result,
                "permalink() failed with {$name} strategy");
        }
    }

    /**
     * 测试自动策略选择
     */
    public function test_auto_strategy()
    {
        Pinyin::useAutoStrategy();

        // 应该选择了一个有效的策略
        $validStrategies = [
            ConverterFactory::MEMORY_OPTIMIZED,
            ConverterFactory::CACHED,
            ConverterFactory::SMART,
        ];

        $this->assertContains(
            ConverterFactory::getDefaultStrategy(),
            $validStrategies
        );

        // 功能应该正常
        $result = Pinyin::sentence('你好世界');
        $this->assertEquals(['nǐ', 'hǎo', 'shì', 'jiè'], $result->toArray());
    }

    /**
     * 测试直接设置策略
     */
    public function test_set_converter_strategy()
    {
        Pinyin::setConverterStrategy(ConverterFactory::CACHED);
        $this->assertEquals(
            ConverterFactory::CACHED,
            ConverterFactory::getDefaultStrategy()
        );

        $result = Pinyin::sentence('你好');
        $this->assertEquals(['nǐ', 'hǎo'], $result->toArray());
    }

    /**
     * 测试策略切换后的性能差异
     */
    public function test_performance_difference_between_strategies()
    {
        $text = '中华人民共和国';
        $iterations = 50;

        // 内存优化策略
        Pinyin::useMemoryOptimized();
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Pinyin::sentence($text);
        }
        $memoryTime = microtime(true) - $start;

        // 缓存策略（预热）
        Pinyin::useCached();
        Pinyin::sentence($text); // 预热缓存

        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Pinyin::sentence($text);
        }
        $cachedTime = microtime(true) - $start;

        // 缓存策略应该更快
        $this->assertLessThan($memoryTime, $cachedTime,
            'Cached strategy should be faster for repeated conversions');
    }

    /**
     * 测试多音字在不同策略下的表现
     */
    public function test_heteronym_with_different_strategies()
    {
        $strategies = [
            'memory' => function () {
                Pinyin::useMemoryOptimized();
            },
            'cached' => function () {
                Pinyin::useCached();
            },
            'smart' => function () {
                Pinyin::useSmart();
            },
        ];

        foreach ($strategies as $name => $setup) {
            $setup();

            $result = Pinyin::heteronym('重庆');
            $array = $result->toArray();

            $this->assertArrayHasKey('重', $array,
                "heteronym() failed with {$name} strategy");
            $this->assertIsArray($array['重']);
            $this->assertContains('zhòng', $array['重']);
            $this->assertContains('chóng', $array['重']);
        }
    }

    /**
     * 测试链式调用在策略切换后的表现
     */
    public function test_method_chaining_after_strategy_switch()
    {
        // 测试内存优化策略
        Pinyin::useMemoryOptimized();
        $result = Pinyin::noTone()
            ->noPunctuation()
            ->convert('你好，世界！');
        $this->assertEquals(['ni', 'hao', 'shi', 'jie'], $result->toArray());

        // 切换到缓存策略
        Pinyin::useCached();
        $result = Pinyin::noTone()
            ->noPunctuation()
            ->convert('你好，世界！');
        $this->assertEquals(['ni', 'hao', 'shi', 'jie'], $result->toArray());

        // 切换到智能策略
        Pinyin::useSmart();
        $result = Pinyin::noTone()
            ->noPunctuation()
            ->convert('你好，世界！');
        $this->assertEquals(['ni', 'hao', 'shi', 'jie'], $result->toArray());
    }

    /**
     * 测试策略切换的独立性
     */
    public function test_strategy_independence()
    {
        // 使用缓存策略转换一些文本
        Pinyin::useCached();
        $result1 = Pinyin::sentence('你好');
        $this->assertEquals(['nǐ', 'hǎo'], $result1->toArray());

        // 切换到内存优化策略
        Pinyin::useMemoryOptimized();
        $result2 = Pinyin::sentence('世界');
        $this->assertEquals(['shì', 'jiè'], $result2->toArray());

        // 再切换回缓存策略，之前的缓存应该还在
        Pinyin::useCached();
        $result3 = Pinyin::sentence('你好');
        $this->assertEquals(['nǐ', 'hǎo'], $result3->toArray());
    }

    /**
     * 测试向后兼容性
     */
    public function test_backward_compatibility()
    {
        // 原有的使用方式应该仍然有效
        $converter = Pinyin::converter();
        $this->assertNotNull($converter);

        // 所有原有的静态方法应该正常工作
        $methods = [
            'sentence' => ['你好世界', ['nǐ', 'hǎo', 'shì', 'jiè']],
            'phrase' => ['你好世界', ['nǐ', 'hǎo', 'shì', 'jiè']],
            'chars' => ['你好', ['你' => 'nǐ', '好' => 'hǎo']],
        ];

        foreach ($methods as $method => $testCase) {
            [$input, $expected] = $testCase;
            $result = Pinyin::$method($input);
            $this->assertEquals($expected, $result->toArray(),
                "Method {$method} failed for backward compatibility");
        }
    }

    /**
     * 测试特殊字符和边界情况
     */
    public function test_edge_cases_with_strategies()
    {
        $edgeCases = [
            '' => [],
            '123' => ['123'],
            'ABC' => ['ABC'],
            '你好123ABC' => ['nǐ', 'hǎo', '123ABC'],
            '😀' => [],  // Emoji
        ];

        $strategies = [
            'memory' => function () {
                Pinyin::useMemoryOptimized();
            },
            'cached' => function () {
                Pinyin::useCached();
            },
            'smart' => function () {
                Pinyin::useSmart();
            },
        ];

        foreach ($strategies as $name => $setup) {
            $setup();

            foreach ($edgeCases as $input => $expected) {
                $result = Pinyin::sentence($input);
                $this->assertEquals($expected, $result->toArray(),
                    "Edge case '{$input}' failed with {$name} strategy");
            }
        }
    }
}
