pinyin
======

Chinese to pinyin translator based on [CC-CEDICT](http://cc-cedict.org/wiki/).

# usage

```php
$py = new Pinyin('./dict/cedict_ts.u8');
echo $py->trans('带着希望去旅行，比到达终点更美好');

// dài zhe xī wàng qù lǔ xíng bǐ dào dá zhōng diǎn gèng měi hǎo 
```

## setting

- `delimiter` Separator between each pinyin, default is a space ' '.
- `accent` Whether the output tone.

```php
<?php
$setting = [
			'delimiter' => '-',
		   ];
$py = new Pinyin('./dict/cedict_ts.u8', $setting);
echo $py->trans('带着希望去旅行，比到达终点更美好');

// dài-zhe-xī-wàng-qù-lǔ-xíng-bǐ-dào-dá-zhōng-diǎn-gèng-měi-hǎo
```
```php
<?php
$setting = [
			'delimiter' => '-',
			'accent' => false,
		   ];
$py = new Pinyin('./dict/cedict_ts.u8', $setting);
echo $py->trans('带着希望去旅行，比到达终点更美好');

// dai-zhe-xi-wang-qu-lu-xing-bi-dao-da-zhong-dian-geng-mei-hao
```

```php
$setting = [
			'accent' => false,
		   ];
$py = new Pinyin('./dict/cedict_ts.u8', $setting);
echo $py->trans('带着希望去旅行，比到达终点更美好');

// dai zhe xi wang qu lu xing bi dao da zhong dian geng mei hao
```

# License

MIT
