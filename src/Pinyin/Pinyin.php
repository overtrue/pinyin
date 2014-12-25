<?php namespace  Overtrue\Pinyin;
/**
 * Pinyin.php
 *
 * @author Carlos <anzhengchao@gmail.com>
 * @date   [2014-07-17 15:49]
 */

/**
 * Chinese to pinyin translator
 *
 * @example
 * <pre>
 *      echo \Overtrue\Pinyin\Pinyin::pinyin('带着希望去旅行，比到达终点更美好'), "\n";
 *      //output: "dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo"
 * </pre>
 */
class Pinyin
{

    /**
     * dictionary path
     *
     * @var array
     */
    protected static $dictionary;

    /**
     * table of pinyin frequency.
     *
     * @var array
     */
    protected static $frequency;

    /**
     * appends dict
     *
     * @var array
     */
    protected static $appends = array();

    /**
     * settings
     *
     * @var array
     */
    protected static $settings = array(
                                  'delimiter'    => ' ',
                                  'traditional'  => false,
                                  'accent'       => true,
                                  'letter'       => false,
                                  'only_chinese' => false,
                                  'uppercase'    => false
                                 );

    /**
     * the instance
     *
     * @var \Overtrue\Pinyin\Pinyin
     */
    private static $_instance;

    /**
     * constructor
     *
     * set dictionary path.
     */
    private function __construct()
    {
        if (is_null(static::$dictionary)) {
            $this->loadDictionary();
        }
    }

    /**
     * disable clone
     *
     * @return void
     */
    private function __clone() {}

    /**
     * get class instance
     *
     * @return \Overtrue\Pinyin\Pinyin
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static;
        }

        return self::$_instance;
    }

    /**
     * setter
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function set($key, $value)
    {
        static::$settings[$key] = $value;
    }

    /**
     * setting.
     *
     * @param array $settings settings.
     *
     * @return void
     */
    public static function settings(array $settings = array())
    {
        static::$settings = array_merge(static::$settings, $settings);
    }

    /**
     * chinese to pinyin
     *
     * @param string $string  source string.
     * @param array  $settings settings.
     *
     * @return string
     */
    public static function pinyin($string, array $settings = array())
    {
        $parsed = self::parse($string, $settings);

        return $parsed['pinyin'];
    }

    /**
     * get first letters of chars
     *
     * @param string $string   source string.
     * @param string $settings settings
     *
     * @return string
     */
    public static function letter($string, array $settings = array())
    {
        $parsed = self::parse($string, $settings);

        return $parsed['letter'];
    }

    /**
     * parse the string to pinyin.
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

        $settings = array_merge(self::$settings, $settings);

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
                   'src'    => $string,
                   'pinyin' => $instance->escape($delimitedPinyin),
                   'letter' => $instance->getFirstLetters($source, $settings),
                  );

        return $return;
    }

    /**
     * 用户自定义补充
     *
     * @param array $appends
     *
     * @return void
     */
    public static function appends($appends = array())
    {
        static::$appends = array_merge(static::$appends, static::formatAdditionalWords($appends));
    }

    /**
     * get first letters from pinyin
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

        return join($settings['delimiter'], $letters);
    }

    /**
     * replace string to pinyin
     *
     * @param string $string
     *
     * @return string
     */
    protected function string2pinyin($string)
    {
        $string = $this->prepare($string);
        $pinyin = strtr($string, array_merge(static::$dictionary, $this->getAdditionalWords()));

        return trim(str_replace("  ", ' ', $pinyin));
    }

    /**
     * load dictionary content
     *
     * @return array
     */
    protected function loadDictionary()
    {
        $dictFile        = __DIR__ .'/data/dict.php';
        $ceditDictFile   = __DIR__ .'/data/cedict/cedict_ts.u8';

        if (!file_exists($dictFile)) {
            // parse and cache
            $dictionary = $this->parseDictionary($ceditDictFile);
            $this->cache($dictFile, $dictionary);

        // load from cache
        } else {
            $dictionary = $this->loadFromCache($dictFile);
        }

        return self::$dictionary = $dictionary;
    }

    /**
     * return additional words
     *
     * @return array
     */
    protected function getAdditionalWords()
    {
        static $additionalWords;

        if (empty($additionalWords)) {
            $additionalWords = include __DIR__ . '/data/additional.php';
            $additionalWords = static::formatAdditionalWords($additionalWords);
        }

        return array_merge($additionalWords, static::$appends);
    }

    /**
     * format users words
     *
     * @param array $additionalWords
     *
     * @return array
     */
    public static function formatAdditionalWords($additionalWords)
    {
        foreach ($additionalWords as $words => $pinyin) {
            $additionalWords[$words] = static::formatDictPinyin($pinyin);
        }

        return $additionalWords;
    }

