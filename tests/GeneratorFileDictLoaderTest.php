<?php

namespace Overtrue\Pinyin\Test;

use Overtrue\Pinyin\Pinyin;

/**
 * Generator syntax(yield) Dict File loader test.
 *
 * @author Seven Du <shiweidu@outlook.com>
 */
class GeneratorFileDictLoaderTest extends AbstractDictLoaderTestCase
{
    protected function setUp()
    {
        $this->pinyin = new Pinyin('Overtrue\\Pinyin\\GeneratorFileDictLoader');
    }
}
