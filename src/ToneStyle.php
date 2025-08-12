<?php

namespace Overtrue\Pinyin;

/**
 * 拼音声调风格枚举
 */
enum ToneStyle: string
{
    /**
     * 符号风格：zhōng
     */
    case SYMBOL = 'symbol';

    /**
     * 数字风格：zhong1
     */
    case NUMBER = 'number';

    /**
     * 无声调：zhong
     */
    case NONE = 'none';
}