    /**
     * parse the dict to php array
     *
     * @param string $dictionaryFile path of dictionary file.
     *
     * @return array
     */
    protected function parseDictionary($dictionaryFile)
    {
        static::$frequency = include __DIR__ . '/data/frequency.php';

        $handle = fopen($dictionaryFile, 'r');
        $regex = "#(?<trad>.*?) (?<simply>.*?) \[(?<pinyin>.*?)\]#i";

        $content = array();

        while ($line = fgets($handle)) {
            if (0 === stripos($line, '#')) {
                continue;
            }
            preg_match($regex, $line, $matches);

            if (empty($matches['trad']) || empty($matches['simply']) || empty($matches['pinyin'])) {
                continue;
            }

            $key = static::$settings['traditional'] ? trim($matches['trad']) : trim($matches['simply']);
            // frequency check
            if (empty($content[$key]) || $this->moreCommonly($matches['pinyin'], $content[$key])) {
               $content[$key] = static::formatDictPinyin($matches['pinyin']);
            }
        }

        return $content;
    }

    /**
     * format pinyin to lowercase.
     *
     * @param string $pinyin pinyin string.
     *
     * @return string
     */
    protected static function formatDictPinyin($pinyin)
    {
        return preg_replace_callback('/[A-Z][a-z]{1,}:?\d{1}/', function($matches){
            return strtolower($matches[0]);
        }, "{$pinyin} ");
    }

    /**
     * Frequency check
     *
     * @param string $new the pinyin with tone.
     * @param string $old the pinyin with tone.
     *
     * @return boolean
     */
    protected function moreCommonly($new, $old)
    {
        $new = trim($new);
        $old = trim($old);

        // contain space
        if (stripos($new, ' ') || $new == $old) {
            return false;
        }

        return isset(static::$frequency[$new])
            && isset(static::$frequency[$old])
            && static::$frequency[$new] > static::$frequency[$old];
    }


    /**
     * load dictionary from cached file
     *
     * @param string $dictionary cached file name
     *
     * @return array
     */
    protected function loadFromCache($dictionary)
    {
        return include $dictionary;
    }

    /**
     * write array to file
     *
     * @param string $filename  filename.
     * @param array  $array     parsed dictionary.
     *
     * @return false|null
     */
    protected function cache($filename, $array)
    {
        if (empty($array)) {
            return false;
        }

        file_put_contents($filename, "<?php\nreturn ".var_export($array, true).";") ;
    }

    /**
     * check if the string has Chinese chars
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
     * Remove the non-Chinese characters
     *
     * @param string $string source string.
     *
     * @return string
     */
    protected function justChinese($string)
    {
        return preg_replace('/[^\p{Han}]/u', '', $string);
    }

    /**
     * prepare the string.
     *
     * @param string $string source string.
     *
     * @return string
     */
    protected function prepare($string)
    {
        $pattern = array(
                '/([a-z])+(\d)/' => '\\1\\\2', // test4 => test\4
            );

        return preg_replace(array_keys($pattern), $pattern, $string);
    }

    /**
     * Credits for this function go to velcrow, who shared this
     * at http://stackoverflow.com/questions/1162491/alternative-to-mysql-real-escape-string-without-connecting-to-db
     *
     * @param string $value the string to  be escaped
     *
     * @return string the escaped string
     */
    protected function escape($value)
    {
        $search  = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $value);
    }

    /**
     * add delimiter
     *
     * @param string $string
     */
    protected function delimit($string, $delimiter = '')
    {
        return preg_replace('/\s+/', strval($delimiter), trim($string));
    }

    /**
     * remove tone
     *
     * @param string $string string with tone.
     *
     * @return string
     */
    protected function removeTone($string)
    {
        $replacement = array(
                        '/u:/' => 'u',
                        '/\d/' => '',
                       );

        return preg_replace(array_keys($replacement), $replacement, $string);
    }

    /**
     * Credits for these 2 functions go to Bouke Versteegh, who shared these
     * at http://stackoverflow.com/questions/1598856/convert-numbered-to-accentuated-pinyin
     *
     * @param string $string The pinyin string with tone numbers, i.e. "ni3 hao3"
     *
     * @return string The formatted string with tone marks, i.e.
     */
    protected function addAccents($string)
    {
        // find words with a number behind them, and replace with callback fn.
        return str_replace('u:', 'ü', preg_replace_callback(
            '~([a-zA-ZüÜ]+\:?)(\d)~',
            array($this, 'addAccentsCallback'),
            $string));
    }

    // helper callback
    protected function addAccentsCallback($match)
    {
        static $accentmap = null;

        if ($accentmap === null) {
            // where to place the accent marks
            $stars =
                    'a* e* i* o* u* ü* ü* ' .
                    'A* E* I* O* U* Ü* ' .
                    'a*i a*o e*i ia* ia*o ie* io* iu* ' .
                    'A*I A*O E*I IA* IA*O IE* IO* IU* ' .
                    'o*u ua* ua*i ue* ui* uo* üe* ' .
                    'O*U UA* UA*I UE* UI* UO* ÜE*';
            $nostars =
                    'a e i o u u: ü ' .
                    'A E I O U Ü ' .
                    'ai ao ei ia iao ie io iu ' .
                    'AI AO EI IA IAO IE IO IU ' .
                    'ou ua uai ue ui uo üe ' .
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
            5 => array('a', 'e', 'i', 'o', 'u', 'ü', 'A', 'E', 'I', 'O', 'U', 'Ü')
        );

        list(, $word, $tone) = $match;

        // add star to vowelcluster
        $word = strtr($word, $accentmap);

        // replace starred letter with accented
        $word = str_replace($vowels, $pinyin[$tone], $word);

        return $word;
    }

}// end of class
