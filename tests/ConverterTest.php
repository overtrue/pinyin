<?php

namespace Overtrue\Pinyin\Tests;

use Overtrue\Pinyin\Collection;
use Overtrue\Pinyin\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    public function assertPinyin(array|string $expected, Collection $collection)
    {
        $this->assertEquals($expected, \is_array($expected) ? $collection->toArray() : $collection->join());
    }

    public function test_polyphonic()
    {
        $this->assertPinyin(['chóng', 'qìng'], Converter::make()->convert('重庆'));

        $this->assertPinyin([
            '重' => ['zhòng', 'chóng', 'tóng'],
            '庆' => ['qìng'],
        ], Converter::make()->polyphonic()->convert('重庆'));
    }

    public function test_surname()
    {
        $this->assertPinyin(['dān', 'dān', 'dān'], Converter::make()->convert('单单单'));
        $this->assertPinyin(['shàn', 'dān', 'dān'], Converter::make()->surname()->convert('单单单'));
    }

    public function test_chars()
    {
        $this->assertPinyin([
            '重' => 'zhòng', // 因为非多音字模式，所以已词频文件来决定拼音的顺序
            '庆' => 'qìng',
        ], Converter::make()->noWords()->convert('重庆'));
    }

    public function test_onlyHans()
    {
        $this->assertPinyin('dài zhe xī wàng qù lǚ xíng ， bǐ dào dá zhōng diǎn gèng měi hǎo', Converter::make()->convert('带着希望去旅行，比到达终点更美好'));
        $this->assertPinyin('dài zhe xī wàng qù lǚ xíng bǐ dào dá zhōng diǎn gèng měi hǎo', Converter::make()->onlyHans()->convert('带着希望去旅行，比到达终点更美好'));
    }

    public function test_noAlpha()
    {
        $this->assertPinyin('abc dài zhe xī def wàng qù lǚ xíng jkl', Converter::make()->convert('abc带着希def望去旅行jkl'));
        $this->assertPinyin('dài zhe xī wàng qù lǚ xíng', Converter::make()->noAlpha()->convert('abc带着希def望去旅行jkl'));
    }

    public function test_noNumber()
    {
        $this->assertPinyin('123 dài zhe xī 456 wàng qù lǚ xíng 789', Converter::make()->convert('123带着希456望去旅行789'));
        $this->assertPinyin('dài zhe xī wàng qù lǚ xíng', Converter::make()->noNumber()->convert('123带着希456望去旅行789'));
    }

    public function test_noPunctuation()
    {
        $this->assertPinyin('123 dài , zhe " xī wàng " qù lǚ xíng 789?', Converter::make()->convert('123带,着"希望"去旅行789?'));
        $this->assertPinyin('123 dài zhe xī 456 wàng qù lǚ xíng 789', Converter::make()->noPunctuation()->convert('123带着希456望去旅行789'));
    }

    public function test_tone_style()
    {
        $this->assertPinyin('chóng qìng', Converter::make()->convert('重庆'));
        $this->assertPinyin('chong qing', Converter::make()->noTone()->convert('重庆'));
        $this->assertPinyin('chong2 qing4', Converter::make()->useNumberTone()->convert('重庆'));

        $this->assertPinyin('chóng qìng', Converter::make()->withToneStyle(Converter::TONE_STYLE_SYMBOL)->convert('重庆'));
        $this->assertPinyin('chong qing', Converter::make()->withToneStyle(Converter::TONE_STYLE_NONE)->convert('重庆'));
        $this->assertPinyin('chong2 qing4', Converter::make()->withToneStyle(Converter::TONE_STYLE_NUMBER)->convert('重庆'));
    }

    public function test_yu()
    {
        $this->assertPinyin('lǚ xíng', Converter::make()->convert('旅行'));
        $this->assertPinyin('lyu xing', Converter::make()->yuToYu()->noTone()->convert('旅行'));
        $this->assertPinyin('lv xing', Converter::make()->yuToV()->noTone()->convert('旅行'));
        $this->assertPinyin('lu xing', Converter::make()->yuToU()->noTone()->convert('旅行'));
    }

    public function test_when()
    {
        $this->assertPinyin('chóng qìng', Converter::make()->convert('重庆'));
        $this->assertPinyin('chóng qìng', Converter::make()->when(false, fn ($converter) => $converter->noTone())->convert('重庆'));
        $this->assertPinyin('chong2 qing4', Converter::make()->when(true, fn ($converter) => $converter->useNumberTone())->convert('重庆'));
    }
}
