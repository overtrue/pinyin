<?php

namespace Overtrue\Pinyin;

use InvalidArgumentException;

defined('PINYIN_DEFAULT') || define('PINYIN_DEFAULT', 4096);
defined('PINYIN_TONE') || define('PINYIN_TONE', 2);
defined('PINYIN_NO_TONE') || define('PINYIN_NO_TONE', 4);
defined('PINYIN_ASCII_TONE') || define('PINYIN_ASCII_TONE', 8);
defined('PINYIN_NAME') || define('PINYIN_NAME', 16);
defined('PINYIN_KEEP_NUMBER') || define('PINYIN_KEEP_NUMBER', 32);
defined('PINYIN_KEEP_ENGLISH') || define('PINYIN_KEEP_ENGLISH', 64);
defined('PINYIN_UMLAUT_V') || define('PINYIN_UMLAUT_V', 128);
defined('PINYIN_KEEP_PUNCTUATION') || define('PINYIN_KEEP_PUNCTUATION', 256);

class Pinyin
{
    private const SEGMENTS_COUNT = 10;
    private const WORDS_PATH = __DIR__.'/../data/words-%s.php';
    private const SURNAMES_PATH = __DIR__.'/../data/surnames.php';

    protected array $punctuations = [
        '，' => ',',
        '。' => '.',
        '！' => '!',
        '？' => '?',
        '：' => ':',
        '“' => '"',
        '”' => '"',
        '‘' => "'",
        '’' => "'",
        '_' => '_',
    ];

    public function __construct(protected int $defaultOptions = \PINYIN_DEFAULT)
    {
    }

    public function convert(string $string, int $option = null)
    {
        $option = $option ?? $this->defaultOptions;
        $pinyin = $this->transform($string, $option);

        return $this->splitPinyin($pinyin, $option);
    }

    public function name(string $name, int $option = null)
    {
        $option = ($option ?? $this->defaultOptions) | \PINYIN_NAME;

        $pinyin = $this->transform($name, $option);

        return $this->splitPinyin($pinyin, $option);
    }

    public function permalink(string $string, string $delimiter = '-', string $option = null)
    {
        $option = $option ?? $this->defaultOptions;

        if (\is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, '-'];
        }

        if (!in_array($delimiter, ['_', '-', '.', ''], true)) {
            throw new InvalidArgumentException("Delimiter must be one of: '_', '-', '', '.'.");
        }

        return implode($delimiter, $this->convert($string, $option | \PINYIN_KEEP_NUMBER | \PINYIN_KEEP_ENGLISH));
    }

    public function abbr(string $string, string $delimiter = '', int $option = null)
    {
        $option = $option ?? $this->defaultOptions;

        if (\is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ''];
        }

        return implode($delimiter, array_map(function ($pinyin) {
            return \is_numeric($pinyin) || preg_match('/\d+/', $pinyin) ? $pinyin : mb_substr($pinyin, 0, 1);
        }, $this->convert($string, $option | \PINYIN_NO_TONE)));
    }

    public function phrase(string $string, string $delimiter = ' ', string $option = null)
    {
        $option = $option ?? $this->defaultOptions;

        if (\is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ' '];
        }

        return implode($delimiter, $this->convert($string, $option));
    }

    public function sentence(string $string, string $delimiter = ' ', string $option = null)
    {
        $option = $option ?? $this->defaultOptions | \PINYIN_NO_TONE;

        if (\is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ' '];
        }

        return implode($delimiter, $this->convert($string, $option | \PINYIN_KEEP_PUNCTUATION | \PINYIN_KEEP_ENGLISH | \PINYIN_KEEP_NUMBER));
    }

    public function transform(string $string, string $option = null)
    {
        $option = $option ?? $this->defaultOptions;

        $string = $this->prepare($string, $option);

        if ($this->hasOption($option, \PINYIN_NAME)) {
            $string = $this->transformSurname($string);
        }

        for ($i = 0; $i < self::SEGMENTS_COUNT; $i++) {
            $string = strtr($string, require sprintf(self::WORDS_PATH, $i));
        }

        return $string;
    }

    protected function transformSurname(string $name)
    {
        $surnames = require self::SURNAMES_PATH;

        foreach ($dictionary as $surname => $pinyin) {
            if (\str_starts_with($name, $surname)) {
                return $pinyin . \mb_substr($name, \mb_strlen($surname));
            }
        }

        return $name;
    }

    protected function splitPinyin(string $pinyin, int $option)
    {
        $split = array_filter(preg_split('/\s+/i', $pinyin));

        if (!$this->hasOption($option, \PINYIN_TONE)) {
            foreach ($split as $index => $pinyin) {
                $split[$index] = $this->formatTone($pinyin, $option);
            }
        }

        return array_values($split);
    }

    protected function hasOption(int $option, int $check)
    {
        return ($option & $check) === $check;
    }

    protected function prepare(string $string, int $option)
    {
        $string = preg_replace_callback('~[a-z0-9_-]+~i', function ($matches) {
            return "\t" . $matches[0];
        }, $string);

        $regex = ['\p{Han}', '\p{Z}', '\p{M}', "\t"];

        if ($this->hasOption($option, \PINYIN_KEEP_NUMBER)) {
            \array_push($regex, '0-9');
        }

        if ($this->hasOption($option, \PINYIN_KEEP_ENGLISH)) {
            \array_push($regex, 'a-zA-Z');
        }

        if ($this->hasOption($option, \PINYIN_KEEP_PUNCTUATION)) {
            $punctuations = \array_merge($this->punctuations, ["\t" => ' ', '  ' => ' ']);
            $string = \trim(\str_replace(\array_keys($punctuations), $punctuations, $string));

            \array_push($regex, \preg_quote(\implode(\array_merge(\array_keys($this->punctuations), $this->punctuations)), '~'));
        }

        return \preg_replace(\sprintf('~[^%s]~u', \implode($regex)), '', $string);
    }

    protected function formatTone(string $pinyin, int $option = \PINYIN_NO_TONE)
    {
        $replacements = [
            'üē' => ['ue', 1], 'üé' => ['ue', 2], 'üě' => ['ue', 3], 'üè' => ['ue', 4],
            'ā' => ['a', 1], 'ē' => ['e', 1], 'ī' => ['i', 1], 'ō' => ['o', 1], 'ū' => ['u', 1], 'ǖ' => ['yu', 1],
            'á' => ['a', 2], 'é' => ['e', 2], 'í' => ['i', 2], 'ó' => ['o', 2], 'ú' => ['u', 2], 'ǘ' => ['yu', 2],
            'ǎ' => ['a', 3], 'ě' => ['e', 3], 'ǐ' => ['i', 3], 'ǒ' => ['o', 3], 'ǔ' => ['u', 3], 'ǚ' => ['yu', 3],
            'à' => ['a', 4], 'è' => ['e', 4], 'ì' => ['i', 4], 'ò' => ['o', 4], 'ù' => ['u', 4], 'ǜ' => ['yu', 4],
        ];

        foreach ($replacements as $unicode => $replacement) {
            if (false !== \strpos($pinyin, $unicode)) {
                $umlaut = $replacement[0];

                // https://zh.wikipedia.org/wiki/%C3%9C
                if ($this->hasOption($option, \PINYIN_UMLAUT_V) && 'yu' == $umlaut) {
                    $umlaut = 'v';
                }

                $pinyin = \str_replace($unicode, $umlaut, $pinyin) . ($this->hasOption($option, PINYIN_ASCII_TONE) ? $replacement[1] : '');
            }
        }

        return $pinyin;
    }
}
