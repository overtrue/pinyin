<?php namespace Overtrue\Pinyin;

use Config;
use Illuminate\Support\ServiceProvider;

/**
 * Pinyin Service Provider for Laravel 4
 */
class PinyinServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $config = Config::get('pinyin');

        Pinyin::settings($config);
    }
}