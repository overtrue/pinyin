<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * @author Garveen <acabin@live.com>
 */

namespace Overtrue\Pinyin\Test;

use Overtrue\Pinyin\Pinyin;

class FileToMemoryDictLoaderTest extends AbstractDictLoaderTestCase
{
    protected function setUp()
    {
        $this->pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
    }
}
