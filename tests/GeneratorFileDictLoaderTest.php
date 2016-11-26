<?php

use Overtrue\Pinyin\Pinyin;

class GeneratorFileDictLoaderTest extends AbstractDictLoaderTest
{
    protected function setUp()
    {
        $this->pinyin = new Pinyin('Overtrue\\Pinyin\\GeneratorFileDictLoader');
    }
}
