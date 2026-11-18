<?php

namespace Overtrue\Pinyin\Tests;

use Overtrue\Pinyin\ConverterFactory;
use Overtrue\Pinyin\Pinyin;
use Overtrue\Pinyin\ToneStyle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NumberToneStyleTest extends TestCase
{
    #[DataProvider('numberToneProvider')]
    public function test_number_tone_for_umlaut_finals(string $input, string $yuTo, array $expectedSyllables): void
    {
        $converter = ConverterFactory::make('memory');
        $converter->useNumberTone();

        match ($yuTo) {
            'v' => $converter->yuToV(),
            'u' => $converter->yuToU(),
            'yu' => $converter->yuToYu(),
            default => throw new \InvalidArgumentException("Unknown yuTo: {$yuTo}"),
        };

        $syllables = $converter->convert($input)->toArray();

        $this->assertSame($expectedSyllables, $syllables);

        foreach ($syllables as $syllable) {
            $this->assertMatchesRegularExpression('/^[a-z]+[1-5]?$/', $syllable);
        }
    }

    public static function numberToneProvider(): array
    {
        return [
            ['略', 'v', ['lve4']],
            ['侵略', 'v', ['qin1', 'lve4']],
            ['虐待', 'v', ['nve4', 'dai4']],
            ['略', 'u', ['lue4']],
            ['略', 'yu', ['lyue4']],
            ['女', 'v', ['nv3']],
            ['女', 'u', ['nu3']],
            ['女', 'yu', ['nyu3']],
            ['旅行', 'v', ['lv3', 'xing2']],
            ['绿', 'v', ['lv4']],
        ];
    }

    public function test_number_tone_symbol_and_none_unchanged_for_umlaut_finals(): void
    {
        $this->assertSame(['lüè'], Pinyin::sentence('略', ToneStyle::SYMBOL)->toArray());
        $this->assertSame(['lve'], Pinyin::sentence('略', ToneStyle::NONE)->toArray());
        $this->assertSame(['lve4'], Pinyin::sentence('略', ToneStyle::NUMBER)->toArray());
    }
}
