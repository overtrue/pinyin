<?php

if (gethostname() == 'overtrue') {
    include __DIR__ . '/src/Pinyin/Pinyin.php';
} else {
    include __DIR__ . '/vendor/autoload.php';
}
