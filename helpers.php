<?php

use Overtrue\Pinyin\Pinyin;

if (!defined('PINYIN_NONE')) {
    define('PINYIN_NONE', Pinyin::NONE);
}

if (!defined('PINYIN_ASCII')) {
    define('PINYIN_ASCII', Pinyin::ASCII);
}

if (!defined('PINYIN_UNICODE')) {
    define('PINYIN_UNICODE', Pinyin::UNICODE);
}
