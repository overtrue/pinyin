<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 Seven Du <shiweidu@outlook.com>
 */

namespace Overtrue\Pinyin;

use Closure;
use Exception;
use SplFileObject;

/**
 * Generator syntax(yield) Dict File loader.
 */
class GeneratorFileDictLoader implements DictLoaderInterface
{
    /**
     * Data directory.
     *
     * @var string
     */
    protected $path;

    /**
     * Words segment name.
     *
     * @var string
     */
    protected $segmentName = 'words_%s';

    /**
     * SplFileObjects.
     *
     * @var array
     */
    protected $handles = [];

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;

        for ($i = 0; $i < 100; ++$i) {
            $segment = $this->path.'/'.sprintf($this->segmentName, $i);

            if (file_exists($segment) && is_file($segment)) {
                array_push($this->handles, $this->openFile($segment));
            }
        }
    }

    /**
     * Construct a new file object.
     *
     * @param string $filename file path
     * @return SplFileObject
     */
    protected function openFile($filename, $mode = 'r')
    {
        return new SplFileObject($filename, $mode);
    }

    /**
     * get Generator syntax.
     *
     * @param array $handles SplFileObjects
     */
    protected function getGenerator(array $handles)
    {
        foreach ($handles as $handle) {
            $handle->seek(0);
            while ($handle->eof() === false) {
                $string = str_replace(['\'', ' ', PHP_EOL, ','], '', $handle->fgets());

                if (strpos($string, '=>') === false) {
                    continue;
                }

                list($string, $pinyin) = explode('=>', $string);

                yield $string => $pinyin;
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
        foreach ($this->getGenerator($this->handles) as $string => $pinyin) {
            $callback([$string => $pinyin]);
        }
    }

    /**
     * Load surname dict.
     *
     * @param Closure $callback
     */
    public function mapSurname(Closure $callback)
    {
        $surnames = $this->path.'/surnames';
            
        foreach ($this->getGenerator([ $this->openFile($surnames) ]) as $string => $pinyin) {
            $callback([$string => $pinyin]);
        }
    }
}
