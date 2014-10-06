<?php
$start = microtime(true);
$startM = memory_get_usage();

include __DIR__ . '/src/Overtrue/Pinyin.php';

$pingyin = new Overtrue\Pinyin;
echo  Overtrue\Pinyin::pinyin('重庆'), "\n";
echo  Overtrue\Pinyin::pinyin('了解来了'), "\n";
echo  Overtrue\Pinyin::pinyin('走了'), "\n";
echo  Overtrue\Pinyin::pinyin('康熙来了'), "\n";
echo  Overtrue\Pinyin::pinyin('了无生趣'), "\n";


echo microtime(true) - $start,"\n";
