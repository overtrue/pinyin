<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
