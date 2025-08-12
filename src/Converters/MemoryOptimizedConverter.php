<?php

namespace Overtrue\Pinyin\Converters;

use Overtrue\Pinyin\Collection;

/**
 * 内存优化版本的转换器
 *
 * 特点：
 * - 最小内存占用（峰值 ~400KB）
 * - 每次加载一个段，用完即释放
 * - 适合 Web 请求和内存受限环境
 */
class MemoryOptimizedConverter extends AbstractConverter
{
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

        // 按顺序加载词典段（长词优先）
        for ($i = 0; $i < self::SEGMENTS_COUNT; $i++) {
            $string = strtr($string, require sprintf(self::WORDS_PATH, $i));
        }

        return $this->split($string);
    }

    protected function convertAsChars(string $string, bool $polyphonic = false): Collection
    {
        $map = require self::CHARS_PATH;

        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        $items = [];

        foreach ($chars as $char) {
            if (isset($map[$char])) {
                if ($polyphonic) {
                    $pinyin = \array_map(fn ($pinyin) => $this->formatTone($pinyin, $this->toneStyle->value), $map[$char]);
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
        static $surnames = null;
        $surnames ??= require self::SURNAMES_PATH;

        foreach ($surnames as $surname => $pinyin) {
            if (\str_starts_with($name, $surname)) {
                return $pinyin.\mb_substr($name, \mb_strlen($surname));
            }
        }

        return $name;
    }

    public function getMemoryUsage(): array
    {
        return [
            'strategy' => 'memory_optimized',
            'peak_memory' => '~400KB',
            'persistent_cache' => false,
            'description' => '最小内存占用，每次加载一个段',
        ];
    }
}
