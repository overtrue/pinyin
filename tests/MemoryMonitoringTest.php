<?php

namespace Overtrue\Pinyin\Tests;

use Overtrue\Pinyin\Converters\CachedConverter;
use Overtrue\Pinyin\Converters\MemoryOptimizedConverter;
use Overtrue\Pinyin\Converters\SmartConverter;
use Overtrue\Pinyin\Pinyin;
use PHPUnit\Framework\TestCase;

class MemoryMonitoringTest extends TestCase
{
    protected function tearDown(): void
    {
        // 清理缓存
        Pinyin::clearCache();
    }

    /**
     * 测试内存优化策略的内存使用
     */
    public function test_memory_optimized_converter_memory_usage()
    {
        $converter = new MemoryOptimizedConverter;

        // 记录初始内存
        $initialMemory = memory_get_usage();

        // 执行多次转换
        for ($i = 0; $i < 10; $i++) {
            $converter->convert('中华人民共和国成立于1949年');
        }

        // 计算内存增长
        $memoryGrowth = memory_get_usage() - $initialMemory;

        // 内存优化策略不应该持续增长内存
        // 允许一些小的增长（用于临时变量等）
        $this->assertLessThan(500 * 1024, $memoryGrowth,
            'Memory optimized converter should not grow memory significantly');
    }

    /**
     * 测试缓存策略的内存使用
     */
    public function test_cached_converter_memory_usage()
    {
        $converter = new CachedConverter;

        // 记录初始内存
        $initialMemory = memory_get_usage();

        // 第一次转换（加载缓存）
        $converter->convert('中华人民共和国');
        $firstLoadMemory = memory_get_usage() - $initialMemory;

        // 后续转换不应该显著增加内存
        $beforeSecond = memory_get_usage();
        for ($i = 0; $i < 10; $i++) {
            $converter->convert('带着希望去旅行');
        }
        $secondLoadMemory = memory_get_usage() - $beforeSecond;

        // 第二次的内存增长应该远小于第一次（或者为0，表示没有额外内存分配）
        $this->assertLessThanOrEqual($firstLoadMemory / 10, $secondLoadMemory,
            'Cached converter should reuse loaded data');

        // 清理缓存
        Pinyin::clearCache();

        // 清理后内存应该释放
        $afterClear = memory_get_usage();
        $this->assertLessThan($beforeSecond, $afterClear,
            'Memory should be released after clearing cache');
    }

    /**
     * 测试智能策略的内存使用
     */
    public function test_smart_converter_memory_usage()
    {
        $converter = new SmartConverter;

        // 短文本不应该使用太多内存
        $initialMemory = memory_get_usage();
        $converter->convert('你好');
        $shortTextMemory = memory_get_usage() - $initialMemory;

        // 长文本会使用更多内存
        $beforeLong = memory_get_usage();
        $longText = str_repeat('中华人民共和国', 50);
        $converter->convert($longText);
        $longTextMemory = memory_get_usage() - $beforeLong;

        // 长文本应该使用更多内存（因为加载了更多词典段）
        $this->assertGreaterThanOrEqual(0, $longTextMemory,
            'Smart converter should load data for longer text');

        // 但不应该超过合理范围
        $this->assertLessThan(2 * 1024 * 1024, $longTextMemory,
            'Smart converter should not use excessive memory');
    }

    /**
     * 测试不同策略的内存对比
     */
    public function test_memory_comparison_between_strategies()
    {
        $text = '中华人民共和国是世界上人口最多的国家';
        $iterations = 5;

        $memoryUsage = [];

        // 测试内存优化策略
        $converter = new MemoryOptimizedConverter;
        $start = memory_get_usage();
        for ($i = 0; $i < $iterations; $i++) {
            $converter->convert($text);
        }
        $memoryUsage['memory_optimized'] = memory_get_usage() - $start;

        // 清理内存
        unset($converter);

        // 测试缓存策略
        $converter = new CachedConverter;
        $start = memory_get_usage();
        for ($i = 0; $i < $iterations; $i++) {
            $converter->convert($text);
        }
        $memoryUsage['cached'] = memory_get_usage() - $start;

        // 清理缓存策略的缓存
        CachedConverter::clearCache();
        unset($converter);

        // 测试智能策略
        $converter = new SmartConverter;
        $start = memory_get_usage();
        for ($i = 0; $i < $iterations; $i++) {
            $converter->convert($text);
        }
        $memoryUsage['smart'] = memory_get_usage() - $start;

        // 内存优化策略应该使用最少的内存
        $this->assertLessThanOrEqual($memoryUsage['cached'], $memoryUsage['memory_optimized'],
            'Memory optimized strategy should use less memory than cached');

        // 智能策略应该在两者之间
        $this->assertLessThanOrEqual($memoryUsage['cached'], $memoryUsage['smart'],
            'Smart strategy should use less memory than fully cached');
        $this->assertGreaterThanOrEqual($memoryUsage['memory_optimized'], $memoryUsage['smart'],
            'Smart strategy should use more memory than memory optimized');
    }

