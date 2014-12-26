Pinyin 
======

[![Build Status](https://travis-ci.org/overtrue/pinyin.svg?branch=master)](https://travis-ci.org/overtrue/pinyin)
[![Latest Stable Version](https://poser.pugx.org/overtrue/pinyin/v/stable.svg)](https://packagist.org/packages/overtrue/pinyin) [![Total Downloads](https://poser.pugx.org/overtrue/pinyin/downloads.svg)](https://packagist.org/packages/overtrue/pinyin) [![Latest Unstable Version](https://poser.pugx.org/overtrue/pinyin/v/unstable.svg)](https://packagist.org/packages/overtrue/pinyin) [![License](https://poser.pugx.org/overtrue/pinyin/license.svg)](https://packagist.org/packages/overtrue/pinyin)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/overtrue/pinyin/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/overtrue/pinyin/?branch=master)

基于[CC-CEDICT](http://cc-cedict.org/wiki/)词典的中文转拼音工具, 更准确的汉字转拼音解决方案。 

SAE服务地址：http://string2pinyin.sinaapp.com/doc.html

```php
use \Overtrue\Pinyin\Pinyin; 

echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好');
// dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo

//多音字
// 了
Pinyin::pinyin('了然'); // liǎo rán
Pinyin::pinyin('来了'); // lái le

// 还
Pinyin::pinyin('还有'); // hái yǒu
Pinyin::pinyin('交还'); // jiāo huán

// 什
Pinyin::pinyin('什么'); // shén me
Pinyin::pinyin('什锦'); // shí jǐn

// 便
Pinyin::pinyin('便当'); // biàn dāng
Pinyin::pinyin('便宜'); // pián yí

// 剥
Pinyin::pinyin('剥皮'); // bāo pí
Pinyin::pinyin('剥皮器'); // bō pí qì

// 不
Pinyin::pinyin('赔不是'); // péi bú shi
Pinyin::pinyin('跑了和尚，跑不了庙'); // pǎo le hé shàng , pǎo bù liǎo miào

// 降
Pinyin::pinyin('降温'); // jiàng wēn
Pinyin::pinyin('投降'); // tóu xiáng

// 都
Pinyin::pinyin('首都'); // shǒu dū
Pinyin::pinyin('都什么年代了'); // dōu shén me nián dài le

// 乐
Pinyin::pinyin('快乐'); // kuài lè
Pinyin::pinyin('音乐'); // yīn yuè

// 长
Pinyin::pinyin('成长'); // chéng zhǎng
Pinyin::pinyin('长江'); // cháng jiāng

// 难
Pinyin::pinyin('难民'); // nàn mín
Pinyin::pinyin('难过'); // nán guò
...

```


# 安装
1. 使用 Composer 安装:
	```
	composer require overtrue/pinyin:2.*
	```
	或者在你的项目composer.json加入：
	```javascript
	{
	    "require": {
	        "overtrue/pinyin": "2.*"
	    }
	}
	```

2. 直接下载文件 `src/Pinyin/Pinyin.php` 引入到项目中。


# 使用

```php
<?php
use \Overtrue\Pinyin\Pinyin;

//获取拼音
echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好');
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
echo Pinyin::pinyin('冷');
// rè
```


## 设置

|      选项      | 描述                                                |
| -------------  | --------------------------------------------------- |
| `delimiter`    | 分隔符，默认为一个空格 ' '                              |
| `traditional`  | 繁体                                                 |
| `accent`       | 是否输出音调                                           |
| `letter`       | 只输出首字母，或者直接使用`Pinyin::letter($string)`      |
| `only_chinese` | 只保留`$string`中中文部分                              |
| `uppercase`    | 取首字母时的大写，默认`false`                           |


*全局设置：*  `Pinyin::set('delimiter', '-');`

*临时设置：*  `Pinyin::pinyin($word, $settings)` 在调用的方法后传参

example:

```php

Pinyin::set('delimiter', '-');//全局
echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好');

// dài-zhe-xī-wàng-qù-lǔ-xíng-bǐ-dào-dá-zhōng-diǎn-gèng-měi-hǎo
```
```php

$setting = [
	    'delimiter' => '-',
	    'accent'    => false,
	   ];

echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好', $setting);//这里的setting只是临时修改，并非全局设置

// dai-zhe-xi-wang-qu-lu-xing-bi-dao-da-zhong-dian-geng-mei-hao
```

```php
Pinyin::set('accent', false);
echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好');

// dai zhe xi wang qu lu xing bi dao da zhong dian geng mei hao
```

# 在Laravel中使用

```shell
composer require overtrue/pinyin:2.*
```
在laravel配置文件中`app.php`里`providers`里加入:
```php
'Overtrue\Pinyin\PinyinServiceProvider',
```
然后看起来可能是这样：
```php
	'providers' =>
		//...
		'Illuminate\Validation\ValidationServiceProvider',
		'Illuminate\View\ViewServiceProvider',
		'Overtrue\Pinyin\PinyinServiceProvider',
	],
```

然后你可以添加配置文件：`config/pinyin.php`(_注意：此项为可选，如果不需要设置则不用创建此文件_):
```php
<?php

return [
	'delimiter' => '-',
	'accent' => false,
	//...
];
```
以上的设置会是全局的设置，如需临时设置请在方法里传参，例如:
```php

Pinyin::letter('您好世界', ['delimiter' => '-']); //n-h-s-j
//大写字母输出
Pinyin::letter('您好世界', ['delimiter' => '-', 'uppercase' => true]); //N-H-S-J
```

### 使用
与上面的使用方法一样：

```php
use \Overtrue\Pinyin\Pinyin;

//...

$pinyin = Pinyin::pinyin("带着希望去旅行，比到达终点更美好");

```

# TODO
- [x] 添加获取首字母；
- [x] 支持繁体；
- [x] 添加补充词典；
- [x] 添加音频表，根据音频提高未匹配词典时多音字准确度；
- [x] 添加Laravel4的serviece provider.
- [x] 添加首字母输出大小写选项`uppercase`
- [x] 支持载入自定义词库:`Pinyin::appends($appends = array())`
- [ ] 支持Laravel5的service provider.

# Contribution
欢迎提意见及完善补充词库 `src/Pinyin/data/additional.php`! :kiss:

# 参考
- [CC-CEDICT](http://cc-cedict.org/wiki/)
- [現代漢語語音語料庫](http://mmc.sinica.edu.tw/intro_c_01.html)
- [汉典](http://www.zdic.net/)

# License

MIT
