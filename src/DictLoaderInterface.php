<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 overtrue <i@overtrue.me>
 */

namespace Overtrue\Pinyin;

/**
 * Dict loader interface.
 */
interface DictLoaderInterface
{
    /**
     * Load dict.
     *
     * <pre>
     * [
     *     '响应时间 xiǎng yìng shí jiān',
     *     '长篇连载 cháng piān lián zǎi',
     *     //...
     * ]
     * </pre>
     *
     * @return array
     */
    public function load();
}
