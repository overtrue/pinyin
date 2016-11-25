<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 Seven Du <shiweidu@outlook.com>
 */

use Overtrue\Pinyin\Pinyin;

/**
 * Base dir.
 *
 * @var string
 */
$baseDir = dirname(__FILE__);

/*
 * 没有使用composer的情况下.
 */
if (!class_exists('Overtrue\\Pinyin\\Pinyin')) {
    $files = array(
        $baseDir.'/vendor/autoload.php',
        dirname(dirname($baseDir)).'/vendor/autoload.php',
        $baseDir.'/autoload.php',
    );

    foreach ($files as $filename) {
        if (file_exists($filename) && is_file($filename)) {
            require $filename;
            break;
        }
    }
}

if (!defined('PINYIN_NONE')) {
    define('PINYIN_NONE', Pinyin::NONE);
}

if (!defined('PINYIN_ASCII')) {
    define('PINYIN_ASCII', Pinyin::ASCII);
}

if (!defined('PINYIN_UNICODE')) {
    define('PINYIN_UNICODE', Pinyin::UNICODE);
}
