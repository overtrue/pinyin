Pinyin
======

基于CC-CEDICT词典的中文转拼音工具。 [CC-CEDICT](http://cc-cedict.org/wiki/).


# 安装
1. 使用 Composer 安装:
	```
	composer require overtrue/pinyin 1.0
	```
	或者在你的项目composer.json加入：
	```javascript
	{
	    "require": {
	        "overtrue/pinyin": "~1.2"
	    }
	}
	```

2. 直接下载文件 `src/Overtrue/Pinyin.php` 引入到项目中。


# 使用

```php
<?php
use \Overtrue\Pinyin;

//获取拼音
echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好');
//或者: Overtrue\pinyin($string);
// dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo

//获取首字母
echo Pinyin::letter('带着希望去旅行，比到达终点更美好');
// D Z X W Q L X B D D Z D G M H

```

## 设置

- `delimiter` 分隔符，默认为一个空格 ' '；
- `traditional` 繁体
- `accent` 是否输出音调；
- `letter` 只输出首字母，或者直接使用`Pinyin::letter($string)`;
- `only_chinese` 只保留中文

* 全局设置：* `Pinyin::set('delimiter', '-');`

* 临时设置：* `Pinyin::pinyin($word, $settings)` 在调用的方法后传参

example:

```php

Pinyin::set('delimiter', '-');//全局
echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好');

// dài-zhe-xī-wàng-qù-lǔ-xíng-bǐ-dào-dá-zhōng-diǎn-gèng-měi-hǎo
```
```php

$setting = [
			'delimiter' => '-',
			'accent' => false,
		   ];

echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好', $setting);//这里的setting只是临时修改，并非全局设置

// dai-zhe-xi-wang-qu-lu-xing-bi-dao-da-zhong-dian-geng-mei-hao
```

```php
Pinyin::set('accent', false);
echo Pinyin::pinyin('带着希望去旅行，比到达终点更美好');

// dai zhe xi wang qu lu xing bi dao da zhong dian geng mei hao
```

# TODO
- [x] 添加获取首字母；
- [x] 支持繁体；
- [ ] 添加补充词典；
- [ ] 添加词频字典，根据词频提高未匹配词典时多音字准确度；

# 参考
- [CC-CEDICT](http://cc-cedict.org/wiki/)
- [現代漢語語音語料庫](http://mmc.sinica.edu.tw/intro_c_01.html)
- [汉典](http://www.zdic.net/)

# License

MIT
