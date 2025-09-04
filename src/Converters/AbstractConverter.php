<?php

namespace Overtrue\Pinyin\Converters;

use Overtrue\Pinyin\Collection;
use Overtrue\Pinyin\Contracts\ConverterInterface;
use Overtrue\Pinyin\ToneStyle;

use function array_values;
use function implode;
use function preg_replace;
use function sprintf;
use function str_contains;
use function str_replace;

abstract class AbstractConverter implements ConverterInterface
{
    protected const SEGMENTS_COUNT = 10;

    protected const WORDS_PATH = __DIR__.'/../../data/words-%s.php';

    protected const CHARS_PATH = __DIR__.'/../../data/chars.php';

    protected const SURNAMES_PATH = __DIR__.'/../../data/surnames.php';

    protected bool $heteronym = false;

    protected bool $heteronymAsList = false;

    protected bool $asSurname = false;

    protected bool $noWords = false;

    protected bool $cleanup = true;

    protected string $yuTo = 'v';

    protected ToneStyle $toneStyle = ToneStyle::SYMBOL;

    protected array $regexps = [
        'separator' => '\p{Z}',
        'mark' => '\p{M}',
        'tab' => "\t",
        'number' => '0-9',
        'alphabet' => 'a-zA-Z',
        'hans' => '\x{3007}\x{2E80}-\x{2FFF}\x{3100}-\x{312F}\x{31A0}-\x{31EF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}',
        'punctuation' => '\p{P}',
    ];

    abstract public function convert(string $string): Collection;

    public function heteronym(bool $asList = false): static
    {
        $this->heteronym = true;
        $this->heteronymAsList = $asList;

        return $this;
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
        $this->regexps['hans'] = '\x{3007}\x{2E80}-\x{2FFF}\x{3100}-\x{312F}\x{31A0}-\x{31EF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}';
        unset($this->regexps['alphabet']);
        unset($this->regexps['number']);
        unset($this->regexps['punctuation']);

        return $this;
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

    /**
     * 设置声调风格
     *
     * @param  string|ToneStyle  $toneStyle  声调风格，可以是字符串或 ToneStyle 枚举
     */
    public function withToneStyle(string|ToneStyle $toneStyle): static
    {
        $this->toneStyle = $toneStyle instanceof ToneStyle ? $toneStyle : ToneStyle::from($toneStyle);

        return $this;
    }

    public function noTone(): static
    {
        $this->toneStyle = ToneStyle::NONE;

        return $this;
    }

    public function useNumberTone(): static
    {
        $this->toneStyle = ToneStyle::NUMBER;

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

    public function yuToYu(): static
    {
        $this->yuTo = 'yu';

        return $this;
    }

    public function when(bool $condition, callable $callback): static
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    // 共享的辅助方法
    protected function preprocessString(string $string): string
    {
        // 把原有的数字和汉字分离，避免拼音转换时被误作声调
        $string = preg_replace_callback('~[a-z0-9_-]+~i', function ($matches) {
            return "\t".$matches[0];
        }, $string);

        // 过滤掉不保留的字符
        if ($this->cleanup) {
            $string = preg_replace(sprintf('~[^%s]~u', implode($this->regexps)), '', $string);
        }

        return $string;
    }

    protected function split(string $item): Collection
    {
        $items = array_values(array_filter(preg_split('/\s+/i', $item)));

        foreach ($items as $index => $item) {
            $items[$index] = $this->formatTone($item, $this->toneStyle->value);
        }

        return new Collection($items);
    }

    protected function formatTone(string $pinyin, string $style): string
    {
        if ($style === ToneStyle::SYMBOL->value) {
            return $pinyin;
        }

        // @formatter:off
        $replacements = [
            'ɑ' => ['a', 5], 'ü' => ['v', 5], 'üē' => ['ue', 1], 'üé' => ['ue', 2], 'üě' => ['ue', 3], 'üè' => ['ue', 4],
            'ā' => ['a', 1], 'ē' => ['e', 1], 'ī' => ['i', 1], 'ō' => ['o', 1], 'ū' => ['u', 1], 'ǖ' => ['v', 1],
            'á' => ['a', 2], 'é' => ['e', 2], 'í' => ['i', 2], 'ó' => ['o', 2], 'ú' => ['u', 2], 'ǘ' => ['v', 2],
            'ǎ' => ['a', 3], 'ě' => ['e', 3], 'ǐ' => ['i', 3], 'ǒ' => ['o', 3], 'ǔ' => ['u', 3], 'ǚ' => ['v', 3],
            'à' => ['a', 4], 'è' => ['e', 4], 'ì' => ['i', 4], 'ò' => ['o', 4], 'ù' => ['u', 4], 'ǜ' => ['v', 4],
        ];
        // @formatter:on

        foreach ($replacements as $unicode => $replacement) {
            if (str_contains($pinyin, $unicode)) {
                $umlaut = $replacement[0];

                if ($this->yuTo !== 'v' && $umlaut === 'v') {
                    $umlaut = $this->yuTo;
                }

                $pinyin = str_replace($unicode, $umlaut, $pinyin);

                if ($style === ToneStyle::NUMBER->value) {
                    $pinyin .= $replacement[1];
                }
            }
        }

        return $pinyin;
    }
}
