<?php

/**
 * Chinese to pinyin translator
 *
 * @author Joy <anzhengchao@gmail.com>
 *
 * @example
 * <pre>
 * 		$py = new Pinyin('./dict/cedict_ts.u8', array('delimiter' => '', 'accent' => false));
 *   	echo $py->trans('带着希望去旅行，比到达终点更美好');
 *   	//output: "dài zhe xī wàng qu luǚ xíng , bǐ dào dá zhōng diǎn gèng měi hǎo"
 * </pre>
 */
class Pinyin
{	

	/**
	 * dictionary path
	 *
	 * @var string
	 */
	protected $dictionary;

	/**
	 * settings
	 *
	 * @var array
	 */
	protected $setting = array(
						  'delimiter' => ' ',
					      'accent'    => true,
						 );


	/**
	 * set the dictionary.
	 *
	 * @param string $dictionary dictionary path.
	 */
	public function __construct($dictionary, array $setting = array())
	{
		if (!$dict = stream_resolve_include_path($dictionary)) {
			throw new Exception("Error Processing load dictionary '$dictionary'", 1);
		}

		$this->dictionary = $dictionary;
		$this->setting = array_merge($this->setting, $setting);
	}

	/**
	 * chinese to pinyin
	 *
	 * @param string $string source string.
	 *
	 * @return string
	 */
	public function trans($string)
	{
		$dictionary = $this->loadDictionary();
		
		foreach ($dictionary as $line) {
			$string = str_replace($line['simplified'], "{$line['pinyin_marks']} ", $string);
			if (!$this->containsChinese($string)) {
				break;
			}
		}

		// add accents
		if($this->setting['accent']) {
			$string = $this->pinyin_addaccents(strtolower($string));
		} else {
			$string = $this->removeTone($string);
		}

		// clean the string
		$string = $this->removeUnwantedCharacters($string);
		
		// add delimiter
		$string = $this->addDelimiter($string);

		return $this->escape($string);
	}

	/**
	 * load dictionary content
	 *
	 * @return array
	 */
	public function loadDictionary()
	{
		$cacheFilename = $this->getCacheFilename($this->dictionary);

		// load from cache
		if (file_exists($cacheFilename)) {
			return $this->loadFromCache($cacheFilename);
		}

		// parse and cache
		$parsedDictionary = $this->parseDictionary($this->dictionary);
		$this->cache($cacheFilename, $parsedDictionary);

		return $parsedDictionary;
	}

	/**
	 * get the filename of cache file.
	 *
	 * @param string $dictionary dictionary path.
	 *
	 * @return string
	 */
	protected function getCacheFilename($dictionary)
	{
		is_dir(__DIR__ .'/cache/') || mkdir(__DIR__ .'/cache/', 0755, true);

		return __DIR__ .'/cache/' . md5($dictionary);
	}

	/**
	 * parse the dict to php array
	 *
	 * @param string $dictionary path of dictionary file.
	 *
	 * @return array 
	 */
	protected function parseDictionary($dictionary)
	{
		//ini_set('memory_limit', '180M');
		$dictionary = file($dictionary);
		$regex = "#(.*?) (.*?) \[(.*?)\] \/(.*)\/#";

		$content = array();

		foreach ($dictionary as $entry) {
		    if (0 === stripos($entry, '#')) {
		        continue;
		    }

		    preg_match($regex, $entry, $matches);

		    $content[] = array(
						  //'traditional'    => $matches[1],
						  'simplified'     => $matches[2],
						  //'pinyin_numbers' => $matches[3],
						  'pinyin_marks'   => $matches[3],
						  //'translation'    => $this->escape($matches[4]),
				    	 );
		}

		// sort by simplified string length.
		usort($content, function($a, $b){
		    if (mb_strlen($a['simplified']) == mb_strlen($b['simplified'])) {
		        return 0;
		    }

		    return mb_strlen($a['simplified']) < mb_strlen($b['simplified']) ? 1 : -1;
		});

		return $content;
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
	 * @param array  $array 	parsed dictionary.
	 *
	 * @return void
	 */
	protected function cache($filename, $array)
	{
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
	 * Remove unwanted characters
	 *
	 * @param string $string 
	 */
	protected function removeUnwantedCharacters($string)	
	{
		$allowChars = ' a-zA-Z0-9āēīōǖǖĀĒĪŌŪǕáéíóǘǘÁÉÍÓÚǗǎěǐǒǚǚǍĚǏǑǓǙàèìòǜǜÀÈÌÒÙǛūúǔùüüÜ\p{Han}';
		$search = array(
					"/[^$allowChars]/u",
				  );

		return preg_replace($search, '', $string);
	}

	/**
	 * add delimiter
	 *
	 * @param string $string 
	 */
	protected function addDelimiter($string)	
	{
		return str_replace(array('  ', ' '), $this->setting['delimiter'], trim($string));
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
	protected function pinyin_addaccents($string)
	{
	    # Find words with a number behind them, and replace with callback fn.
	    return str_replace('u:', 'ü', preg_replace_callback(
	        '~([a-zA-ZüÜ]+\:?)(\d)~',
	        array($this, 'pinyin_addaccents_cb'),
	        $string));
	}

	# Helper callback
	protected function pinyin_addaccents_cb($match)
	{
	    static $accentmap = null;

	    if ($accentmap === null) {
	        # Where to place the accent marks
	        $stars =
	            	'a* e* i* o* u* ü* ' .
	                'A* E* I* O* U* Ü* ' .
	                'a*i a*o e*i ia* ia*o ie* io* iu* ' .
	                'A*I A*O E*I IA* IA*O IE* IO* IU* ' .
	                'o*u ua* ua*i ue* ui* uo* üe* ' .
	                'O*U UA* UA*I UE* UI* UO* ÜE*';
	        $nostars = 
	        		'a e i o u ü ' .
	                'A E I O U Ü ' .
	                'ai ao ei ia iao ie io iu ' .
	                'AI AO EI IA IAO IE IO IU ' .
	                'ou ua uai ue ui uo üe ' .
	                'OU UA UAI UE UI UO ÜE';

	        # Build an array like array('a' => 'a*') and store statically
	        $accentmap = array_combine(explode(' ', $nostars), explode(' ', $stars));
	    }

	    static $vowels = array('a*', 'e*', 'i*', 'o*', 'u*', 'ü*', 'A*', 'E*', 'I*', 'O*', 'U*', 'Ü*');

	    static $pinyin = array(
	        1 => array('ā', 'ē', 'ī', 'ō', 'ū',  'ǖ', 'Ā', 'Ē', 'Ī', 'Ō', 'Ū', 'Ǖ'),
	        2 => array('á', 'é', 'í', 'ó', 'ú',  'ǘ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ǘ'),
	        3 => array('ǎ', 'ě', 'ǐ', 'ǒ', 'ǔ', 'ǚ', 'Ǎ', 'Ě', 'Ǐ', 'Ǒ', 'Ǔ', 'Ǚ'),
	        4 => array('à', 'è', 'ì', 'ò', 'ù',  'ǜ', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ǜ'),
	        5 => array('a', 'e', 'i', 'o', 'u',  'ü', 'A', 'E', 'I', 'O', 'U', 'Ü')
	    );

	    list(, $word, $tone) = $match;
	    # Add star to vowelcluster
	    $word = strtr($word, $accentmap);
	    # Replace starred letter with accented
	    $word = str_replace($vowels, $pinyin[$tone], $word);
	    
	    return $word;
	}

}