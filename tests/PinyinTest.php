<?php

namespace Overtrue\Pinyin\Tests;

use Overtrue\Pinyin\Collection;
use PHPUnit\Framework\TestCase;
use Overtrue\Pinyin\Pinyin;

class PinyinTest extends TestCase
{
    public function assertPinyin(array|string $expected, Collection $collection)
    {
        $this->assertEquals($expected, \is_array($expected) ? $collection->toArray() : $collection->join());
    }

    public function test_name()
    {
        $this->assertPinyin(['ōu', 'yáng'], Pinyin::name('欧阳'));
        $this->assertPinyin(['chóng', 'qìng'], Pinyin::name('重庆'));
        $this->assertPinyin(['shàn'], Pinyin::name('单'));
        $this->assertPinyin(['shàn', 'dān', 'dān'], Pinyin::name('单单单'));
        $this->assertPinyin(['chán', 'yú', 'dān'], Pinyin::name('单于单'));
        $this->assertPinyin(['piáo', 'dān', 'pǔ'], Pinyin::name('朴单朴'));
        $this->assertPinyin(['yù', 'chí', 'pǔ'], Pinyin::name('尉迟朴'));
        $this->assertPinyin(['wèi', 'mǒu', 'mǒu'], Pinyin::name('尉某某'));
        $this->assertPinyin(['yù', 'chí', 'mǒu', 'mǒu'], Pinyin::name('尉迟某某'));
        $this->assertPinyin(['zhái', 'dí', 'dí'], Pinyin::name('翟翟翟'));

        // 只有首字算姓氏
        $this->assertPinyin(['gū', 'dān'], Pinyin::name('孤单'));

        // 以下两词在任何位置都不变
        $this->assertPinyin(['mǒu', 'mǒu', 'yù', 'chí'], Pinyin::name('某某尉迟'));
        $this->assertPinyin(['shàn', 'chán', 'yú', 'dān'], Pinyin::name('单单于单'));
    }

    public function test_phrase()
    {
        $this->assertPinyin(['nín', 'hǎo'], Pinyin::phrase('您好!'));
        $this->assertPinyin(['nín', 'hǎo', '2018i', 'New', 'Year'], Pinyin::phrase('您好&^2018i New Year!√ç'));
        $this->assertPinyin('dài zhe xī wàng qù lyu xíng bǐ dào dá zhōng diǎn gèng měi hǎo', Pinyin::phrase('带着希望去旅行，比到达终点更美好！'));
    }

    public function test_permalink()
    {
        $this->assertSame('dai-zhe-xi-wang-qu-lyu-xing', Pinyin::permalink('带着希望去旅行'));
        $this->assertSame('dai_zhe_xi_wang_qu_lyu_xing', Pinyin::permalink('带着希望去旅行', '_'));
        $this->assertSame('dai.zhe.xi.wang.qu.lyu.xing', Pinyin::permalink('带着希望去旅行', '.'));
        $this->assertSame('daizhexiwangqulyuxing', Pinyin::permalink('带着希望去旅行', ''));

        // with number.
        $this->assertSame('1-dai-23-zhe-56-xi-wang-qu-abc-lyu-xing-568', Pinyin::permalink('1带23着。！5_6.=希望去abc旅行568'));

        $this->expectException('InvalidArgumentException', "Delimiter must be one of: '_', '-', '', '.'.");

        $this->assertSame('daizhexiwangqulyuxing', Pinyin::permalink('带着希望去旅行', '='));
    }

    public function test_abbr()
    {
        $this->assertPinyin(['d', 'z', 'x', 'w', 'q', 'l', 'x'], Pinyin::abbr('带着希望去旅行'));

        // issue #27
        $this->assertPinyin(['d', 'l', 'd', 'n', 'r'], Pinyin::abbr('独立的女人'));
        $this->assertPinyin(['n'], Pinyin::abbr('女'));
        $this->assertPinyin(['n', 'r'], Pinyin::abbr('女人'));

        // #91 #101
        $this->assertPinyin(['n', 'h', '2017'], Pinyin::abbr('你好2017！'));
        $this->assertPinyin(['H', 'N', 'Y', '2018'], Pinyin::abbr('Happy New Year! 2018！'));

        $this->assertPinyin(['d', 'f', 's', 'c', 'd', 'z', 'z', '123', 'r'], Pinyin::abbr('Ⅲ度房室传导阻滞123人'));
        $this->assertPinyin(['d', 't', 'f'], Pinyin::abbr('单田芳'));
    }

