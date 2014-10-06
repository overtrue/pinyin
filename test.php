<?php
$start = microtime(true);
$startM = memory_get_usage();

include __DIR__ . '/src/Overtrue/Pinyin.php';

$pinyin = new Overtrue\Pinyin;
// $pinyin->set('delimiter', '-');
// $pinyin->set('accent', false);
echo  Overtrue\Pinyin::letter('您好'), "\n";
echo  Overtrue\Pinyin::pinyin('了解来了'), "\n";
echo  Overtrue\Pinyin::pinyin('走了'), "\n";
echo  Overtrue\Pinyin::pinyin('了了'), "\n";
echo  Overtrue\Pinyin::pinyin('康熙来了'), "\n";
echo  Overtrue\Pinyin::pinyin('了无生趣'), "\n";

echo microtime(true) - $start,"\n";
