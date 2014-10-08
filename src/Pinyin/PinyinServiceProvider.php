<?php namespace Overtrue\Pinyin;

use Illuminate\Support\ServiceProvider;

/**
 * Pinyin Service Provider for Laravel
 */
class PinyinServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $config = config('pinyin');

        Pinyin::settings($config);
    }
}
