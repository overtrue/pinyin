<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 Seven Du <shiweidu@outlook.com>
 */

/*
 * 不排除使用了composer还收到include这个文件的情况.
 * 比如直接跳过helpers.php
 */
if (!class_exists('Overtrue\\Pinyin\\Pinyin')) {
    /**
     * Base dir.
     *
     * @var string
     */
    $baseDir = dirname(__FILE__);

    /**
     * overtrue/pinyin Component class maps.
     *
     * @var array
     */
    $classmaps = array(
        'Overtrue\\Pinyin\\Pinyin'               => $baseDir.'/src/Pinyin.php',
        'Overtrue\\Pinyin\\DictLoaderInterface'  => $baseDir.'/src/DictLoaderInterface.php',
        'Overtrue\\Pinyin\\FileDictLoader'       => $baseDir.'/src/FileDictLoader.php',
        'Overtrue\\Pinyin\\MemoryFileDictLoader' => $baseDir.'/src/MemoryFileDictLoader.php',
    );

    if (function_exists('spl_autoload_register')) {
        spl_autoload_register(function ($className) use ($classmaps) {
            if (isset($classmaps[$className])) {
                include $classmaps[$className];
            }
        });
    }

}
