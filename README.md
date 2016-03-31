Pinyin
======

[![Build Status](https://travis-ci.org/overtrue/pinyin.svg?branch=master)](https://travis-ci.org/overtrue/pinyin)
[![Latest Stable Version](https://poser.pugx.org/overtrue/pinyin/v/stable.svg)](https://packagist.org/packages/overtrue/pinyin) [![Total Downloads](https://poser.pugx.org/overtrue/pinyin/downloads.svg)](https://packagist.org/packages/overtrue/pinyin) [![Latest Unstable Version](https://poser.pugx.org/overtrue/pinyin/v/unstable.svg)](https://packagist.org/packages/overtrue/pinyin) [![License](https://poser.pugx.org/overtrue/pinyin/license.svg)](https://packagist.org/packages/overtrue/pinyin)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/overtrue/pinyin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/overtrue/pinyin/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/overtrue/pinyin/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/overtrue/pinyin/?branch=master)

基于 [CC-CEDICT](http://cc-cedict.org/wiki/) 词典的中文转拼音工具，更准确的汉字转拼音解决方案。

```php
use Overtrue\Pinyin\Pinyin;

echo Pinyin::trans('带着希望去旅行，比到达终点更美好');
// dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo

//多音字
// 了
Pinyin::trans('了然'); // liǎo rán
Pinyin::trans('来了'); // lái le

// 还
Pinyin::trans('还有'); // hái yǒu
Pinyin::trans('交还'); // jiāo huán

// 什
Pinyin::trans('什么'); // shén me
Pinyin::trans('什锦'); // shí jǐn

// 便
Pinyin::trans('便当'); // biàn dāng
Pinyin::trans('便宜'); // pián yí

// 剥
Pinyin::trans('剥皮'); // bāo pí
Pinyin::trans('剥皮器'); // bō pí qì

// 不
Pinyin::trans('赔不是'); // péi bú shi
Pinyin::trans('跑了和尚，跑不了庙'); // pǎo le hé shàng , pǎo bù liǎo miào

// 降
Pinyin::trans('降温'); // jiàng wēn
Pinyin::trans('投降'); // tóu xiáng

// 都
Pinyin::trans('首都'); // shǒu dū
Pinyin::trans('都什么年代了'); // dōu shén me nián dài le

// 乐
Pinyin::trans('快乐'); // kuài lè
Pinyin::trans('音乐'); // yīn yuè

// 长
Pinyin::trans('成长'); // chéng zhǎng
Pinyin::trans('长江'); // cháng jiāng

// 难
Pinyin::trans('难民'); // nàn mín
Pinyin::trans('难过'); // nán guò
...

```


## 安装
1. 使用 Composer 安装:
	```
	composer require overtrue/pinyin:2.*
	```
	或者在你的项目 composer.json 加入：
	```javascript
	{
	    "require": {
	        "overtrue/pinyin": "2.*"
	    }
	}
	```

2. 直接下载文件 `src/Pinyin/Pinyin.php` 引入到项目中。


## 使用

```php
<?php
use Overtrue\Pinyin\Pinyin;

//获取拼音
echo Pinyin::trans('带着希望去旅行，比到达终点更美好');
// dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo

//获取首字母
echo Pinyin::letter('带着希望去旅行，比到达终点更美好');
// d z x w q l x b d d z d g m h

//当前也可以两个同时获取
echo Pinyin::parse('带着希望去旅行，比到达终点更美好');
// output:
// array(
//  'src'    => '带着希望去旅行，比到达终点更美好',
// 	'pinyin' => 'dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo',
// 	'letter' => 'd z x w q l x b d d z d g m h',
// );

// 加载自定义补充词库
$appends = array(
	'冷' => 're4',
);
Pinyin::appends($appends);
echo Pinyin::trans('冷');
// rè
```


### 设置

|      选项      | 描述                                                |
| -------------  | --------------------------------------------------- |
| `delimiter`    | 分隔符，默认为一个空格                              |
| `accent`       | 是否输出音调                                        |
| `only_chinese` | 只保留 `$string` 中中文部分                         |
| `uppercase`    | 取首字母时的大写，默认 `false`                      |
| `charset`    | 字符集，默认：`UTF-8`                      |


*全局设置：*  `Pinyin::set('delimiter', '-');`

*临时设置：*  `Pinyin::trans($word, $settings)` 在调用的方法后传参

example:

```php

Pinyin::set('delimiter', '-');//全局
echo Pinyin::trans('带着希望去旅行，比到达终点更美好');

// dài-zhe-xī-wàng-qù-lǔ-xíng-bǐ-dào-dá-zhōng-diǎn-gèng-měi-hǎo
```
```php

$setting = [
	    'delimiter' => '-',
	    'accent'    => false,
	   ];

echo Pinyin::trans('带着希望去旅行，比到达终点更美好', $setting);//这里的 setting 只是临时修改，并非全局设置

// dai-zhe-xi-wang-qu-lu-xing-bi-dao-da-zhong-dian-geng-mei-hao
```

```php
Pinyin::set('accent', false);
echo Pinyin::trans('带着希望去旅行，比到达终点更美好');

// dai zhe xi wang qu lu xing bi dao da zhong dian geng mei hao
```

## 在 Laravel 中使用

独立的包在这里：[overtrue/laravel-pinyin](https://github.com/overtrue/laravel-pinyin)


### 使用

与上面的使用方法一样：

```php
use Overtrue\Pinyin\Pinyin;

//...

$pinyin = Pinyin::trans("带着希望去旅行，比到达终点更美好");

```

## TODO
- [x] <del>添加获取首字母；</del>
- [x] <del>添加补充词典；</del>
- [x] <del>添加音频表，根据音频提高未匹配词典时多音字准确度；</del>
- [x] <del>添加首字母输出大小写选项 `uppercase`；</del>
- [x] <del>支持载入自定义词库：`Pinyin::appends($appends = array())`；</del>
- [x] <del>支持 Laravel 5 的 service provider。[overtrue/laravel-pinyin](https://github.com/overtrue/laravel-pinyin)</del>

## Contribution
欢迎提意见及完善补充词库 `src/data/dict.php`！ :kiss:

## 参考

- [CC-CEDICT](http://cc-cedict.org/wiki/)
- [現代漢語語音語料庫](http://mmc.sinica.edu.tw/intro_c_01.html)
- [汉典](http://www.zdic.net/)

# License

MIT
