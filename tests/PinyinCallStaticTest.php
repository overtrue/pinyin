<?php

namespace Overtrue\Pinyin\Tests;

use InvalidArgumentException;
use Overtrue\Pinyin\Collection;
use Overtrue\Pinyin\Pinyin;
use Overtrue\Pinyin\ToneStyle;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ValueError;

class PinyinCallStaticTest extends TestCase
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
     * 测试有效的动态方法调用
     */
    public function test_valid_dynamic_method_calls()
    {
        // 测试surname方法
        $result = Pinyin::surname();
        $this->assertInstanceOf(Collection::class, $result->convert('欧阳'));
        $this->assertEquals(['ōu', 'yáng'], $result->convert('欧阳')->toArray());

        // 测试noWords方法
        $result = Pinyin::noWords();
        $this->assertInstanceOf(Collection::class, $result->convert('你好'));
        $this->assertEquals(['你' => 'nǐ', '好' => 'hǎo'], $result->convert('你好')->toArray());

        // 测试onlyHans方法
        $result = Pinyin::onlyHans();
        $this->assertInstanceOf(Collection::class, $result->convert('你好123'));
        $this->assertEquals(['nǐ', 'hǎo'], $result->convert('你好123')->toArray());

        // 测试noAlpha方法
        $result = Pinyin::noAlpha();
        $this->assertInstanceOf(Collection::class, $result->convert('你好ABC'));
        $this->assertEquals(['nǐ', 'hǎo'], $result->convert('你好ABC')->toArray());

        // 测试noNumber方法
        $result = Pinyin::noNumber();
        $this->assertInstanceOf(Collection::class, $result->convert('你好123'));
        $this->assertEquals(['nǐ', 'hǎo'], $result->convert('你好123')->toArray());

        // 测试noCleanup方法
        $result = Pinyin::noCleanup();
        $this->assertInstanceOf(Collection::class, $result->convert('你好！'));
        $this->assertEquals(['nǐ', 'hǎo', '！'], $result->convert('你好！')->toArray());

        // 测试noPunctuation方法
        $result = Pinyin::noPunctuation();
        $this->assertInstanceOf(Collection::class, $result->convert('你好，世界！'));
        $this->assertEquals(['nǐ', 'hǎo', 'shì', 'jiè'], $result->convert('你好，世界！')->toArray());

        // 测试noTone方法
        $result = Pinyin::noTone();
        $this->assertInstanceOf(Collection::class, $result->convert('你好'));
        $this->assertEquals(['ni', 'hao'], $result->convert('你好')->toArray());

        // 测试useNumberTone方法
        $result = Pinyin::useNumberTone();
        $this->assertInstanceOf(Collection::class, $result->convert('你好'));
        $this->assertEquals(['ni3', 'hao3'], $result->convert('你好')->toArray());

        // 测试yuToV方法
        $result = Pinyin::yuToV();
        $this->assertInstanceOf(Collection::class, $result->convert('吕'));
        $this->assertEquals(['lǚ'], $result->convert('吕')->toArray());

        // 测试yuToU方法
        $result = Pinyin::yuToU();
        $this->assertInstanceOf(Collection::class, $result->convert('吕'));
        $this->assertEquals(['lǚ'], $result->convert('吕')->toArray());

        // 测试withToneStyle方法
        $result = Pinyin::withToneStyle(ToneStyle::NONE);
        $this->assertInstanceOf(Collection::class, $result->convert('你好'));
        $this->assertEquals(['ni', 'hao'], $result->convert('你好')->toArray());

        // 测试withToneStyle方法（字符串参数）
        $result = Pinyin::withToneStyle('none');
        $this->assertInstanceOf(Collection::class, $result->convert('你好'));
        $this->assertEquals(['ni', 'hao'], $result->convert('你好')->toArray());
    }

    /**
     * 测试convert方法的动态调用
     */
    public function test_dynamic_convert_method()
    {
        $result = Pinyin::convert('你好世界');
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(['nǐ', 'hǎo', 'shì', 'jiè'], $result->toArray());
    }

    /**
     * 测试链式调用
     */
    public function test_dynamic_method_chaining()
    {
        $result = Pinyin::noTone()
            ->noPunctuation()
            ->convert('你好，世界！');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(['ni', 'hao', 'shi', 'jie'], $result->toArray());
    }

    /**
     * 测试复杂链式调用
     */
    public function test_complex_dynamic_method_chaining()
    {
        $result = Pinyin::surname()
            ->noTone()
            ->noPunctuation()
            ->convert('欧阳，你好！');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(['ou', 'yang', 'ni', 'hao'], $result->toArray());
    }

    /**
     * 测试无效的动态方法调用
     */
    public function test_invalid_dynamic_method_calls()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method invalidMethod does not exist.');

        Pinyin::invalidMethod();
    }

    /**
     * 测试不存在的convert方法调用
     */
    public function test_nonexistent_convert_method()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method convertWithInvalidParam does not exist.');

        Pinyin::convertWithInvalidParam('test');
    }

    /**
     * 测试空方法名
     */
    public function test_empty_method_name()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method  does not exist.');

        // 使用反射来测试空方法名的情况
        $reflection = new ReflectionClass(Pinyin::class);
        $method = $reflection->getMethod('__callStatic');
        $method->invokeArgs(null, ['', []]);
    }

    /**
     * 测试不同策略下的动态方法调用
     */
    public function test_dynamic_methods_with_different_strategies()
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

            // 测试noTone方法
            $result = Pinyin::noTone()->convert('你好');
            $this->assertEquals(['ni', 'hao'], $result->toArray(),
                "noTone() failed with {$name} strategy");

            // 测试surname方法
            $result = Pinyin::surname()->convert('欧阳');
            $this->assertEquals(['ōu', 'yáng'], $result->toArray(),
                "surname() failed with {$name} strategy");

            // 测试链式调用
            $result = Pinyin::surname()
                ->noTone()
                ->convert('欧阳');
            $this->assertEquals(['ou', 'yang'], $result->toArray(),
                "Chained methods failed with {$name} strategy");
        }
    }

    /**
     * 测试withToneStyle方法的参数验证
     */
    public function test_with_tone_style_parameter_validation()
    {
        // 测试有效的ToneStyle枚举
        $result = Pinyin::withToneStyle(ToneStyle::NUMBER)->convert('你好');
        $this->assertEquals(['ni3', 'hao3'], $result->toArray());

        // 测试有效的字符串
        $result = Pinyin::withToneStyle('number')->convert('你好');
        $this->assertEquals(['ni3', 'hao3'], $result->toArray());

        // 测试无效的字符串
        $this->expectException(ValueError::class);
        Pinyin::withToneStyle('invalid')->convert('你好');
    }

    /**
     * 测试heteronym方法的动态调用
     */
    public function test_dynamic_heteronym_method()
    {
        $result = Pinyin::heteronym('重庆');
        $this->assertInstanceOf(Collection::class, $result);
        $array = $result->toArray();
        $this->assertArrayHasKey('重', $array);
        $this->assertIsArray($array['重']);
        $this->assertContains('zhòng', $array['重']);
        $this->assertContains('chóng', $array['重']);
    }

    /**
     * 测试heteronym方法带参数
     */
    public function test_dynamic_heteronym_method_with_parameters()
    {
        $result = Pinyin::heteronym('重庆', ToneStyle::SYMBOL, true);
        $this->assertInstanceOf(Collection::class, $result);
        $array = $result->toArray();
        $this->assertIsArray($array);
        $this->assertCount(2, $array);
        $this->assertArrayHasKey('重', $array[0]);
        $this->assertArrayHasKey('庆', $array[1]);
    }

    /**
     * 测试when方法的动态调用
     */
    public function test_dynamic_when_method()
    {
        // 条件为true
        $result = Pinyin::when(true, function ($converter) {
            return $converter->noTone();
        })->convert('你好');
        $this->assertEquals(['ni', 'hao'], $result->toArray());

        // 条件为false
        $result = Pinyin::when(false, function ($converter) {
            return $converter->noTone();
        })->convert('你好');
        $this->assertEquals(['nǐ', 'hǎo'], $result->toArray());
    }

    /**
     * 测试性能 - 动态方法调用相对性能
     */
    public function test_performance_dynamic_method_calls()
    {
        $iterations = 100;
        $testText = '你好';

        // 测试直接调用（基准）
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Pinyin::sentence($testText);
        }
        $directTime = microtime(true) - $start;

        // 测试动态方法调用
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Pinyin::noTone()->convert($testText);
        }
        $dynamicTime = microtime(true) - $start;

        // 动态方法调用不应该比直接调用慢太多（最多3倍）
        $ratio = $dynamicTime / $directTime;
        $this->assertLessThan(3.0, $ratio, 
            "Dynamic method calls should not be significantly slower than direct calls. Ratio: {$ratio}");
        
        // 确保两种方法都能正常工作
        $this->assertEquals(['ni', 'hao'], Pinyin::noTone()->convert($testText)->toArray());
        $this->assertEquals('nǐ hǎo', Pinyin::sentence($testText));
    }

    /**
     * 测试内存使用 - 动态方法调用
     */
    public function test_memory_usage_dynamic_method_calls()
    {
        $initialMemory = memory_get_usage();

        for ($i = 0; $i < 100; $i++) {
            $result = Pinyin::noTone()->convert('你好');
            unset($result);
        }

        $memoryGrowth = memory_get_usage() - $initialMemory;
        $this->assertLessThan(1024 * 1024, $memoryGrowth, 'Dynamic method calls should not cause memory leaks');
    }

    /**
     * 测试边界情况
     */
    public function test_edge_cases_dynamic_methods()
    {
        // 测试空字符串
        $result = Pinyin::noTone()->convert('');
        $this->assertEquals([], $result->toArray());

        // 测试纯数字
        $result = Pinyin::noTone()->convert('123');
        $this->assertEquals(['123'], $result->toArray());

        // 测试纯英文
        $result = Pinyin::noTone()->convert('hello');
        $this->assertEquals(['hello'], $result->toArray());

        // 测试特殊字符
        $result = Pinyin::noCleanup()->convert('！@#');
        $this->assertEquals(['！@#'], $result->toArray());
    }
}