    public function test_name_abbr()
    {
        $this->assertPinyin(['o', 'y'], Pinyin::nameAbbr('欧阳'));
        $this->assertPinyin(['c', 'q'], Pinyin::nameAbbr('重庆'));
        $this->assertPinyin(['s'], Pinyin::nameAbbr('单'));
        $this->assertPinyin(['s', 'd', 'd'], Pinyin::nameAbbr('单单单'));
        $this->assertPinyin(['c', 'y', 'd'], Pinyin::nameAbbr('单于单'));
        $this->assertPinyin(['p', 'd', 'p'], Pinyin::nameAbbr('朴单朴'));
        $this->assertPinyin(['y', 'c', 'p'], Pinyin::nameAbbr('尉迟朴'));
        $this->assertPinyin(['w', 'm', 'm'], Pinyin::nameAbbr('尉某某'));
        $this->assertPinyin(['y', 'c', 'm', 'm'], Pinyin::nameAbbr('尉迟某某'));
        $this->assertPinyin(['z', 'd', 'd'], Pinyin::nameAbbr('翟翟翟'));

        // 只有首字算姓氏
        $this->assertPinyin(['g', 'd'], Pinyin::nameAbbr('孤单'));

        // 以下两词在任何位置都不变
        $this->assertPinyin(['m', 'm', 'y', 'c'], Pinyin::nameAbbr('某某尉迟'));
        $this->assertPinyin(['s', 'c', 'y', 'd'], Pinyin::nameAbbr('单单于单'));
    }

