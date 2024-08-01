<?php

namespace Overtrue\Pinyin;

class Converter
{
    private const SEGMENTS_COUNT = 10;

    private const WORDS_PATH = __DIR__.'/../data/words-%s.php';

    private const CHARS_PATH = __DIR__.'/../data/chars.php';

    private const SURNAMES_PATH = __DIR__.'/../data/surnames.php';

    public const TONE_STYLE_SYMBOL = 'symbol';

    public const TONE_STYLE_NUMBER = 'number';

    public const TONE_STYLE_NONE = 'none';

    protected bool $heteronym = false;

    protected bool $heteronymAsList = false;

    protected bool $asSurname = false;

    protected bool $noWords = false;

    protected bool $cleanup = true;

    protected string $yuTo = 'v';

    protected string $toneStyle = self::TONE_STYLE_SYMBOL;

    protected array $regexps = [
        'separator' => '\p{Z}',
        'mark' => '\p{M}',
        'tab' => "\t",
    ];

    public const REGEXPS = [
        'number' => '0-9',
        'alphabet' => 'a-zA-Z',
        // 中文不带符号
        'hans' => '\x{3007}\x{2E80}-\x{2FFF}\x{3100}-\x{312F}\x{31A0}-\x{31EF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}',
        // 符号: !"#$%&'()*+,-./:;<=>?@[\]^_{|}~`
        'punctuation' => '\p{P}',
    ];

    public function __construct()
    {
        $this->regexps = \array_merge($this->regexps, self::REGEXPS);
    }

    public static function make(): static
    {
        return new static;
    }

    public function heteronym(bool $asList = false): static
    {
        $this->heteronym = true;
        $this->heteronymAsList = $asList;

        return $this;
    }

    /**
     * @deprecated Use `heteronym` instead.
     *             This method will be removed in the next major version.
     */
    public function polyphonic(bool $asList = false): static
    {
        return $this->heteronym($asList);
    }

    public function surname(): static
    {
        $this->asSurname = true;

        return $this;
    }

    public function noWords(): static
    {
        $this->noWords = true;

        return $this;
    }

    public function noCleanup(): static
    {
        $this->cleanup = false;

        return $this;
    }

    public function onlyHans(): static
    {
        // 中文汉字不含符号
        $this->regexps['hans'] = self::REGEXPS['hans'];

        return $this->noAlpha()->noNumber()->noPunctuation();
    }

    public function noAlpha(): static
    {
        unset($this->regexps['alphabet']);

        return $this;
    }

    public function noNumber(): static
    {
        unset($this->regexps['number']);

        return $this;
    }

    public function noPunctuation(): static
    {
        unset($this->regexps['punctuation']);

        return $this;
    }

    public function withToneStyle(string $toneStyle): static
    {
        $this->toneStyle = $toneStyle;

        return $this;
    }

    public function noTone(): static
    {
        $this->toneStyle = self::TONE_STYLE_NONE;

        return $this;
    }

    public function useNumberTone(): static
    {
        $this->toneStyle = self::TONE_STYLE_NUMBER;

        return $this;
    }

    public function yuToYu(): static
    {
        $this->yuTo = 'yu';

        return $this;
    }

    public function yuToV(): static
    {
        $this->yuTo = 'v';

        return $this;
    }

    public function yuToU(): static
    {
        $this->yuTo = 'u';

        return $this;
    }

    public function when(bool $condition, callable $callback): static
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    public function convert(string $string, ?callable $beforeSplit = null): Collection
    {
        // 把原有的数字和汉字分离，避免拼音转换时被误作声调
        $string = preg_replace_callback('~[a-z0-9_-]+~i', function ($matches) {
            return "\t".$matches[0];
        }, $string);

        // 过滤掉不保留的字符
        if ($this->cleanup) {
            $string = \preg_replace(\sprintf('~[^%s]~u', \implode($this->regexps)), '', $string);
        }

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

        for ($i = 0; $i < self::SEGMENTS_COUNT; $i++) {
            $string = strtr($string, require sprintf(self::WORDS_PATH, $i));
        }

        return $this->split($beforeSplit ? $beforeSplit($string) : $string);
    }

    public function convertAsChars(string $string, bool $polyphonic = false): Collection
    {
        $map = require self::CHARS_PATH;

        // split string as chinese chars
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
        static $surnames = null;
        $surnames ??= require self::SURNAMES_PATH;

        foreach ($surnames as $surname => $pinyin) {
            if (\str_starts_with($name, $surname)) {
                return $pinyin.\mb_substr($name, \mb_strlen($surname));
            }
        }

        return $name;
    }

    protected function split(string $item): Collection
    {
        $items = \array_values(array_filter(preg_split('/\s+/i', $item)));

        foreach ($items as $index => $item) {
            $items[$index] = $this->formatTone($item, $this->toneStyle);
        }

        return new Collection($items);
    }

    protected function formatTone(string $pinyin, string $style): string
    {
        if ($style === self::TONE_STYLE_SYMBOL) {
            return $pinyin;
        }

        $replacements = [
            // mb_chr(593) => 'ɑ' 轻声中除了 `ɑ` 和 `ü` 以外，其它和字母一样
            'ɑ' => ['a', 5], 'ü' => ['v', 5],
            'üē' => ['ue', 1], 'üé' => ['ue', 2], 'üě' => ['ue', 3], 'üè' => ['ue', 4],
            'ā' => ['a', 1], 'ē' => ['e', 1], 'ī' => ['i', 1], 'ō' => ['o', 1], 'ū' => ['u', 1], 'ǖ' => ['v', 1],
            'á' => ['a', 2], 'é' => ['e', 2], 'í' => ['i', 2], 'ó' => ['o', 2], 'ú' => ['u', 2], 'ǘ' => ['v', 2],
            'ǎ' => ['a', 3], 'ě' => ['e', 3], 'ǐ' => ['i', 3], 'ǒ' => ['o', 3], 'ǔ' => ['u', 3], 'ǚ' => ['v', 3],
            'à' => ['a', 4], 'è' => ['e', 4], 'ì' => ['i', 4], 'ò' => ['o', 4], 'ù' => ['u', 4], 'ǜ' => ['v', 4],
        ];

        foreach ($replacements as $unicode => $replacement) {
            if (\str_contains($pinyin, $unicode)) {
                $umlaut = $replacement[0];

                // https://zh.wikipedia.org/wiki/%C3%9C
                if ($this->yuTo !== 'v' && $umlaut === 'v') {
                    $umlaut = $this->yuTo;
                }

                $pinyin = \str_replace($unicode, $umlaut, $pinyin);

                if ($this->toneStyle === self::TONE_STYLE_NUMBER) {
                    $pinyin .= $replacement[1];
                }
            }
        }

        return $pinyin;
    }
}
