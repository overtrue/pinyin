<?php

namespace Overtrue\Pinyin;

use InvalidArgumentException;

/**
 * @method static Converter polyphonic()
 * @method static Converter surname()
 * @method static Converter noWords()
 * @method static Converter onlyHans()
 * @method static Converter noAlpha()
 * @method static Converter noNumber()
 * @method static Converter noPunctuation()
 * @method static Converter noTone()
 * @method static Converter useNumberTone()
 * @method static Converter yuToV()
 * @method static Converter yuToU()
 * @method static Converter withToneStyle(string $toneStyle = 'symbol')
 * @method static Collection convert(string $string, callable $beforeSplit = null)
 */
class Pinyin
{
    public static function name(string $name, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
    {
        return self::surname()->withToneStyle($toneStyle)->convert($name);
    }

    public static function phrase(string $string, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
    {
        return self::noPunctuation()->withToneStyle($toneStyle)->convert($string);
    }

    public static function sentence(string $string, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
    {
        return self::withToneStyle($toneStyle)->convert($string);
    }

    public static function polyphones(string $string, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
    {
        return self::polyphonic()->withToneStyle($toneStyle)->convert($string);
    }

    public static function chars(string $string, string $toneStyle = Converter::TONE_STYLE_SYMBOL): Collection
    {
        return self::onlyHans()->noWords()->withToneStyle($toneStyle)->convert($string);
    }

    public static function permalink(string $string, string $delimiter = '-'): string
    {
        if (!in_array($delimiter, ['_', '-', '.', ''], true)) {
            throw new InvalidArgumentException("Delimiter must be one of: '_', '-', '', '.'.");
        }

        return self::noPunctuation()->noTone()->convert($string)->join($delimiter);
    }

    public static function nameAbbr(string $string): Collection
    {
        return self::abbr($string, true);
    }

    public static function abbr(string $string, bool $asName = false): Collection
    {
        return self::noTone()
            ->noPunctuation()
            ->when($asName, fn ($c) => $c->surname())
            ->convert($string)
            ->map(function ($pinyin) {
                // 常用于电影名称入库索引处理，例如：《晚娘2012》-> WN2012
                return \is_numeric($pinyin) || preg_match('/\d{2,}/', $pinyin) ? $pinyin : \mb_substr($pinyin, 0, 1);
            });
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $converter = Converter::make();

        if (\method_exists($converter, $name)) {
            return $converter->$name(...$arguments);
        }

        throw new InvalidArgumentException("Method {$name} does not exist.");
    }
}
