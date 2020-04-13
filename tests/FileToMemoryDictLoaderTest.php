<?php

namespace Overtrue\Pinyin\Test;

use Overtrue\Pinyin\Pinyin;

class FileToMemoryDictLoaderTest extends AbstractDictLoaderTestCase
{
    protected function setUp(): void
    {
        $this->pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
    }
}
