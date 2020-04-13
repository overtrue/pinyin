<?php

namespace Overtrue\Pinyin\Test;

use Overtrue\Pinyin\Pinyin;

class FileDictLoaderTest extends AbstractDictLoaderTestCase
{
    protected function setUp(): void
    {
        $this->pinyin = new Pinyin();
    }
}
