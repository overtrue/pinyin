Pinyin
======

Chinese to pinyin translator based on [CC-CEDICT](http://cc-cedict.org/wiki/).

	PHP extension is in progress

# Install
1. installing via Composer:

	Next, update your project's composer.json file to include Pinyin:

	```javascript
	{
	    "require": {
	        "joychao/pinyin": "~1.0"
	    }
	}
	```

2. download source file `src/Pinyin.php` and include it.


# Usage

```php
<?php
echo Pinyin::trans('带着希望去旅行，比到达终点更美好');

// dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo 
```


## Setting

- `delimiter` Separator between each pinyin, default is a space ' '.
- `accent` Whether the output tone.

`Pinyin::set($setting);` or `Pinyin::trans($word, $setting)`

example:

```php
<?php
$setting = [
			'delimiter' => '-',
		   ];
Pinyin::set($setting);
echo Pinyin::trans('带着希望去旅行，比到达终点更美好');

// dài-zhe-xī-wàng-qù-lǔ-xíng-bǐ-dào-dá-zhōng-diǎn-gèng-měi-hǎo
```
```php
<?php
$setting = [
			'delimiter' => '-',
			'accent' => false,
		   ];

echo Pinyin::trans('带着希望去旅行，比到达终点更美好', $setting);

// dai-zhe-xi-wang-qu-lu-xing-bi-dao-da-zhong-dian-geng-mei-hao
```

```php
<?php
$setting = [
			'accent' => false,
		   ];
Pinyin::set($setting);
echo Pinyin::trans('带着希望去旅行，比到达终点更美好');

// dai zhe xi wang qu lu xing bi dao da zhong dian geng mei hao
```

# License

MIT
