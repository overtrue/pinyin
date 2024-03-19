# Pinyin

[![Test](https://github.com/overtrue/pinyin/actions/workflows/test.yml/badge.svg)](https://github.com/overtrue/pinyin/actions/workflows/test.yml)
[![Latest Stable Version](https://poser.pugx.org/overtrue/pinyin/v/stable.svg)](https://packagist.org/packages/overtrue/pinyin) [![Total Downloads](https://poser.pugx.org/overtrue/pinyin/downloads.svg)](https://packagist.org/packages/overtrue/pinyin) [![Latest Unstable Version](https://poser.pugx.org/overtrue/pinyin/v/unstable.svg)](https://packagist.org/packages/overtrue/pinyin) [![License](https://poser.pugx.org/overtrue/pinyin/license.svg)](https://packagist.org/packages/overtrue/pinyin)

:cn: 基于 [mozillazg/pinyin-data](https://github.com/mozillazg/pinyin-data) 词典的中文转拼音工具，更准确的支持多音字的汉字转拼音解决方案。

[喜欢我的项目？点击这里支持我](https://github.com/sponsors/overtrue)

## 安装

使用 Composer 安装:

```bash
composer require overtrue/pinyin:^5.0
```

## 使用

### 拼音风格

除了获取首字母的方法外，所有方法都支持第二个参数，用于指定拼音的格式，可选值为：

- `symbol` （默认）声调符号，例如 `pīn yīn`
- `none` 不输出拼音，例如 `pin yin`
- `number` 末尾数字模式的拼音，例如 `pin1 yin1`

### 返回值

除了 `permalink` 返回字符串外，其它方法都返回集合类型 [`Overtrue\Pinyin\Collection`](https://github.com/overtrue/pinyin/blob/master/src/Collection.php)：

```php
use Overtrue\Pinyin\Pinyin;

$pinyin = Pinyin::sentence('你好，世界');
```

你可以通过以下方式访问集合内容:

```php
echo $pinyin; // nǐ hǎo shì jiè

// 直接将对象转成字符串
$string = (string) $pinyin; // nǐ hǎo shì jiè

$pinyin->toArray(); // ['nǐ', 'hǎo', 'shì', 'jiè']

// 直接使用索引访问
$pinyin[0]; // 'nǐ'

// 使用函数遍历
$pinyin->map('ucfirst'); // ['Nǐ', 'Hǎo', 'Shì', 'Jiè']

// 拼接为字符串
$pinyin->join(' '); // 'nǐ hǎo shì jiè'
$pinyin->join('-'); // 'nǐ-hǎo-shì-jiè'

// 转成 json
$pinyin->toJson(); // '["nǐ","hǎo","shì","jiè"]'
json_encode($pinyin); // '["nǐ","hǎo","shì","jiè"]'
```

### 文字段落转拼音

```php
use Overtrue\Pinyin\Pinyin;

echo Pinyin::sentence('带着希望去旅行，比到达终点更美好');
// dài zhe xī wàng qù lǚ xíng ， bǐ dào dá zhōng diǎn gèng měi hǎo

// 去除声调
echo Pinyin::sentence('带着希望去旅行，比到达终点更美好', 'none');
// dai zhe xi wang qu lv xing ， bi dao da zhong dian geng mei hao

// 保留所有非汉字字符
echo Pinyin::fullSentence('ル是片假名，π是希腊字母', 'none');
// ル shi pian jia ming ，π shi xi la zi mu
```

### 生成用于链接的拼音字符串

通常用于文章链接等，可以使用 `permalink` 方法获取拼音字符串：

```php
echo Pinyin::permalink('带着希望去旅行'); // dai-zhe-xi-wang-qu-lyu-xing
echo Pinyin::permalink('带着希望去旅行', '.'); // dai.zhe.xi.wang.qu.lyu.xing
```

### 获取首字符字符串

通常用于创建搜索用的索引，可以使用 `abbr` 方法转换：

```php
Pinyin::abbr('带着希望去旅行'); // ['d', 'z', 'x', 'w', 'q', 'l', 'x']
echo Pinyin::abbr('带着希望去旅行')->join('-'); // d-z-x-w-q-l-x
echo Pinyin::abbr('你好2018！')->join(''); // nh2018
echo Pinyin::abbr('Happy New Year! 2018！')->join(''); // HNY2018

// 保留原字符串的英文单词
echo Pinyin::abbr('CGV电影院', false, true)->join(''); // CGVdyy
```

**姓名首字母**

将首字作为姓氏转换，其余作为普通词语转换：

```php
Pinyin::nameAbbr('欧阳'); // ['o', 'y']
echo Pinyin::nameAbbr('单单单')->join('-'); // s-d-d
```

### 姓名转换

姓名的姓的读音有些与普通字不一样，比如 ‘单’ 常见的音为 `dan`，而作为姓的时候读 `shan`。

```php
Pinyin::name('单某某'); // ['shàn', 'mǒu', 'mǒu']
Pinyin::name('单某某', 'none'); // ['shan', 'mou', 'mou']
Pinyin::name('单某某', 'none')->join('-'); // shan-mou-mou
```

### 护照姓名转换

根据国家规定 [关于中国护照旅行证上姓名拼音 ü（吕、律、闾、绿、女等）统一拼写为 YU 的提醒](http://sg.china-embassy.gov.cn/lsfw/zghz1/hzzxdt/201501/t20150122_2022198.htm) 的规则，将 `ü` 转换为 `yu`：

```php
Pinyin::passportName('吕小布'); // ['lyu', 'xiao', 'bu']
Pinyin::passportName('女小花'); // ['nyu', 'xiao', 'hua']
Pinyin::passportName('律师'); // ['lyu', 'shi']
```

### 多音字

多音字的返回值为关联数组的集合，默认返回去重后的所有读音：

```php
$pinyin = Pinyin::polyphones('重庆');

$pinyin['重']; // ["zhòng", "chóng", "tóng"]
$pinyin['庆']; // ["qìng"]

$pinyin->toArray();
// [
//     "重": ["zhòng", "chóng", "tóng"],
//     "庆": ["qìng"]
// ]
```

如果不想要去重，可以数组形式返回：

```php
$pinyin = Pinyin::polyphones('重庆重庆', Converter::TONE_STYLE_SYMBOL, true);

// or 
$pinyin = Pinyin::polyphonesAsArray('重庆重庆', Converter::TONE_STYLE_SYMBOL);

$pinyin->toArray();
// [
//     ["重" => ["zhòng", "chóng", "tóng"]],
//     ["庆" => ["qìng"]],
//     ["重" => ["zhòng", "chóng", "tóng"]],
//     ["庆" => ["qìng"]]
// ]
```

### 单字转拼音

和多音字类似，单字的返回值为字符串，多音字将根据该字字频调整得到常用音：

```php
$pinyin = Pinyin::chars('重庆');

echo $pinyin['重']; // "zhòng"
echo $pinyin['庆']; // "qìng"

$pinyin->toArray();
// [
//     "重": "zhòng",
//     "庆": "qìng"
// ]
```

> **Warning**
>
> 当单字处理时由于多音字来自词频表中取得常用音，所以在词语环境下可能出现不正确的情况，建议使用多音字处理。

更多使用请参考 [测试用例](https://github.com/overtrue/pinyin/blob/master/tests/PinyinTest.php)。

## v/yu/ü 的问题

根据国家语言文字工作委员会的规定，`lv`、`lyu`、`lǚ` 都是正确的，但是 `lv` 是最常用的，所以默认使用 `lv`，如果你需要使用其他的，可以在初始化时传入：

```php
echo Pinyin::sentence('旅行');
// lǚ xíng

echo Pinyin::sentence('旅行', 'none');
// lv xing

echo Pinyin::yuToYu()->sentence('旅行', 'none');
// lyu xing

echo Pinyin::yuToU()->sentence('旅行', 'none');
// lu xing

echo Pinyin::yuToV()->sentence('旅行', 'none');
// lv xing
```

> **Warning**
>
> 仅在拼音风格为非 `none` 模式下有效。

## 命令行工具

你可以使用命令行来实现拼音的转换：

```bash
php ./bin/pinyin 带着希望去旅行 --method=sentence --tone-style=symbol
# dài zhe xī wàng qù lǚ xíng
```

更多使用方法，可以查看帮助文档：

```bash
php ./bin/pinyin --help

# Usage:
#     ./pinyin [chinese] [method] [options]
# Options:
#     -j, --json               输出 JSON 格式.
#     -c, --compact            不格式化输出 JSON.
#     -m, --method=[method]    转换方式，可选：sentence/sentenceFull/permalink/abbr/nameAbbr/name/passportName/phrase/polyphones/chars.
#     --no-tone                不使用音调.
#     --tone-style=[style]     音调风格，可选值：symbol/none/number, default: none.
#     -h, --help               显示帮助.
```

## 在 Laravel 中使用

独立的包在这里：[overtrue/laravel-pinyin](https://github.com/overtrue/laravel-pinyin)

## 中文简繁转换

如何你有这个需求，也可以了解我的另一个包：[overtrue/php-opencc](https://github.com/overtrue/php-opencc)

## Contribution

欢迎提意见及完善补充词库：

- 单字拼音错误请添加到：[sources/pathes/chars.txt](https://github.com/overtrue/pinyin/blob/master/sources/pathes/chars.txt)；
- 词语错误或补齐，请添加到：[sources/pathes/words.txt](https://github.com/overtrue/pinyin/blob/master/sources/pathes/words.txt)；

## 参考

- [mozillazg/pinyin-data](https://github.com/mozillazg/pinyin-data)
- [详细参考资料](https://github.com/overtrue/pinyin-resources)

## :heart: Sponsor me

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me.svg?raw=true)](https://github.com/sponsors/overtrue)

如果你喜欢我的项目并想支持它，[点击这里 :heart:](https://github.com/sponsors/overtrue)

## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/overtrue)

## PHP 扩展包开发

> 想知道如何从零开始构建 PHP 扩展包？
>
> 请关注我的实战课程，我会在此课程中分享一些扩展开发经验 —— [《PHP 扩展包实战教程 - 从入门到发布》](https://learnku.com/courses/creating-package)

# License

MIT
