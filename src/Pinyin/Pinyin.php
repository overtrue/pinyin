<?php

/**
 * Pinyin.php.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    overtrue <i@overtrue.me>
 * @copyright 2015 overtrue <i@overtrue.me>
 *
 * @link      https://github.com/overtrue/pinyin
 * @link      http://overtrue.me
 */

namespace Overtrue\Pinyin;

/**
 * Chinese to pinyin translator.
 *
 * @example
 * <pre>
 *      echo \Overtrue\Pinyin\Pinyin::trans('带着希望去旅行，比到达终点更美好'), "\n";
 *      //output: "dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo"
 * </pre>
 */
class Pinyin
{
    /**
     * Dictionary.
     *
     * @var array
     */
    protected static $dictionary = array();

    /**
     * Settings.
     *
     * @var array
     */
    protected static $settings = array(
                                  'delimiter' => ' ',
                                  'accent' => true,
                                  'only_chinese' => false,
                                  'uppercase' => false,
                                  'charset' => 'UTF-8'  // GB2312,UTF-8
                                 );
    /**
     * Internal charset used by this package.
     *
     * @var string
     */
    protected static $internalCharset = 'UTF-8';

    /**
     * The instance.
     *
     * @var \Overtrue\Pinyin\Pinyin
     */
    private static $_instance;

    /**
     * Constructor.
     *
     * set dictionary path.
     */
    private function __construct()
    {
        $list = json_decode(file_get_contents(dirname(__DIR__).'/data/dict.php'), true);
        static::appends($list);
    }

    /**
     * Disable clone.
     */
    private function __clone()
    {
    }

    /**
     * Get class instance.
     *
     * @return \Overtrue\Pinyin\Pinyin
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    /**
     * Setter.
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function set($key, $value)
    {
        static::$settings[$key] = $value;
    }

    /**
     * Global settings.
     *
     * @param array $settings settings.
     */
    public static function settings(array $settings = array())
    {
        static::$settings = array_merge(static::$settings, $settings);
    }

    /**
     * Chinese to pinyin.
     *
     * @param string $string   source string.
     * @param array  $settings settings.
     *
     * @return string
     */
    public static function trans($string, array $settings = array())
    {
        $parsed = self::parse($string, $settings);

        return $parsed['pinyin'];
    }

    /**
     * Get first letters of string.
     *
     * @param string $string   source string.
     * @param string $settings settings
     *
     * @return string
     */
    public static function letter($string, array $settings = array())
    {
        $settings = array_merge($settings, array('accent' => false, 'only_chinese' => true));

        $parsed = self::parse($string, $settings);

        return $parsed['letter'];
    }

    /**
     * Parse the string to pinyin.
     *
     * Overtrue\Pinyin\Pinyin::parse('带着梦想旅行');
     *
     * @param string $string
     * @param array  $settings
     *
     * @return array
     */
    public static function parse($string, array $settings = array())
    {
        $instance = static::getInstance();
        $raw      = $string;

        $settings = array_merge(self::$settings, $settings);

        // add charset set
        if (!empty($settings['charset']) && $settings['charset'] != static::$internalCharset) {
            $string = iconv($settings['charset'], static::$internalCharset, $string);
        }

        // remove non-Chinese char.
        if ($settings['only_chinese']) {
            $string = $instance->justChinese($string);
        }

        $source = $instance->string2pinyin($string);

        // add accents
        if ($settings['accent']) {
            $pinyin = $instance->addAccents($source);
        } else {
            $pinyin = $instance->removeTone($source);
        }

        //add delimiter
        $delimitedPinyin = $instance->delimit($pinyin, $settings['delimiter']);

        $return = array(
                   'src' => $raw,
                   'pinyin' => stripslashes($delimitedPinyin),
                   'letter' => stripslashes($instance->getFirstLetters($source, $settings)),
                  );

        return $return;
    }

    /**
     * Add custom words.
     *
     * @param array $appends
     */
    public static function appends(array $appends)
    {
        $list = static::formatWords($appends);

        foreach ($list as $key => $value) {
            $firstChar = mb_substr($key, 0, 1, static::$internalCharset);
            self::$dictionary[$firstChar][$key] = $value;
        }
    }

    /**
     * Get first letters from pinyin.
     *
     * @param string $pinyin
     * @param array  $settings
     *
     * @return string
     */
    protected function getFirstLetters($pinyin, $settings)
    {
        $letterCase = $settings['uppercase'] ? 'strtoupper' : 'strtolower';

        $letters = array();

        foreach (explode(' ', $pinyin) as $word) {
            if (empty($word)) {
                continue;
            }

            $ord = ord(strtolower($word{0}));

            if ($ord >= 97 && $ord <= 122) {
                $letters[] = $letterCase($word{0});
            }
        }

        return implode($settings['delimiter'], $letters);
    }

    /**
     * Replace string to pinyin.
     *
     * @param string $string
     *
     * @return string
     */
    protected function string2pinyin($string)
    {
        $preparedString = $this->prepare($string);
        $count = mb_strlen($preparedString, static::$internalCharset);
        $dictionary = array();

        $i = 0;
        while ($i < $count) {
            $char = mb_substr($preparedString, $i++, 1, static::$internalCharset);
            $pinyinGroup = isset(self::$dictionary[$char]) ? self::$dictionary[$char] : array();
            $dictionary = array_merge($dictionary, $pinyinGroup);
        }

        $pinyin = strtr($preparedString, $dictionary);

        return trim(str_replace('  ', ' ', $pinyin));
    }

