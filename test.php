<?php
$start = microtime(true);
$startM = memory_get_usage();

include __DIR__ . '/src/Overtrue/Pinyin.php';

$str = "重庆";
var_dump(preg_replace('/[^\p{Han}]/u', '', $str));
$pingyin = new Overtrue\Pinyin;
echo '<pre>';
echo Overtrue\pinyin($str), "\n";
echo Overtrue\letter($str), "\n";
echo $pingyin->pinyin($str, ['letter' => true]), "\n";
Overtrue\Pinyin::set('only_chinese', true);
echo  Overtrue\Pinyin::pinyin($str), " after\n";
// echo  Overtrue\Pinyin::pinyin('重庆重度'), "\n";
// echo  Overtrue\Pinyin::pinyin('捞劳佬唠'), "\n";
// echo  Overtrue\Pinyin::pinyin('给力不？'), "\n";
// echo  Overtrue\Pinyin::pinyin('落下拉下'), "\n";
// echo  Overtrue\Pinyin::pinyin('惭愧'), "\n";
echo $pingyin->letter($str), "\n";
echo $pingyin->letter($str, ''), "\n";
echo $pingyin->letter($str, '-'), "\n";



echo microtime(true) - $start,"\n";
