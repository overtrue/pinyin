<?php

namespace Overtrue\Pinyin;

use InvalidArgumentException;
use Overtrue\Pinyin\Contracts\ConverterInterface;
use Overtrue\Pinyin\Converters\CachedConverter;
use Overtrue\Pinyin\Converters\MemoryOptimizedConverter;
use Overtrue\Pinyin\Converters\SmartConverter;

/**
 * Converter 工厂类
 *
 * 提供不同策略的 Converter 实例
 */
class ConverterFactory
{
    public const MEMORY_OPTIMIZED = 'memory';

    public const CACHED = 'cached';

    public const SMART = 'smart';

    /**
     * 默认策略
     */
    private static string $defaultStrategy = self::MEMORY_OPTIMIZED;

    /**
     * 创建 Converter 实例
     *
     * @param  string|null  $strategy  策略名称，null 则使用默认策略
     */
    public static function make(?string $strategy = null): ConverterInterface
    {
        $strategy = $strategy ?? self::$defaultStrategy;

        return match ($strategy) {
            self::MEMORY_OPTIMIZED => new MemoryOptimizedConverter,
            self::CACHED => new CachedConverter,
            self::SMART => new SmartConverter,
            default => throw new InvalidArgumentException("Unknown converter strategy: {$strategy}")
        };
    }

    /**
     * 设置默认策略
     */
    public static function setDefaultStrategy(string $strategy): void
    {
        if (! in_array($strategy, [self::MEMORY_OPTIMIZED, self::CACHED, self::SMART])) {
            throw new InvalidArgumentException("Invalid strategy: {$strategy}");
        }

        self::$defaultStrategy = $strategy;
    }

    /**
     * 获取当前默认策略
     */
    public static function getDefaultStrategy(): string
    {
        return self::$defaultStrategy;
    }

    /**
     * 根据场景推荐策略
     *
     * @param  array  $context  场景上下文
     * @return string 推荐的策略
     */
    public static function recommend(array $context = []): string
    {
        // Web 请求场景
        if (($context['sapi'] ?? php_sapi_name()) === 'fpm-fcgi') {
            return self::MEMORY_OPTIMIZED;
        }

        // CLI 批处理场景
        if (($context['batch'] ?? false) === true) {
            return self::CACHED;
        }

        // 内存限制场景
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit !== '-1' && self::parseBytes($memoryLimit) < 128 * 1024 * 1024) {
            return self::MEMORY_OPTIMIZED;
        }

        // 默认使用智能策略
        return self::SMART;
    }

    /**
     * 获取所有可用策略的信息
     */
    public static function getStrategiesInfo(): array
    {
        return [
            self::MEMORY_OPTIMIZED => [
                'name' => '内存优化',
                'class' => MemoryOptimizedConverter::class,
                'memory' => '~400KB',
                'speed' => '中等',
                'use_case' => 'Web请求、内存受限环境',
            ],
            self::CACHED => [
                'name' => '全缓存',
                'class' => CachedConverter::class,
                'memory' => '~4MB',
                'speed' => '最快',
                'use_case' => '批处理、长时运行进程',
            ],
            self::SMART => [
                'name' => '智能',
                'class' => SmartConverter::class,
                'memory' => '600KB-1.5MB',
                'speed' => '快',
                'use_case' => '通用场景、自动优化',
            ],
        ];
    }

    private static function parseBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[-1]);
        $value = (int) $value;

        return match ($last) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