    /**
     * Format user's words.
     *
     * @param array $words
     *
     * @return array
     */
    public static function formatWords($words)
    {
        foreach ($words as $word => $pinyin) {
            $words[$word] = static::formatDictPinyin($pinyin);
        }

        return $words;
    }

    /**
     * Format pinyin to lowercase.
     *
     * @param string $pinyin pinyin string.
     *
     * @return string
     */
    protected static function formatDictPinyin($pinyin)
    {
        $pinyin = trim($pinyin);

        return preg_replace_callback('/[a-z]{1,}:?\d{1}\s?/i', function ($matches) {
            return strtolower($matches[0]);
        }, " {$pinyin} ");
    }

    /**
     * Check if the string has Chinese characters.
     *
     * @param string $string string to check.
     *
     * @return int
     */
    protected function containChinese($string)
    {
        return preg_match('/\p{Han}+/u', $string);
    }

    /**
     * Remove the non-Chinese characters.
     *
     * @param string $string source string.
     *
     * @return string
     */
    public function justChinese($string)
    {
        return preg_replace('/[^\p{Han}]/u', '', $string);
    }

    /**
     * Prepare the string.
     *
     * @param string $string source string.
     *
     * @return string
     */
    protected function prepare($string)
    {
        $pattern = array(
                '/([A-z])(\d)/' => '$1\\\\\2', // test4 => test\\4
            );

        return preg_replace(array_keys($pattern), $pattern, $string);
    }

    /**
     * Add delimiter.
     *
     * @param string $string
     * @param string $delimiter
     *
     * @return string
     */
    protected function delimit($string, $delimiter = '')
    {
        $defaultEncoding = mb_regex_encoding();
        mb_regex_encoding(static::$internalCharset);
        $string = mb_ereg_replace('\s+', strval($delimiter), trim($string));
        mb_regex_encoding($defaultEncoding);

        return $string;
    }

    /**
     * Remove tone.
     *
     * @param string $string string with tone.
     *
     * @return string
     */
    protected function removeTone($string)
    {
        $replacement = array(
                        '/u:/' => 'u',
                        '/([a-z])[1-5]/i' => '\\1',
                       );

        return preg_replace(array_keys($replacement), $replacement, $string);
    }

    /**
     * Credits for these 2 functions go to Bouke Versteegh, who shared these
     * at http://stackoverflow.com/questions/1598856/convert-numbered-to-accentuated-pinyin.
     *
     * @param string $string The pinyin string with tone numbers, i.e. "ni3 hao3"
     *
     * @return string The formatted string with tone marks, i.e.
     */
    protected function addAccents($string)
    {
        // find words with a number behind them, and replace with callback fn.
        return str_replace('u:', 'ü', preg_replace_callback(
            '~([a-zA-ZüÜ]+\:?)([1-5])~',
            array($this, 'addAccentsCallback'),
            $string));
    }

    /**
     * Helper callback.
     *
     * @param array $match
     */
    protected function addAccentsCallback($match)
    {
        static $accentmap = null;

        if ($accentmap === null) {
            // where to place the accent marks
            $stars = 'a* e* i* o* u* ü* ü* '.
                     'A* E* I* O* U* Ü* '.
                     'a*i a*o e*i ia* ia*o ie* io* iu* '.
                     'A*I A*O E*I IA* IA*O IE* IO* IU* '.
                     'o*u ua* ua*i ue* ui* uo* üe* '.
                     'O*U UA* UA*I UE* UI* UO* ÜE*';
            $nostars = 'a e i o u u: ü '.
                       'A E I O U Ü '.
                       'ai ao ei ia iao ie io iu '.
                       'AI AO EI IA IAO IE IO IU '.
                       'ou ua uai ue ui uo üe '.
                       'OU UA UAI UE UI UO ÜE';

            // build an array like array('a' => 'a*') and store statically
            $accentmap = array_combine(explode(' ', $nostars), explode(' ', $stars));
        }

        $vowels = array('a*', 'e*', 'i*', 'o*', 'u*', 'ü*', 'A*', 'E*', 'I*', 'O*', 'U*', 'Ü*');

        $pinyin = array(
            1 => array('ā', 'ē', 'ī', 'ō', 'ū', 'ǖ', 'Ā', 'Ē', 'Ī', 'Ō', 'Ū', 'Ǖ'),
            2 => array('á', 'é', 'í', 'ó', 'ú', 'ǘ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ǘ'),
            3 => array('ǎ', 'ě', 'ǐ', 'ǒ', 'ǔ', 'ǚ', 'Ǎ', 'Ě', 'Ǐ', 'Ǒ', 'Ǔ', 'Ǚ'),
            4 => array('à', 'è', 'ì', 'ò', 'ù', 'ǜ', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ǜ'),
            5 => array('a', 'e', 'i', 'o', 'u', 'ü', 'A', 'E', 'I', 'O', 'U', 'Ü'),
        );

        list(, $word, $tone) = $match;

        // add star to vowelcluster
        $word = strtr($word, $accentmap);

        // replace starred letter with accented
        $word = str_replace($vowels, $pinyin[$tone], $word);

        return $word;
    }
}//end class

