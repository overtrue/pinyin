<?php

namespace Overtrue\Pinyin\Tests;

use Overtrue\Pinyin\ToneStyle;
use PHPUnit\Framework\TestCase;
use ValueError;

class ToneStyleTest extends TestCase
{
    /**
     * 测试枚举值
     */
    public function test_enum_values()
    {
        $this->assertEquals('symbol', ToneStyle::SYMBOL->value);
        $this->assertEquals('number', ToneStyle::NUMBER->value);
        $this->assertEquals('none', ToneStyle::NONE->value);
    }

    /**
     * 测试from方法 - 有效值
     */
    public function test_from_valid_values()
    {
        $this->assertEquals(ToneStyle::SYMBOL, ToneStyle::from('symbol'));
        $this->assertEquals(ToneStyle::NUMBER, ToneStyle::from('number'));
        $this->assertEquals(ToneStyle::NONE, ToneStyle::from('none'));
    }

    /**
     * 测试from方法 - 大小写不敏感
     */
    public function test_from_case_insensitive()
    {
        // PHP枚举默认是大小写敏感的，所以这些应该抛出异常
        $this->expectException(ValueError::class);
        ToneStyle::from('SYMBOL');
    }

    /**
     * 测试from方法 - 无效值
     */
    public function test_from_invalid_values()
    {
        $this->expectException(ValueError::class);
        ToneStyle::from('invalid');
    }

    /**
     * 测试from方法 - 空字符串
     */
    public function test_from_empty_string()
    {
        $this->expectException(ValueError::class);
        ToneStyle::from('');
    }

    /**
     * 测试from方法 - 数字
     */
    public function test_from_numeric()
    {
        $this->expectException(ValueError::class);
        ToneStyle::from(123);
    }

    /**
     * 测试tryFrom方法 - 有效值
     */
    public function test_try_from_valid_values()
    {
        $this->assertEquals(ToneStyle::SYMBOL, ToneStyle::tryFrom('symbol'));
        $this->assertEquals(ToneStyle::NUMBER, ToneStyle::tryFrom('number'));
        $this->assertEquals(ToneStyle::NONE, ToneStyle::tryFrom('none'));
    }

    /**
     * 测试tryFrom方法 - 无效值
     */
    public function test_try_from_invalid_values()
    {
        $this->assertNull(ToneStyle::tryFrom('invalid'));
        $this->assertNull(ToneStyle::tryFrom(''));
        $this->assertNull(ToneStyle::tryFrom(123));
    }

    /**
     * 测试cases方法
     */
    public function test_cases()
    {
        $cases = ToneStyle::cases();

        $this->assertIsArray($cases);
        $this->assertCount(3, $cases);
        $this->assertContains(ToneStyle::SYMBOL, $cases);
        $this->assertContains(ToneStyle::NUMBER, $cases);
        $this->assertContains(ToneStyle::NONE, $cases);
    }

    /**
     * 测试枚举比较
     */
    public function test_enum_comparison()
    {
        $this->assertTrue(ToneStyle::from('symbol') === ToneStyle::SYMBOL);
        $this->assertTrue(ToneStyle::from('number') === ToneStyle::NUMBER);
        $this->assertTrue(ToneStyle::from('none') === ToneStyle::NONE);

        $this->assertFalse(ToneStyle::SYMBOL === ToneStyle::NUMBER);
        $this->assertFalse(ToneStyle::NUMBER === ToneStyle::NONE);
        $this->assertFalse(ToneStyle::SYMBOL === ToneStyle::NONE);
    }

    /**
     * 测试枚举在switch语句中的使用
     */
    public function test_enum_in_switch()
    {
        $testCases = [
            'symbol' => ToneStyle::SYMBOL,
            'number' => ToneStyle::NUMBER,
            'none' => ToneStyle::NONE,
        ];

        foreach ($testCases as $expected => $toneStyle) {
            $result = match ($toneStyle) {
                ToneStyle::SYMBOL => 'symbol_style',
                ToneStyle::NUMBER => 'number_style',
                ToneStyle::NONE => 'none_style',
            };

            $this->assertEquals($expected.'_style', $result);
        }
    }

    /**
     * 测试枚举作为数组键
     */
    public function test_enum_as_array_key()
    {
        $map = [
            'symbol' => ToneStyle::SYMBOL,
            'number' => ToneStyle::NUMBER,
            'none' => ToneStyle::NONE,
        ];

        $this->assertEquals(ToneStyle::SYMBOL, $map['symbol']);
        $this->assertEquals(ToneStyle::NUMBER, $map['number']);
        $this->assertEquals(ToneStyle::NONE, $map['none']);
    }

    /**
     * 测试枚举序列化
     */
    public function test_enum_serialization()
    {
        $toneStyle = ToneStyle::SYMBOL;

        // 测试JSON序列化
        $json = json_encode($toneStyle);
        $this->assertEquals('"symbol"', $json);

        // 测试反序列化
        $decoded = json_decode($json, true);
        $this->assertEquals('symbol', $decoded);

        // 测试从JSON反序列化回枚举
        $restored = ToneStyle::from($decoded);
        $this->assertEquals($toneStyle, $restored);
    }

    /**
     * 测试枚举在函数参数中的使用
     */
    public function test_enum_as_function_parameter()
    {
        $this->assertEquals('symbol', $this->getToneStyleValue(ToneStyle::SYMBOL));
        $this->assertEquals('number', $this->getToneStyleValue(ToneStyle::NUMBER));
        $this->assertEquals('none', $this->getToneStyleValue(ToneStyle::NONE));
    }

    /**
     * 测试边界情况
     */
    public function test_edge_cases()
    {
        // 测试空格
        $this->expectException(ValueError::class);
        ToneStyle::from(' symbol ');

        // 测试特殊字符
        $this->expectException(ValueError::class);
        ToneStyle::from('symbol!');

        // 测试部分匹配
        $this->expectException(ValueError::class);
        ToneStyle::from('sym');
    }

    /**
     * 辅助方法：获取ToneStyle的值
     */
    private function getToneStyleValue(ToneStyle $toneStyle): string
    {
        return $toneStyle->value;
    }
}
