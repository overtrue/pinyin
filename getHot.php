<?php 
namespace test;
require_once __DIR__ . "/src/Pinyin/Pinyin.php";
use Overtrue\Pinyin\Pinyin;

echo Pinyin::trans("你好");
echo "\n";
echo Pinyin::trans("爱屋及乌");
echo "\n";

 ?>