<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 overtrue <i@overtrue.me>
 */

namespace Overtrue\Pinyin;

/**
 * Dict File loader.
 */
class FileDictLoader implements DictLoaderInterface
{
    /**
     * Dict path.
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Load dict.
     *
     * @param string $path
     *
     * @return array
     */
    public function load($path)
    {
        return file($path);
    }
}
