<?php

namespace Overtrue\Pinyin;

use InvalidArgumentException;
use Overtrue\Pinyin\Contracts\ConverterInterface;
use Overtrue\Pinyin\Converters\CachedConverter;
use Overtrue\Pinyin\Converters\SmartConverter;

use function is_numeric;
use function mb_substr;
use function method_exists;

/**
 * @method static ConverterInterface surname()
 * @method static ConverterInterface noWords()
 * @method static ConverterInterface onlyHans()
 * @method static ConverterInterface noAlpha()
 * @method static ConverterInterface noNumber()
 * @method static ConverterInterface noCleanup()
 * @method static ConverterInterface noPunctuation()
 * @method static ConverterInterface noTone()
 * @method static ConverterInterface useNumberTone()
 * @method static ConverterInterface yuToV()
 * @method static ConverterInterface yuToU()
 * @method static ConverterInterface withToneStyle(string|ToneStyle $toneStyle = 'symbol')
 * @method static Collection convert(string $string, callable $beforeSplit = null)
 */
class Pinyin
{
    /**
     * 当前使用的转换策略
     */
    private static ?string $converterStrategy = null;

    public static function name(string $name, string|ToneStyle $toneStyle = ToneStyle::SYMBOL): Collection
    {
        return self::converter()->surname()->withToneStyle($toneStyle)->convert($name);
    }

    public static function passportName(string $name, string|ToneStyle $toneStyle = ToneStyle::NONE): Collection
    {
        return self::converter()->surname()->yuToYu()->withToneStyle($toneStyle)->convert($name);
    }

    public static function phrase(string $string, string|ToneStyle $toneStyle = ToneStyle::SYMBOL): Collection
    {
        return self::converter()->noPunctuation()->withToneStyle($toneStyle)->convert($string);
    }

    public static function sentence(string $string, string|ToneStyle $toneStyle = ToneStyle::SYMBOL): Collection
    {
        return self::converter()->withToneStyle($toneStyle)->convert($string);
    }

    public static function fullSentence(string $string, string|ToneStyle $toneStyle = ToneStyle::SYMBOL): Collection
    {
        return self::converter()->noCleanup()->withToneStyle($toneStyle)->convert($string);
    }

    public static function heteronym(string $string, string|ToneStyle $toneStyle = ToneStyle::SYMBOL, bool $asList = false): Collection
    {
        return self::converter()->heteronym($asList)->withToneStyle($toneStyle)->convert($string);
    }

    public static function heteronymAsList(string $string, string|ToneStyle $toneStyle = ToneStyle::SYMBOL): Collection
    {
        return self::heteronym($string, $toneStyle, true);
    }

    public static function chars(string $string, string|ToneStyle $toneStyle = ToneStyle::SYMBOL): Collection
    {
        return self::converter()->onlyHans()->noWords()->withToneStyle($toneStyle)->convert($string);
    }

    public static function permalink(string $string, string $delimiter = '-'): string
    {
        if (! in_array($delimiter, ['_', '-', '.', ''], true)) {
            throw new InvalidArgumentException("Delimiter must be one of: '_', '-', '', '.'.");
        }

        return self::converter()->noPunctuation()->noTone()->convert($string)->join($delimiter);
    }

    public static function nameAbbr(string $string): Collection
    {
        return self::abbr($string, true);
    }

    public static function abbr(string $string, bool $asName = false, bool $preserveEnglishWords = false): Collection
    {
        return self::converter()->noTone()
            ->noPunctuation()
            ->when($asName, fn ($c) => $c->surname())
            ->convert($string)
            ->map(function ($pinyin) use ($string, $preserveEnglishWords) {
                // 如果内容在原字符串中，则直接返回
                if ($preserveEnglishWords && str_contains($string, $pinyin)) {
                    return $pinyin;
                }

                // 常用于电影名称入库索引处理，例如：《晚娘2012》-> WN2012
                return is_numeric($pinyin) || preg_match('/\d{2,}/', $pinyin) ? $pinyin : mb_substr($pinyin, 0, 1);
            });
    }

    /**
     * 获取 Converter 实例
     *
     * @param  string|null  $strategy  指定策略，null 则使用默认策略
     */
    public static function converter(?string $strategy = null): ConverterInterface
    {
        // 使用新的工厂模式
        $strategy = $strategy ?? self::$converterStrategy;

        return ConverterFactory::make($strategy);
    }

    /**
     * 设置默认转换策略
     *
     * @param  string  $strategy  策略名称
     */
    public static function setConverterStrategy(string $strategy): void
    {
        self::$converterStrategy = $strategy;
        ConverterFactory::setDefaultStrategy($strategy);
    }

    /**
     * 使用内存优化策略
     */
    public static function useMemoryOptimized(): void
    {
        self::setConverterStrategy(ConverterFactory::MEMORY_OPTIMIZED);
    }

    /**
     * 使用缓存策略
     */
    public static function useCached(): void
    {
        self::setConverterStrategy(ConverterFactory::CACHED);
    }

    /**
     * 使用智能策略
     */
    public static function useSmart(): void
    {
        self::setConverterStrategy(ConverterFactory::SMART);
    }

    /**
     * 根据运行环境自动选择策略
     */
    public static function useAutoStrategy(): void
    {
        $strategy = ConverterFactory::recommend();
        self::setConverterStrategy($strategy);
    }

    /**
     * 清理所有转换器的缓存
     */
    public static function clearCache(): void
    {
        CachedConverter::clearCache();
        SmartConverter::clearCache();
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $converter = self::converter();

        if (method_exists($converter, $name)) {
            return $converter->$name(...$arguments);
        }

        throw new InvalidArgumentException("Method {$name} does not exist.");
    }
}
