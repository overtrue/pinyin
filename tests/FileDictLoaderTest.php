<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * @author Garveen <acabin@live.com>
 */

use Overtrue\Pinyin\FileDictLoader;
use Overtrue\Pinyin\Pinyin;

class FileDictLoaderTest extends AbstractDictLoaderTest
{
    protected function setUp()
    {
        $this->pinyin = new Pinyin();
    }
}
