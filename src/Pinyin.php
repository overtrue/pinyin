<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 overtrue <i@overtrue.me>
 */

namespace Overtrue\Pinyin;

/**
 * Chinese to pinyin translator.
 *
 * @author    overtrue <i@overtrue.me>
 * @copyright 2015 overtrue <i@overtrue.me>
 *
 * @link      https://github.com/overtrue/pinyin
 * @link      http://overtrue.me
 */
class Pinyin
{
    /**
     * Dict loader.
     *
     * @var \Overtrue\Pinyin\DictLoaderInterface
     */
    protected $loader;

    /**
     * Constructor.
     *
     * @param \Overtrue\Pinyin\DictLoaderInterface $loader
     */
    public function __construct(DictLoaderInterface $loader = null)
    {
        $this->loader = $loader;
    }

    public function convert($string)
    {
        # code...
    }

    public function permlink($string)
    {
        # code...
    }

    public function abbr($string)
    {
        # code...
    }

    public function sentence($sentence)
    {
        # code...
    }

    /**
     * Loader setter.
     *
     * @param \Overtrue\Pinyin\DictLoaderInterface $loader
     *
     * @return $this
     */
    public function setLoader(DictLoaderInterface $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Return dict loader,.
     *
     * @return \Overtrue\Pinyin\DictLoaderInterface
     */
    public function getLoader()
    {
        return $this->loader ?: new FileDictLoader(__DIR__.'/data/words.dat');
    }
}
