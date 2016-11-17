<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * @author Garveen <acabin@live.com>
 */

use Overtrue\Pinyin\MemoryFileDictLoader;
use Overtrue\Pinyin\Pinyin;

class FileToMemoryDictLoaderTest extends AbstractDictLoaderTest
{
    protected function setUp()
    {
        $this->pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
    }
}
