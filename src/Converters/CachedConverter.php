<?php

namespace Overtrue\Pinyin\Converters;

use Overtrue\Pinyin\Collection;

use function array_map;
use function mb_strlen;
use function mb_substr;
use function str_starts_with;

/**
 * 缓存版本的转换器
 *
 * 特点：
 * - 缓存所有词典数据
 * - 更快的重复转换速度
 * - 适合批处理和长时运行的进程
 * - 内存占用较高（~4MB）
 */
class CachedConverter extends AbstractConverter
{
    private static ?array $charsCache = null;

    private static ?array $surnamesCache = null;

    private static array $wordsCache = [];

    private static ?array $fullDictionary = null;

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

        // 使用缓存的完整词典
        $dictionary = $this->getFullDictionary();
        $string = strtr($string, $dictionary);

        return $this->split($string);
    }

    private function getFullDictionary(): array
    {
        if (self::$fullDictionary === null) {
            self::$fullDictionary = [];
            // 按顺序加载，保证长词优先
            for ($i = 0; $i < self::SEGMENTS_COUNT; $i++) {
                self::$fullDictionary += $this->loadWordsSegment($i);
            }
        }

        return self::$fullDictionary;
    }

    private function loadWordsSegment(int $index): array
    {
        if (! isset(self::$wordsCache[$index])) {
            self::$wordsCache[$index] = require sprintf(self::WORDS_PATH, $index);
        }

        return self::$wordsCache[$index];
    }

    protected function convertAsChars(string $string, bool $polyphonic = false): Collection
    {
        self::$charsCache ??= require self::CHARS_PATH;

        $chars = mb_str_split($string);
        $items = [];

        foreach ($chars as $char) {
            if (isset(self::$charsCache[$char])) {
                if ($polyphonic) {
                    $pinyin = array_map(fn ($pinyin) => $this->formatTone($pinyin, $this->toneStyle->value), self::$charsCache[$char]);
                    if ($this->heteronymAsList) {
                        $items[] = [$char => $pinyin];
                    } else {
                        $items[$char] = $pinyin;
                    }
                } else {
                    $items[$char] = $this->formatTone(self::$charsCache[$char][0], $this->toneStyle->value);
                }
            }
        }

        return new Collection($items);
    }

    protected function convertSurname(string $name): string
    {
        self::$surnamesCache ??= require self::SURNAMES_PATH;

        foreach (self::$surnamesCache as $surname => $pinyin) {
            if (str_starts_with($name, $surname)) {
                return $pinyin.mb_substr($name, mb_strlen($surname));
            }
        }

        return $name;
    }

    /**
     * 清理缓存（可选）
     */
    public static function clearCache(): void
    {
        self::$charsCache = null;
        self::$surnamesCache = null;
        self::$wordsCache = [];
        self::$fullDictionary = null;
    }
}
