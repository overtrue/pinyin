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

use Closure;
use Overtrue\Pinyin\DictLoaderInterface;
use Overtrue\Pinyin\Pinyin;
use PHPUnit_Framework_TestCase;

class PinyinTest extends PHPUnit_Framework_TestCase
{
    public function testLoaderSetter()
    {
        $pinyin = new Pinyin();

        $loader = new MockLoader();

        $pinyin->setLoader($loader);

        $this->assertSame($loader, $pinyin->getLoader());
        $this->assertSame('foo bar', $pinyin->sentence('你好'));
    }
}

/**
 * Mocker loader.
 */
class MockLoader implements DictLoaderInterface
{
    public function map(Closure $callback)
    {
        $dictionary = array(
                '你好' => "foo\tbar",
            );
        $callback($dictionary);
    }

    public function mapSurname(Closure $callback)
    {
        $dictionary = array(
                '单' => 'shan',
                '朴' => 'piao',
                '尉迟' => 'yu chi',
            );
        $callback($dictionary);
    }
}