    public function test_sentence()
    {
        $this->assertPinyin(
            'dài zhe xī wàng qù lyu xíng ， bǐ dào dá zhōng diǎn gèng měi hǎo ！',
            Pinyin::sentence('带着希望去旅行，比到达终点更美好！')
        );

        $this->assertPinyin(
            'dài zhe &* xī 123 wàng qù good lyu boy2 xíng ！.',
            Pinyin::sentence('带^着&*希123望去good旅boy2行！.')
        );
        $this->assertPinyin(
            '-- dài zhe &* xī 123 wàng .。 qù good lyu boy2 xíng ！.',
            Pinyin::sentence('--带^着&*希123望.。去good旅boy2行！.')
        );

        // 特殊字符
        $this->assertPinyin('hello, world!', Pinyin::sentence('hello, world!'));
        $this->assertPinyin('DNA jiàn dìng', Pinyin::sentence('DNA鉴定'));
        $this->assertPinyin('21 sān tǐ zōng hé zhèng', Pinyin::sentence('21三体综合症'));
        $this->assertPinyin('C pán', Pinyin::sentence('C盘'));
        $this->assertPinyin('zhōng dù xìng fèi shuǐ zhǒng', Pinyin::sentence('中度性肺水肿'));

        // 了
        $this->assertPinyin(['liǎo', 'rán'], Pinyin::sentence('了然'));
        $this->assertPinyin(['lái', 'le'], Pinyin::sentence('来了'));

        // 还
        $this->assertPinyin(['hái', 'yǒu'], Pinyin::sentence('还有'));
        $this->assertPinyin(['jiāo', 'huán'], Pinyin::sentence('交还'));

        // 什
        $this->assertPinyin(['shén', 'me'], Pinyin::sentence('什么'));
        $this->assertPinyin(['shí', 'jǐn'], Pinyin::sentence('什锦'));

        // 便
        $this->assertPinyin(['biàn', 'dāng'], Pinyin::sentence('便当'));
        $this->assertPinyin(['pián', 'yí'], Pinyin::sentence('便宜'));

        // 剥
        $this->assertPinyin(['bō', 'xuē'], Pinyin::sentence('剥削'));
        $this->assertPinyin(['bāo', 'pí', 'qì'], Pinyin::sentence('剥皮器'));

        // 不
        $this->assertPinyin(['péi', 'bú', 'shì'], Pinyin::sentence('赔不是'));
        $this->assertPinyin(
            ['pǎo', 'le', 'hé', 'shàng', '，', 'pǎo', 'bù', 'liǎo', 'miào'],
            Pinyin::sentence('跑了和尚，跑不了庙')
        );

        // 降
        $this->assertPinyin(['jiàng', 'wēn'], Pinyin::sentence('降温'));
        $this->assertPinyin(['tóu', 'xiáng'], Pinyin::sentence('投降'));

        // 都
        $this->assertPinyin(['shǒu', 'dū'], Pinyin::sentence('首都'));
        $this->assertPinyin(['dōu', 'shén', 'me', 'nián', 'dài', 'le'], Pinyin::sentence('都什么年代了'));

        // 乐
        $this->assertPinyin(['kuài', 'lè'], Pinyin::sentence('快乐'));
        $this->assertPinyin(['yīn', 'yuè'], Pinyin::sentence('音乐'));

        // ⺁
        $this->assertPinyin(['fǎn'], Pinyin::sentence('⺁'));

        // 长
        $this->assertPinyin(['chéng', 'zhǎng'], Pinyin::sentence('成长'));
        $this->assertPinyin(['cháng', 'jiāng'], Pinyin::sentence('长江'));

        // 难
        $this->assertPinyin(['nàn', 'mín'], Pinyin::sentence('难民'));
        $this->assertPinyin(['nán', 'guò'], Pinyin::sentence('难过'));

        // 厦
        $this->assertPinyin(['dà', 'shà'], Pinyin::sentence('大厦'));
        $this->assertPinyin(['xià', 'mén'], Pinyin::sentence('厦门'));

        // 曾
        $this->assertPinyin(['céng', 'jīng'], Pinyin::sentence('曾经'));
        $this->assertPinyin(['xìng', 'zēng'], Pinyin::sentence('姓曾'));
        $this->assertPinyin(['zēng', 'xìng'], Pinyin::sentence('曾姓'));

        // 奇
        $this->assertPinyin(['qí', 'guài'], Pinyin::sentence('奇怪'));
        $this->assertPinyin(['jī', 'ǒu', 'jiào', 'yàn'], Pinyin::sentence('奇偶校验'));

        // 其它多音词
        $this->assertPinyin(['náo', 'zhí', 'wéi', 'qū'], Pinyin::sentence('挠直为曲'));
        $this->assertPinyin(['pī', 'fēng', 'mò', 'yuè'], Pinyin::sentence('批风抹月'));

        // 符号
        $this->assertPinyin(['nín', 'hǎo', '&', '2018i', 'New', 'Year!'], Pinyin::sentence('您好&^2018i New Year!√ç'));

        $this->assertPinyin(['nín', 'hǎo', '!'], Pinyin::sentence('您好!'));
        $this->assertPinyin(['nín', 'hǎo', '2018!'], Pinyin::sentence('您好2018!'));
        $this->assertPinyin(['nín', 'hǎo', 'New', 'Year!'], Pinyin::sentence('您好 New Year!'));

        // 测试字母+数字Bug.
        $this->assertPinyin('cè shì R60', Pinyin::sentence('测试R60'));
        $this->assertPinyin('cè ce5 shì Rr60', Pinyin::sentence('测ce5试Rr60'));
        $this->assertPinyin('cè 3sh4i R50', Pinyin::sentence('测3sh4i R50'));
        $this->assertPinyin('ce3sh4i R50', Pinyin::sentence('ce3sh4i R50'));
        $this->assertPinyin('33ai4', Pinyin::sentence('33ai4'));
        $this->assertPinyin('33ai4 nǐ', Pinyin::sentence('33ai4你'));
        $this->assertPinyin('ài 334 nǐ', Pinyin::sentence('爱334你'));
        $this->assertPinyin('aaaa1234', Pinyin::sentence('aaaa1234'));
        $this->assertPinyin('aaaa_1234', Pinyin::sentence('aaaa_1234'));
        $this->assertPinyin('ai45 liǎo wú shēng qù cè shì', Pinyin::sentence('ai45了无生趣测试'));
        $this->assertPinyin('java gōng chéng shī', Pinyin::sentence('java工程师'));
    }

