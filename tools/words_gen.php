#!/usr/bin/env php
<?php

$savePath = __DIR__.'/../data/words.dat';
$polyphones = file(__DIR__ . '/sources/multi_readings_chars.txt');
$cceditWords = __DIR__ . '/sources/ccedit-words.txt';

if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    exit('PHP7 required.');
}

$fp = fopen($cceditWords, 'r');

$regexs = []; // 汉字 => /(pinyin1|pinyin2|...)/
foreach ($polyphones as $charLine) {
    list($han, $standardPinyin, $extendsPinyin) = explode(' ', trim($charLine));

    $regexs[$han] = '/('.str_replace(',', '|', $extendsPinyin).')/';
}

$output = [];

while (!feof($fp)) {
    $line = trim(fgets($fp));
    $lineReplaced = strtr($line, $regexs);

    list($words, $pinyin) = explode(',', $line);

    if ($lineReplaced !== $line) {
        $matchedChars = array_unique(array_filter(array_diff(utf8_str_split($line), utf8_str_split($lineReplaced))));

        // [
        //  '汉', '字'
        // ]
        foreach ($matchedChars as $char) {
            if (preg_match($regexs[$char], $pinyin)) {
                $output[] = sprintf('%s %s', $words, $pinyin);
            }
        }
    }
}


file_put_contents($savePath, join("\n", array_unique($output)));

echo count($output)." words saved in $savePath";

///////////////////////////////////////////////////////////////

function utf8_str_split($str, $split_len = 1)
{
    if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)
        return FALSE;

    $len = mb_strlen($str, 'UTF-8');
    if ($len <= $split_len)
        return array($str);

    preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);

    return $ar[0];
}