Pinyin
======

基于CC-CEDICT词典的中文转拼音工具，支持多音字转换。 [CC-CEDICT](http://cc-cedict.org/wiki/).


# 安装
1. 使用 Composer 安装:
	```
	composer require overtrue/pinyin 1.0
	```
	或者在你的项目composer.json加入：
	```javascript
	{
	    "require": {
	        "overtrue/pinyin": "~1.0"
	    }
	}
	```

2. 直接下载文件 `src/Overtrue/Pinyin.php` 引入到项目中。


# 使用

```php
<?php
use \Overtrue\Pinyin;

$pinyin = new Pinyin;


echo $pinyin->trans('带着希望去旅行，比到达终点更美好');

// dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo
```


## 设置

- `delimiter` 分隔符，默认为一个空格 ' '；
- `accent` 是否输出音调；

`$pinyin->set('delimiter', '-');` or `$pinyin->trans($word, $settings)`

example:

```php

$pinyin->set('delimiter', '-')
echo $pinyin->trans('带着希望去旅行，比到达终点更美好');

// dài-zhe-xī-wàng-qù-lǔ-xíng-bǐ-dào-dá-zhōng-diǎn-gèng-měi-hǎo
```
```php

$setting = [
			'delimiter' => '-',
			'accent' => false,
		   ];

echo $pinyin->trans('带着希望去旅行，比到达终点更美好', $setting);

// dai-zhe-xi-wang-qu-lu-xing-bi-dao-da-zhong-dian-geng-mei-hao
```

```php
$pinyin->set('accent', false);
echo $pinyin->trans('带着希望去旅行，比到达终点更美好');

// dai zhe xi wang qu lu xing bi dao da zhong dian geng mei hao
```

# License

MIT