    public function test_issues()
    {
        $this->assertPinyin('ā lè tài', Pinyin::sentence('阿勒泰'));
        $this->assertPinyin('è ěr duō sī', Pinyin::sentence('鄂尔多斯'));
        $this->assertPinyin('zú', Pinyin::sentence('足'));
        $this->assertPinyin('féng', Pinyin::sentence('冯'));
        $this->assertPinyin('bào hǔ píng hé', Pinyin::sentence('暴虎冯河'));
        $this->assertPinyin('hé', Pinyin::sentence('和'));
        $this->assertPinyin('gěi', Pinyin::sentence('给'));
        $this->assertPinyin('là', Pinyin::sentence('腊'));
        // # 28 词库不全
        $this->assertPinyin('kūn', Pinyin::sentence('堃'));

        // #29
        $this->assertPinyin('dì', Pinyin::sentence('地'));
        $this->assertPinyin('zhì yú sǐ dì', Pinyin::sentence('置于死地'));

        // #35
        $this->assertPinyin('jì xiào', Pinyin::sentence('技校'));
        $this->assertPinyin('jiào zhèng', Pinyin::sentence('校正'));

        // #45
        $this->assertPinyin('luó', Pinyin::sentence('罗'));

        // #58
        $this->assertPinyin('fēi', Pinyin::sentence('飞'));
        $this->assertPinyin('jiāng', Pinyin::sentence('将'));
        $this->assertPinyin('míng jiàng', Pinyin::sentence('名将'));
        $this->assertPinyin('dì wáng jiàng xiàng', Pinyin::sentence('帝王将相'));
        $this->assertPinyin('dì wáng jiàng xiàng', Pinyin::sentence('帝王将相'));
        $this->assertPinyin('wèi shǒu wèi wěi', Pinyin::sentence('畏首畏尾'));

        // #63
        $this->assertPinyin('shěn', Pinyin::sentence('沈'));
        $this->assertPinyin('shěn yáng', Pinyin::sentence('沈阳'));
        $this->assertPinyin('chén yú luò yàn', Pinyin::sentence('沈鱼落雁'));

        // #81
        $this->assertPinyin('yuán yùn', Pinyin::sentence('圆晕'));
        $this->assertPinyin('guāng yùn', Pinyin::sentence('光晕'));
        $this->assertPinyin('yūn jué', Pinyin::sentence('晕厥'));

        // #105 #112
        $this->assertPinyin('ǹ', Pinyin::sentence('嗯'));

        // #167
        $this->assertPinyin('chǔ', Pinyin::sentence('褚'));

        // #174
        $this->assertPinyin('tuò', Pinyin::sentence('拓'));

        // #146
        $this->assertPinyin('zhōng 、 wén', Pinyin::sentence('中、文'));

        // #96
        $this->assertPinyin('shén me', Pinyin::sentence('什么'));
        $this->assertPinyin('hái shuō shí mǒ ne ？ huán gěi nǐ 。 hái gè pì ！', Pinyin::sentence('还说什么呢？还给你。还个屁！'));

        // #82
        $this->assertPinyin('wū lā tè qián qí', Pinyin::sentence('乌拉特前旗'));
        $this->assertPinyin('cháo yáng qū', Pinyin::sentence('朝阳区'));
        $this->assertPinyin('jì xī xiàn', Pinyin::sentence('绩溪县'));
        $this->assertPinyin('bǎi sè xiàn', Pinyin::sentence('百色县'));
        $this->assertPinyin('dū ān yáo zú zì zhì xiàn', Pinyin::sentence('都安瑶族自治县'));
        $this->assertPinyin('tǎ shí kù ěr gān', Pinyin::sentence('塔什库尔干'));
        $this->assertPinyin('cháng yáng tǔ jiā zú zì zhì xiàn', Pinyin::sentence('长阳土家族自治县'));
        $this->assertPinyin('mǎ wěi qū', Pinyin::sentence('马尾区'));
        $this->assertPinyin('wèi shǒu wèi wěi', Pinyin::sentence('畏首畏尾'));
        $this->assertPinyin('sān dū shuǐ zú zì zhì xiàn', Pinyin::sentence('三都水族自治县'));

        $this->assertPinyin(['lyu', 'xiù', 'cai'], Pinyin::convert('吕秀才'));
        $this->assertPinyin(['lv', 'xiù', 'cai'], Pinyin::yuToV()->convert('吕秀才'));

        #175
        $this->assertPinyin('yuán', Pinyin::sentence('貟'));
        $this->assertPinyin(['yùn', 'xiù', 'cai'], Pinyin::name('貟秀才'));
        $this->assertPinyin(['yùn', 'xiù', 'cai'], Pinyin::name('贠秀才'));

        #183
        $this->assertPinyin('yín háng quàn', Pinyin::sentence('银行券'));
        $this->assertPinyin('xún chá', Pinyin::sentence('询查'));

        #170
        $this->assertPinyin('ké', Pinyin::sentence('咳'));
        $this->assertPinyin('xiǎo ér fèi ké kē lì', Pinyin::sentence('小儿肺咳颗粒'));

        #151
        $this->assertPinyin('gǔ tóu', Pinyin::sentence('骨头'));

        #116
        $this->assertPinyin(['shàn', 'mǒu', 'mǒu'], Pinyin::name('单某某'));

        #106
        $this->assertPinyin('zhēn huán zhuàn', Pinyin::sentence('甄嬛传'));
        $this->assertPinyin('chuán qí', Pinyin::sentence('传奇'));
        $this->assertPinyin('zhuàn jì', Pinyin::sentence('传记'));
        $this->assertPinyin('liú mèng qián', Pinyin::sentence('刘孟乾'));

        #164
        $this->assertPinyin(['ōu', 'mǒu', 'mǒu'], Pinyin::name('区某某'));
        $this->assertPinyin(['yuè', 'mǒu', 'mǒu'], Pinyin::name('乐某某'));
    }
}
