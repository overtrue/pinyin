<?php

namespace Overtrue\Pinyin\Tests;

use InvalidArgumentException;
use Overtrue\Pinyin\Contracts\ConverterInterface;
use Overtrue\Pinyin\ConverterFactory;
use Overtrue\Pinyin\Converters\CachedConverter;
use Overtrue\Pinyin\Converters\MemoryOptimizedConverter;
use Overtrue\Pinyin\Converters\SmartConverter;
use PHPUnit\Framework\TestCase;

class ConverterFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        // 重置为默认策略
        ConverterFactory::setDefaultStrategy(ConverterFactory::MEMORY_OPTIMIZED);
    }

    public function test_make_memory_optimized_converter()
    {
        $converter = ConverterFactory::make(ConverterFactory::MEMORY_OPTIMIZED);

        $this->assertInstanceOf(ConverterInterface::class, $converter);
        $this->assertInstanceOf(MemoryOptimizedConverter::class, $converter);
    }

    public function test_make_cached_converter()
    {
        $converter = ConverterFactory::make(ConverterFactory::CACHED);

        $this->assertInstanceOf(ConverterInterface::class, $converter);
        $this->assertInstanceOf(CachedConverter::class, $converter);
    }

    public function test_make_smart_converter()
    {
        $converter = ConverterFactory::make(ConverterFactory::SMART);

        $this->assertInstanceOf(ConverterInterface::class, $converter);
        $this->assertInstanceOf(SmartConverter::class, $converter);
    }

    public function test_make_with_default_strategy()
    {
        // 默认应该是内存优化策略
        $converter = ConverterFactory::make();
        $this->assertInstanceOf(MemoryOptimizedConverter::class, $converter);

        // 改变默认策略
        ConverterFactory::setDefaultStrategy(ConverterFactory::CACHED);
        $converter = ConverterFactory::make();
        $this->assertInstanceOf(CachedConverter::class, $converter);
    }

    public function test_make_with_invalid_strategy()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown converter strategy: invalid');

        ConverterFactory::make('invalid');
    }

    public function test_set_default_strategy()
    {
        $this->assertEquals(ConverterFactory::MEMORY_OPTIMIZED, ConverterFactory::getDefaultStrategy());

        ConverterFactory::setDefaultStrategy(ConverterFactory::CACHED);
        $this->assertEquals(ConverterFactory::CACHED, ConverterFactory::getDefaultStrategy());

        ConverterFactory::setDefaultStrategy(ConverterFactory::SMART);
        $this->assertEquals(ConverterFactory::SMART, ConverterFactory::getDefaultStrategy());
    }

    public function test_set_invalid_default_strategy()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid strategy: invalid');

        ConverterFactory::setDefaultStrategy('invalid');
    }

    public function test_recommend_for_web_request()
    {
        $context = ['sapi' => 'fpm-fcgi'];
        $recommended = ConverterFactory::recommend($context);

        $this->assertEquals(ConverterFactory::MEMORY_OPTIMIZED, $recommended);
    }

    public function test_recommend_for_batch_processing()
    {
        $context = ['batch' => true];
        $recommended = ConverterFactory::recommend($context);

        $this->assertEquals(ConverterFactory::CACHED, $recommended);
    }

    public function test_recommend_for_cli_with_sufficient_memory()
    {
        $context = ['sapi' => 'cli', 'batch' => false];
        $recommended = ConverterFactory::recommend($context);

        // 应该推荐智能策略
        $this->assertEquals(ConverterFactory::SMART, $recommended);
    }

    public function test_get_strategies_info()
    {
        $info = ConverterFactory::getStrategiesInfo();

        $this->assertIsArray($info);
        $this->assertArrayHasKey(ConverterFactory::MEMORY_OPTIMIZED, $info);
        $this->assertArrayHasKey(ConverterFactory::CACHED, $info);
        $this->assertArrayHasKey(ConverterFactory::SMART, $info);

        foreach ($info as $strategy => $details) {
            $this->assertArrayHasKey('name', $details);
            $this->assertArrayHasKey('class', $details);
            $this->assertArrayHasKey('memory', $details);
            $this->assertArrayHasKey('speed', $details);
            $this->assertArrayHasKey('use_case', $details);
        }
    }

    public function test_strategies_are_reusable()
    {
        // 测试多次创建相同策略的转换器
        $converter1 = ConverterFactory::make(ConverterFactory::MEMORY_OPTIMIZED);
        $converter2 = ConverterFactory::make(ConverterFactory::MEMORY_OPTIMIZED);

        // 应该是不同的实例
        $this->assertNotSame($converter1, $converter2);

        // 但类型相同
        $this->assertEquals(get_class($converter1), get_class($converter2));
    }
}
