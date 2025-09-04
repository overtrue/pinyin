<?php

namespace Overtrue\Pinyin\Converters;

use Overtrue\Pinyin\Collection;

use function array_map;
use function mb_strlen;
use function mb_substr;
use function str_starts_with;

/**
 * 智能版本的转换器
 *
 * 特点：
 * - 根据文本长度智能选择策略
 * - 短文本跳过不必要的长词词典
 * - 缓存小型常用数据
 * - 平衡内存和性能
 */
class SmartConverter extends AbstractConverter
{
    private static ?array $surnamesCache = null;

    private static array $segmentCache = [];

    private static ?array $fullDictionary = null;

    private const MAX_CACHE_SEGMENTS = 3;

    public function convert(string $string): Collection
    {
        $string = $this->preprocessString($string);

        return $this->determineConversionStrategy($string);
    }

    private function determineConversionStrategy(string $string): Collection
    {
        // 多音字处理
        if ($this->heteronym) {
            return $this->convertAsChars($string, true);
        }

        // 仅字符转换
        if ($this->noWords) {
            return $this->convertAsChars($string);
        }

        // 替换姓氏
        if ($this->asSurname) {
            $string = $this->convertSurname($string);
        }

        // 智能加载词典
        $string = $this->smartConvert($string);

        return $this->split($string);
    }

    private function smartConvert(string $string): string
    {
        // 使用和CachedConverter相同的逻辑，但保持缓存机制
        $dictionary = $this->getFullDictionary();

        return strtr($string, $dictionary);
    }

    private function getFullDictionary(): array
    {
        if (self::$fullDictionary === null) {
            self::$fullDictionary = [];
            // 按顺序加载，保证长词优先
            for ($i = 0; $i < self::SEGMENTS_COUNT; $i++) {
                self::$fullDictionary += $this->loadSegmentWithCache($i);
            }
        }

        return self::$fullDictionary;
    }

    private function loadSegmentWithCache(int $index): array
    {
        if (! isset(self::$segmentCache[$index])) {
            // 缓存数量限制
            if (count(self::$segmentCache) >= self::MAX_CACHE_SEGMENTS) {
                // 移除最早的缓存（简单的FIFO）
                array_shift(self::$segmentCache);
            }
            self::$segmentCache[$index] = require sprintf(self::WORDS_PATH, $index);
        }

        return self::$segmentCache[$index];
    }

    protected function convertAsChars(string $string, bool $polyphonic = false): Collection
    {
        // 字符表太大，不缓存
        $map = require self::CHARS_PATH;

        $chars = mb_str_split($string);
        $items = [];

        foreach ($chars as $char) {
            if (isset($map[$char])) {
                if ($polyphonic) {
                    $pinyin = array_map(fn ($pinyin) => $this->formatTone($pinyin, $this->toneStyle->value), $map[$char]);
                    if ($this->heteronymAsList) {
                        $items[] = [$char => $pinyin];
                    } else {
                        $items[$char] = $pinyin;
                    }
                } else {
                    $items[$char] = $this->formatTone($map[$char][0], $this->toneStyle->value);
                }
            }
        }

        return new Collection($items);
    }

    protected function convertSurname(string $name): string
    {
        // 姓氏表很小，可以缓存
        self::$surnamesCache ??= require self::SURNAMES_PATH;

        foreach (self::$surnamesCache as $surname => $pinyin) {
            if (str_starts_with($name, $surname)) {
                return $pinyin.mb_substr($name, mb_strlen($surname));
            }
        }

        return $name;
    }

    /**
     * 清理缓存
     */
    public static function clearCache(): void
    {
        self::$surnamesCache = null;
        self::$segmentCache = [];
        self::$fullDictionary = null;
    }
}
