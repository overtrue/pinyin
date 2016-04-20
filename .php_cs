<?php

$year = date('Y');

$header = <<<EOF
This file is part of the overtrue/pinyin.

(c) $year overtrue <i@overtrue.me>
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    // use default SYMFONY_LEVEL and extra fixers:
    ->fixers(array(
        'header_comment',
        'long_array_syntax',
        'ordered_use',
        'strict',
        'strict_param',
        'phpdoc_order',
        'php4_constructor',
    ))
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('vendor')
            ->in(__DIR__.'/src')
    )
;