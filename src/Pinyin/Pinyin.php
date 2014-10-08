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
                                 );

    /**
     * the instance
     *
     * @var \Overtrue\Pinyin\Pinyin
     */
    protected static $instance;

    /**
     * constructor
     *
     * set dictionary path.
     */
    public function __construct()
    {
        if (is_null(self::$dictionary)) {
            self::$dictionary = $this->loadDictionary();
            self::$frequency  = include __DIR__ . '/data/frequency.php';
        }
    }

    /**
     * get class instance
     *
     * @return Overtrue\Pinyin
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * set.
     *
     * @param array $settings settings.
     */
    public static function set($key, $value)
    {
        self::$settings[$key] = $value;
    }

    /**
     * setting.
     *
     * @param array $settings settings.
     */
    public static function settings(array $settings = array())
    {
        self::$settings = array_merge(self::$settings, $settings);
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
        $instance = self::getInstance();

        $oldSettings = self::$settings;

        // merge setting
        empty($settings) || self::settings($settings);

        if (self::$settings['letter']) {
            self::settings($oldSettings);

            return self::letter($string);
        }

        // remove non-Chinese char.
        if (self::$settings['only_chinese']) {
            $string = $instance->onlyChinese($string);
        }

        $string = $instance->string2pinyin($string);

        // add delimiter
        $string = $instance->addDelimiter($string, self::$settings['delimiter']);

        self::settings($oldSettings);

        return $instance->escape($string);
    }

    /**
     * get first letters of chars
     *
     * @param string $string    source string.
     * @param string $delimiter delimiter for letters.
     *
     * @return string
     */
    public static function letter($string, $delimiter = null)
    {
        $instance = self::getInstance();

        $letters = array();

        for ($i = 0; $char = $instance->getChar($string, $i); $i++) {
            if ($letter = $instance->getCharFirstLetter($char)) {
                $letters[] = $letter;
            }
        }

        !is_null($delimiter) || $delimiter = self::$settings['delimiter'];

        return $instance->addDelimiter(join(' ', $letters), $delimiter);
    }

    /**
     * get char
     *
     * @param string  $string source string.
     * @param integer $offset offset.
     *
     * @return string
     */
    protected function getChar($string, $offset)
    {
        return mb_substr($string, $offset, 1, 'UTF-8');
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
        $pinyin = strtr($string, self::$dictionary);

        // add accents
        if (self::$settings['accent']) {
            $pinyin = $this->addAccents($pinyin);
        } else {
            $pinyin = $this->removeTone($pinyin);
        }

        return $pinyin;
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
        $additionalWords = $this->getAdditionalWords();

        // load from cache
        if (file_exists($dictFile)) {
            return $this->loadFromCache($dictFile);
        }

        // parse and cache
        $parsedDictionary = $this->parseDictionary($ceditDictFile);

        $dictionary = array_merge($parsedDictionary, $additionalWords);

        $this->cache($dictFile, $dictionary);

        return $dictionary;
    }

    /**
     * return additional words
     *
     * @return array
     */
    protected function getAdditionalWords()
    {
        $additionalWords = include __DIR__ . '/data/additional.php';

        foreach ($additionalWords as $words => $pinyin) {
            $additionalWords[$words] = $this->formatDictPinyin($pinyin);
        }

        return $additionalWords;
    }

    /**
     * parse the dict to php array
     *
     * @param string $dictionary path of dictionary file.
     *
     * @return array
     */
    protected function parseDictionary($dictionaryFile)
    {
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

            $key = self::$settings['traditional'] ? $matches['trad'] : $matches['simply'];

            // frequency check
            if (!isset($content[$key]) || $this->moreCommonly($matches['pinyin'], $content[$key])) {
               $content[$key] = $this->formatDictPinyin($matches['pinyin']);
            }
        }

        return $content;
    }

    /**
     * format pinyin to lowercase.
     *
     * @param string $pinyin pinyin string.
     *
     * @return string.
     */
    protected function formatDictPinyin($pinyin)
    {
        return preg_replace_callback('/[A-Z][a-z]{1,}:?\d{1}/', function($matches){
            return strtolower($matches[0]);
        }, "{$pinyin} ");
    }

    /**
     * Frequency check
     *
     * @param string $pinyin the pinyin with tone.
     *
     * @return boolean
     */
    protected function moreCommonly($pinyin, $target)
    {
        $pinyin = trim($pinyin);
        $target = trim($target);

        return isset(self::$frequency[$pinyin])
            && isset(self::$frequency[$target])
            && self::$frequency[$pinyin] > self::$frequency[$target];
    }

    /**
     * get first letter of char.
     *
     * @param string $string source string.
     *
     * @return string
     */
    protected function getCharFirstLetter($char)
    {
        if (empty($char) || !$this->containsChinese($char)) {
            return '';
        }

        $fchar = ord($char{0});

        if ($fchar >= ord('A') && $fchar <= ord('z')) {
            return strtoupper($char{0});
        }

        $s1 = iconv('UTF-8', 'gb2312', $char);
        $s2 = iconv('gb2312', 'UTF-8', $s1);

        $s = $s2 == $char ? $s1 : $str;

        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;

        if ($asc >= - 20319 && $asc <= - 20284) return 'A';
        if ($asc >= - 20283 && $asc <= - 19776) return 'B';
        if ($asc >= - 19775 && $asc <= - 19219) return 'C';
        if ($asc >= - 19218 && $asc <= - 18711) return 'D';
        if ($asc >= - 18710 && $asc <= - 18527) return 'E';
        if ($asc >= - 18526 && $asc <= - 18240) return 'F';
        if ($asc >= - 18239 && $asc <= - 17923) return 'G';
        if ($asc >= - 17922 && $asc <= - 17418) return 'H';
        if ($asc >= - 17417 && $asc <= - 16475) return 'J';
        if ($asc >= - 16474 && $asc <= - 16213) return 'K';
        if ($asc >= - 16212 && $asc <= - 15641) return 'L';
        if ($asc >= - 15640 && $asc <= - 15166) return 'M';
        if ($asc >= - 15165 && $asc <= - 14923) return 'N';
        if ($asc >= - 14922 && $asc <= - 14915) return 'O';
        if ($asc >= - 14914 && $asc <= - 14631) return 'P';
        if ($asc >= - 14630 && $asc <= - 14150) return 'Q';
        if ($asc >= - 14149 && $asc <= - 14091) return 'R';
        if ($asc >= - 14090 && $asc <= - 13319) return 'S';
        if ($asc >= - 13318 && $asc <= - 12839) return 'T';
        if ($asc >= - 12838 && $asc <= - 12557) return 'W';
        if ($asc >= - 12556 && $asc <= - 11848) return 'X';
        if ($asc >= - 11847 && $asc <= - 11056) return 'Y';
        if ($asc >= - 11055 && $asc <= - 10247) return 'Z';

        return null;
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
     * @return void
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
    protected function containsChinese($string)
    {
        return preg_match('/\p{Han}+/u', $string);
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
     * @param string $string the string to  be escaped
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
     * Remove the non-Chinese characters
     *
     * @param string $string source string.
     *
     * @return string
     */
    protected function onlyChinese($string)
    {
        return preg_replace('/[^\p{Han}]/u', '', $string);
    }

    /**
     * add delimiter
     *
     * @param string $string
     */
    protected function addDelimiter($string, $delimiter = '')
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
