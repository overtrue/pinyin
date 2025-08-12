<?php

namespace Overtrue\Pinyin\Converters;

use Overtrue\Pinyin\Collection;

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
    private static ?array $commonWordsCache = null;
    private static array $segmentCache = [];
    private const MAX_CACHE_SEGMENTS = 3;

    public function convert(string $string): Collection
    {
        $string = $this->preprocessString($string);

        // 多音字
        if ($this->heteronym) {
            return $this->convertAsChars($string, true);
        }

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
        $textLength = mb_strlen($string);
        
        // 分析文本特征
        $startSegment = $this->analyzeTextComplexity($string, $textLength);
        
        // 短文本优化：使用缓存
        if ($textLength < 50 && $startSegment > 5) {
            return $this->convertWithCache($string, $startSegment);
        }
        
        // 标准处理
        for ($i = $startSegment; $i < self::SEGMENTS_COUNT; $i++) {
            $string = strtr($string, require sprintf(self::WORDS_PATH, $i));
        }
        
        return $string;
    }

    private function analyzeTextComplexity(string $text, int $length): int
    {
        // 超短文本（<10字）：跳过超长词典
        if ($length < 10) {
            return 6;
        }
        
        // 短文本（<30字）：跳过长词典
        if ($length < 30) {
            return 3;
        }
        
        // 中等文本（<100字）：跳过超长词典
        if ($length < 100) {
            return 1;
        }
        
        // 长文本：加载全部
        return 0;
    }

    private function convertWithCache(string $string, int $startSegment): string
    {
        // 缓存最近使用的几个段
        for ($i = $startSegment; $i < self::SEGMENTS_COUNT; $i++) {
            if (!isset(self::$segmentCache[$i])) {
                // 缓存数量限制
                if (count(self::$segmentCache) >= self::MAX_CACHE_SEGMENTS) {
                    // 移除最早的缓存（简单的FIFO）
                    array_shift(self::$segmentCache);
                }
                self::$segmentCache[$i] = require sprintf(self::WORDS_PATH, $i);
            }
            $string = strtr($string, self::$segmentCache[$i]);
        }
        
        return $string;
    }

    protected function convertAsChars(string $string, bool $polyphonic = false): Collection
    {
        // 字符表太大，不缓存
        $map = require self::CHARS_PATH;

        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        $items = [];
        
        foreach ($chars as $char) {
            if (isset($map[$char])) {
                if ($polyphonic) {
                    $pinyin = \array_map(fn ($pinyin) => $this->formatTone($pinyin, $this->toneStyle), $map[$char]);
                    if ($this->heteronymAsList) {
                        $items[] = [$char => $pinyin];
                    } else {
                        $items[$char] = $pinyin;
                    }
                } else {
                    $items[$char] = $this->formatTone($map[$char][0], $this->toneStyle);
                }
            }
        }

        return new Collection($items);
    }

    protected function convertSurname(string $name): string
    {
        // 姓氏表很小，可以缓存
        if (self::$surnamesCache === null) {
            self::$surnamesCache = require self::SURNAMES_PATH;
        }

        foreach (self::$surnamesCache as $surname => $pinyin) {
            if (\str_starts_with($name, $surname)) {
                return $pinyin.\mb_substr($name, \mb_strlen($surname));
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
        self::$commonWordsCache = null;
        self::$segmentCache = [];
    }

    public function getMemoryUsage(): array
    {
        $cacheCount = count(self::$segmentCache);
        $estimatedSize = $cacheCount * 200; // 每段约200KB
        
        return [
            'strategy' => 'smart',
            'peak_memory' => '~600KB-1.5MB',
            'persistent_cache' => 'partial',
            'cached_segments' => $cacheCount,
            'estimated_cache_size' => $estimatedSize . 'KB',
            'description' => '智能策略，根据文本特征优化',
        ];
    }
}