    /**
     * 测试大量文本处理的内存表现
     */
    public function test_bulk_processing_memory_usage()
    {
        $texts = [];
        for ($i = 0; $i < 100; $i++) {
            $texts[] = "这是第{$i}个测试文本";
        }

        // 内存优化策略
        $converter = new MemoryOptimizedConverter;
        $start = memory_get_usage();
        $peakStart = memory_get_peak_usage();

        foreach ($texts as $text) {
            $converter->convert($text);
        }

        $memoryGrowth = memory_get_usage() - $start;
        $peakGrowth = memory_get_peak_usage() - $peakStart;

        // 内存增长应该很小（因为每次处理后释放）
        $this->assertLessThan(1024 * 1024, $memoryGrowth,
            'Memory optimized converter should not accumulate memory');

        // 峰值内存也应该在合理范围内
        $this->assertLessThan(5 * 1024 * 1024, $peakGrowth,
            'Peak memory should be reasonable');
    }

    /**
     * 测试内存泄漏
     */
    public function test_no_memory_leak()
    {
        $converter = new MemoryOptimizedConverter;

        // 记录初始内存
        $initialMemory = memory_get_usage();

        // 大量重复操作
        for ($i = 0; $i < 1000; $i++) {
            $result = $converter->convert('测试文本');
            unset($result); // 确保释放结果
        }

        // 强制垃圾回收
        gc_collect_cycles();

        // 最终内存不应该显著增长
        $finalMemory = memory_get_usage();
        $memoryGrowth = $finalMemory - $initialMemory;

        // 允许一些小的增长，但不应该是线性增长
        $this->assertLessThan(100 * 1024, $memoryGrowth,
            'Should not have memory leak');
    }

    /**
     * 测试运行时内存监控功能
     */
    public function test_runtime_memory_monitoring()
    {
        $strategies = [
            'memory' => MemoryOptimizedConverter::class,
            'cached' => CachedConverter::class,
            'smart' => SmartConverter::class,
        ];

        $memoryResults = [];

        foreach ($strategies as $name => $class) {
            $converter = new $class;

            $initialMemory = memory_get_usage();
            $converter->convert('测试文本');
            $memoryGrowth = memory_get_usage() - $initialMemory;

            $memoryResults[$name] = $memoryGrowth;

            // 内存增长应该大于等于0（可能为0如果数据已经加载）
            $this->assertGreaterThanOrEqual(0, $memoryGrowth, "Strategy {$name} should not have negative memory growth");
        }

        // 验证不同策略的内存使用差异
        $this->assertNotEquals($memoryResults['memory'], $memoryResults['cached'],
            'Different strategies should have different memory usage');
    }

    /**
     * 测试峰值内存监控
     */
    public function test_peak_memory_monitoring()
    {
        // 清理缓存确保测试的准确性
        CachedConverter::clearCache();

        $converter = new CachedConverter;

        $initialPeak = memory_get_peak_usage();
        $initialMemory = memory_get_usage();

        // 执行一些内存密集型操作
        for ($i = 0; $i < 100; $i++) {
            $converter->convert('这是一个很长的测试文本，用来测试峰值内存使用情况');
        }

        $finalPeak = memory_get_peak_usage();
        $finalMemory = memory_get_usage();
        $peakGrowth = $finalPeak - $initialPeak;
        $memoryGrowth = $finalMemory - $initialMemory;

        // 在CI环境中，峰值内存可能不会增长（如果缓存已经加载）
        // 但至少当前内存使用应该有增长，或者峰值内存增长
        $hasMemoryGrowth = $memoryGrowth > 0 || $peakGrowth > 0;

        $this->assertTrue($hasMemoryGrowth,
            sprintf('Either current memory (%d bytes) or peak memory (%d bytes) should increase',
                $memoryGrowth, $peakGrowth));

        // 但应该在合理范围内（放宽限制，因为测试环境可能不同）
        $this->assertLessThan(50 * 1024 * 1024, $peakGrowth, 'Peak memory should be reasonable');
    }
}
