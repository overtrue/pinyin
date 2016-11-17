<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 overtrue <i@overtrue.me>
 */

namespace Overtrue\Pinyin;

use Closure;

/**
 * Dict File loader.
 */
class FileToMemoryDictLoader extends FileDictLoader implements DictLoaderInterface
{
    /**
     * Segment files
     *
     * @var array
     */
    protected $segments = array();

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        parent::__construct($path);
        for ($i = 0; $i < 100; ++$i) {
            $segment = $this->path . '/' . sprintf($this->segmentName, $i);
            if (file_exists($segment)) {
                $this->segments[] = (array) include $segment;
            }
        }
    }

    /**
     * Load dict.
     *
     * @param Closure $callback
     */
    public function map(Closure $callback)
    {
        foreach ($this->segments as $dictionary) {
            $callback($dictionary);
        }

    }
}
