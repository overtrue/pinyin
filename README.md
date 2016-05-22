Pinyin
======

[![Build Status](https://travis-ci.org/overtrue/pinyin.svg?branch=master)](https://travis-ci.org/overtrue/pinyin)
[![Latest Stable Version](https://poser.pugx.org/overtrue/pinyin/v/stable.svg)](https://packagist.org/packages/overtrue/pinyin) [![Total Downloads](https://poser.pugx.org/overtrue/pinyin/downloads.svg)](https://packagist.org/packages/overtrue/pinyin) [![Latest Unstable Version](https://poser.pugx.org/overtrue/pinyin/v/unstable.svg)](https://packagist.org/packages/overtrue/pinyin) [![License](https://poser.pugx.org/overtrue/pinyin/license.svg)](https://packagist.org/packages/overtrue/pinyin)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/overtrue/pinyin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/overtrue/pinyin/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/overtrue/pinyin/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/overtrue/pinyin/?branch=master)

基于 [CC-CEDICT](http://cc-cedict.org/wiki/) 词典的中文转拼音工具，更准确的支持多音字的汉字转拼音解决方案。


## 安装

使用 Composer 安装:

```
composer require "overtrue/pinyin:~3.0"
```

## 使用

### 拼音数组

```php
use Overtrue\Pinyin\Pinyin;

$pinyin = new Pinyin();

$pinyin->convert('带着希望去旅行，比到达终点更美好');
// ["dai", "zhe", "xi", "wang", "qu", "lv", "xing", "bi", "dao", "da", "zhong", "dian", "geng", "mei", "hao"]

$pinyin->convert('带着希望去旅行，比到达终点更美好', PINYIN_UNICODE);
// ["dài","zhe","xī","wàng","qù","lǚ","xíng","bǐ","dào","dá","zhōng","diǎn","gèng","měi","hǎo"]

$pinyin->convert('带着希望去旅行，比到达终点更美好', PINYIN_ASCII);
//["dai4","zhe","xi1","wang4","qu4","lv3","xing2","bi3","dao4","da2","zhong1","dian3","geng4","mei3","hao3"]
```

选项：

|      选项      | 描述                                                |
| -------------  | ---------------------------------------------------|
| `PINYIN_NONE`   | 不带音调输出: `mei hao`                           |
| `PINYIN_ASCII`  | 带数字式音调：  `mei3 hao3`                    |
| `PINYIN_UNICODE`  | UNICODE 式音调：`měi hǎo`                    |

### 生成用于链接的拼音字符串

```php
$pinyin->permalink('带着希望去旅行'); // dai-zhe-xi-wang-qu-lv-xing
$pinyin->permalink('带着希望去旅行', '.'); // dai.zhe.xi.wang.qu.lv.xing
```

### 获取首字符字符串

```php
$pinyin->abbr('带着希望去旅行'); // dzxwqlx
$pinyin->abbr('带着希望去旅行', '-'); // d-z-x-w-q-l-x
```

### 翻译整段文字为拼音

将会保留中文字符：`，。 ！ ？ ： “ ” ‘ ’` 并替换为对应的英文符号。

```php
$pinyin->sentence('带着希望去旅行，比到达终点更美好！');
// dai zhe xi wang qu lv xing, bi dao da zhong dian geng mei hao!

$pinyin->sentence('带着希望去旅行，比到达终点更美好！', true);
// dài zhe xī wàng qù lǚ xíng, bǐ dào dá zhōng diǎn gèng měi hǎo!
```

### 翻译姓名

姓名的姓的读音有些与普通字不一样，比如 ‘单’ 常见的音为 `dan`，而作为姓的时候读 `shan`。

```php
$pinyin->name('单某某'); // ['shan', 'mou', 'mou']
$pinyin->name('单某某', PINYIN_UNICODE); // ["shàn","mǒu","mǒu"]
```

## 在 Laravel 中使用

独立的包在这里：[overtrue/laravel-pinyin](https://github.com/overtrue/laravel-pinyin)

## Contribution
欢迎提意见及完善补充词库 [`tools/patches/`](https://github.com/overtrue/pinyin/tree/master/tools/patches) :kiss:

## 参考

- [详细参考资料](https://github.com/overtrue/pinyin-resources)

# License

MIT
