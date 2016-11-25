<?php

use Overtrue\Pinyin\Pinyin;

class CustomAutoloadTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        include dirname(__DIR__).'/helpers.php';
    }

    public function testConst()
    {
        $this->assertSame(Pinyin::NONE, PINYIN_NONE);
        $this->assertSame(Pinyin::ASCII, PINYIN_ASCII);
        $this->assertSame(Pinyin::UNICODE, PINYIN_UNICODE);
    }
}
