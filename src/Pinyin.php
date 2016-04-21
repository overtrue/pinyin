<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 overtrue <i@overtrue.me>
 */

namespace Overtrue\Pinyin;

define('PINYIN_TONE_NONE', 'none');
define('PINYIN_TONE_ASCII', 'ascii');
define('PINYIN_TONE_UNICODE', 'unicode');

/**
 * Chinese to pinyin translator.
 *
 * @author    overtrue <i@overtrue.me>
 * @copyright 2015 overtrue <i@overtrue.me>
 *
 * @link      https://github.com/overtrue/pinyin
 * @link      http://overtrue.me
 */
class Pinyin
{
    const TONE_NONE = 'none';
    const TONE_ASCII = 'ascii';
    const TONE_UNICODE = 'unicode';

    /**
     * Dict loader.
     *
     * @var \Overtrue\Pinyin\DictLoaderInterface
     */
    protected $loader;

    /**
     * Punctuations map.
     *
     * @var array
     */
    protected $punctuations = array(
        '，' => ',',
        '。' => '.',
        '！' => '!',
        '？' => '?',
        '：' => ':',
        '“' => '"',
        '”' => '"',
        '‘' => "'",
        '’' => "'",
        "\t" => " ",
        "  " => " ",
    );

    /**
     * Constructor.
     *
     * @param \Overtrue\Pinyin\DictLoaderInterface $loader
     */
    public function __construct(DictLoaderInterface $loader = null)
    {
        $this->loader = $loader;
    }

    /**
     * Convert string to pinyin.
     *
     * @param string $string
     * @param string $option
     *
     * @return array
     */
    public function convert($string, $option = self::TONE_NONE)
    {
        $pinyin = $this->romanize($string);

        $split = array_filter(preg_split('/[^üāēīōūǖáéíóúǘǎěǐǒǔǚàèìòùǜa-z]+/u', $pinyin));

        if ($option !== self::TONE_UNICODE) {
            foreach ($split as $index => $pinyin) {
                $split[$index] = $this->format($pinyin, $option == self::TONE_ASCII);
            }
        }

        return $split;
    }

    /**
     * Return a pinyin permlink from string.
     *
     * @param string $string
     * @param string $delimiter
     *
     * @return string
     */
    public function permlink($string, $delimiter = '-')
    {
        return join($delimiter, $this->convert($string, self::TONE_NONE));
    }

    /**
     * Return first letters.
     *
     * @param string $string
     * @param string $delimiter
     *
     * @return string
     */
    public function abbr($string, $delimiter = '')
    {
        return join($delimiter, array_map(function($pinyin){
            return $pinyin[0];
        }, $this->convert($string, self::TONE_NONE)));
    }

    /**
     * Chinese to pinyin sentense.
     *
     * @param string $sentence
     *
     * @return string
     */
    public function sentence($sentence)
    {
        $marks = array_keys($this->punctuations);
        $regex = '/[^üāēīōūǖáéíóúǘǎěǐǒǔǚàèìòùǜa-z'.join($marks).'\s]+/u';

        return trim(str_replace($marks, $this->punctuations, preg_replace($regex, '', $this->romanize($sentence))));
    }

    /**
     * Loader setter.
     *
     * @param \Overtrue\Pinyin\DictLoaderInterface $loader
     *
     * @return $this
     */
    public function setLoader(DictLoaderInterface $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Return dict loader,.
     *
     * @return \Overtrue\Pinyin\DictLoaderInterface
     */
    public function getLoader()
    {
        return $this->loader ?: new FileDictLoader(__DIR__.'/../data/words.dat');
    }

    /**
     * Preprocess.
     *
     * @param string $string
     *
     * @return string
     */
    protected function prepare($string)
    {
        return preg_replace('~[^\p{Han}\p{P}\p{Z}\p{M}\p{N}\p{L}]~u', '', $string);
    }

    /**
     * Convert Chinese to pinyin.
     *
     * @param string $string
     *
     * @return string
     */
    protected function romanize($string)
    {
        $string = $this->prepare($string);

        $dictionary = $this->getLoader()->load();

        return strtr($string, $dictionary);
    }

    /**
     * Format.
     *
     * @param string $pinyin
     * @param bool   $tone
     *
     * @return string
     */
    protected function format($pinyin, $tone = false)
    {
        $replacements = [
            'üē' => ['ue', 1], 'üé' => ['ue', 2], 'üě' => ['ue', 3], 'üè' => ['ue', 4],
            'ā' => ['a', 1], 'ē' => ['e', 1], 'ī' => ['i', 1], 'ō' => ['o', 1], 'ū' => ['u', 1], 'ǖ' => ['v', 1],
            'á' => ['a', 2], 'é' => ['e', 2], 'í' => ['i', 2], 'ó' => ['o', 2], 'ú' => ['u', 2], 'ǘ' => ['v', 2],
            'ǎ' => ['a', 3], 'ě' => ['e', 3], 'ǐ' => ['i', 3], 'ǒ' => ['o', 3], 'ǔ' => ['u', 3], 'ǚ' => ['v', 3],
            'à' => ['a', 4], 'è' => ['e', 4], 'ì' => ['i', 4], 'ò' => ['o', 4], 'ù' => ['u', 4], 'ǜ' => ['v', 4]
        ];

        foreach ($replacements as $unicde => $replacements) {
            if (false !== strpos($pinyin, $unicde)) {
                $pinyin = str_replace($unicde, $replacements[0], $pinyin) . ($tone ? $replacements[1] : '');
            }
        }

        return $pinyin;
    }
}
