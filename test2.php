<?php
$str = "dài 着梦想旅行";

preg_match('/\p{Han}{1}/u', $str, $matches);
var_dump($matches);