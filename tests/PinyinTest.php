<?php

namespace Overtrue\Pinyin\Tests;

use PHPUnit\Framework\TestCase;
use Overtrue\Pinyin\Pinyin;

class PinyinTest extends TestCase
{
    protected Pinyin $pinyin;

    public function setUp(): void
    {
        $this->pinyin = new Pinyin();
    }

    public function testConvert()
    {
        $this->assertSame(['nin', 'hao', '2018i', 'New', 'Year'], $this->pinyin->convert('您好&^2018i New Year!√ç', \PINYIN_KEEP_NUMBER | \PINYIN_KEEP_ENGLISH));
        $this->assertSame(['nin', 'hao', 'i', 'New', 'Year'], $this->pinyin->convert('您好&^2018i New Year!√ç', \PINYIN_KEEP_ENGLISH));
        $this->assertSame(['nin', 'hao', '2018'], $this->pinyin->convert('您好&^2018i New Year!√ç', \PINYIN_KEEP_NUMBER));

        $this->assertSame(['nin', 'hao'], $this->pinyin->convert('您好!'));
        $this->assertSame(['nin', 'hao'], $this->pinyin->convert('您好!', PINYIN_DEFAULT));
        $this->assertSame(['nin2', 'hao3'], $this->pinyin->convert('您好!', PINYIN_ASCII_TONE));
        $this->assertSame(['nín', 'hǎo'], $this->pinyin->convert('您好!', PINYIN_TONE));
        $this->assertSame(['nín', 'hǎo', '2018'], $this->pinyin->convert('您好2018!', PINYIN_KEEP_NUMBER | PINYIN_TONE));
        $this->assertSame(['nín', 'hǎo'], $this->pinyin->convert('您好 New Year!', PINYIN_KEEP_NUMBER | PINYIN_TONE));
    }

    public function testPermalink()
    {
        $this->assertSame('dai-zhe-xi-wang-qu-lyu-xing', $this->pinyin->permalink('带着希望去旅行'));
        $this->assertSame('dai_zhe_xi_wang_qu_lyu_xing', $this->pinyin->permalink('带着希望去旅行', '_'));
        $this->assertSame('dai.zhe.xi.wang.qu.lyu.xing', $this->pinyin->permalink('带着希望去旅行', '.'));
        $this->assertSame('daizhexiwangqulyuxing', $this->pinyin->permalink('带着希望去旅行', ''));

        // with number.
        $this->assertSame('1-dai-23-zhe-56-xi-wang-qu-abc-lyu-xing-568', $this->pinyin->permalink('1带23着。！5_6.=希望去abc旅行568'));

        $this->expectException('InvalidArgumentException', "Delimiter must be one of: '_', '-', '', '.'.");

        $this->assertSame('daizhexiwangqulyuxing', $this->pinyin->permalink('带着希望去旅行', '='));
    }

    public function testAbbr()
    {
        $this->assertSame('dzxwqlx', $this->pinyin->abbr('带着希望去旅行'));
        $this->assertSame('d z x w q l x', $this->pinyin->abbr('带着希望去旅行', ' '));
        $this->assertSame('d*z*x*w*q*l*x', $this->pinyin->abbr('带着希望去旅行', '*'));
        $this->assertSame('d--z--x--w--q--l--x', $this->pinyin->abbr('带着希望去旅行', '--'));

        // issue #27
        $this->assertEquals('dldnr', $this->pinyin->abbr('独立的女人'));
        $this->assertEquals('n', $this->pinyin->abbr('女'));
        $this->assertEquals('nr', $this->pinyin->abbr('女人'));

        // #91 #101
        $this->assertEquals('nh2017', $this->pinyin->abbr('你好2017！', \PINYIN_KEEP_NUMBER));
        $this->assertEquals('HNY2018', $this->pinyin->abbr('Happy New Year! 2018！', \PINYIN_KEEP_NUMBER | \PINYIN_KEEP_ENGLISH));
    }

    public function testSentence()
    {
        $this->assertSame('dai zhe xi wang qu lyu xing，bi dao da zhong dian geng mei hao！', $this->pinyin->sentence('带着希望去旅行，比到达终点更美好！'));

        $this->assertSame('dai zhe&*xi 123 wang qu good lyu boy2 xing！.', $this->pinyin->sentence('带^着&*希123望去good旅boy2行！.'));
        $this->assertSame('--dai zhe&*xi 123 wang.。qu good lyu boy2 xing！.', $this->pinyin->sentence('--带^着&*希123望.。去good旅boy2行！.'));
    }

    public function testName()
    {
        $this->assertSame(['shan'], $this->pinyin->name('单'));
        $this->assertSame(['gu', 'dan'], $this->pinyin->name('孤单'));
        $this->assertSame(['shan', 'dan', 'dan'], $this->pinyin->name('单单单'));
        $this->assertSame(['chan', 'yu', 'dan'], $this->pinyin->name('单于单'));
        $this->assertSame(['piao', 'dan', 'pu'], $this->pinyin->name('朴单朴'));
        $this->assertSame(['yu', 'chi', 'pu'], $this->pinyin->name('尉迟朴'));
        $this->assertSame(['wei', 'mou', 'mou'], $this->pinyin->name('尉某某'));
        $this->assertSame(['yu', 'chi', 'mou','mou'], $this->pinyin->name('尉迟某某'));
        $this->assertSame(['zhai', 'di', 'di'], $this->pinyin->name('翟翟翟'));

        // 以下两词在任何位置都不变
        $this->assertSame(['mou', 'mou', 'yu', 'chi'], $this->pinyin->name('某某尉迟'));
        $this->assertSame(['shan', 'chan', 'yu', 'dan'], $this->pinyin->name('单单于单'));
    }

    // test special words
    public function testSpecialWords()
    {
        $this->assertEquals('hello,world!', $this->pinyin->sentence('hello, world!'));
        $this->assertEquals('DNA jiàn dìng', $this->pinyin->sentence('DNA鉴定', \PINYIN_TONE));
        $this->assertEquals('21 sān tǐ zōng hé zhèng', $this->pinyin->sentence('21三体综合症', \PINYIN_TONE));
        $this->assertEquals('C pán', $this->pinyin->sentence('C盘', \PINYIN_TONE));
        $this->assertEquals('G diǎn', $this->pinyin->sentence('G点', \PINYIN_TONE));
        $this->assertEquals('zhōng dù xìng fèi shuǐ zhǒng', $this->pinyin->sentence('中度性肺水肿', \PINYIN_TONE));
    }

    // test multibyte non-Chinese character
    public function testSpecialWordsUsingAbbr()
    {
        $this->assertEquals('dfscdzz123r', $this->pinyin->abbr('Ⅲ度房室传导阻滞123人', PINYIN_KEEP_ENGLISH | PINYIN_KEEP_NUMBER));
    }

    // test name to abbr
    public function testNamesUsingAbbr()
    {
        $this->assertEquals('stf', $this->pinyin->abbr('单田芳', \PINYIN_NAME));
    }

    // test Polyphone
    public function testPolyphone()
    {
        // 了
        $this->assertEquals(['liǎo', 'rán'], $this->pinyin->convert('了然', PINYIN_TONE));
        $this->assertEquals(['lái', 'le'], $this->pinyin->convert('来了', PINYIN_TONE));

        // 还
        $this->assertEquals(['hái', 'yǒu'], $this->pinyin->convert('还有', PINYIN_TONE));
        $this->assertEquals(['jiāo', 'huán'], $this->pinyin->convert('交还', PINYIN_TONE));

        // 什
        $this->assertEquals(['shén', 'me'], $this->pinyin->convert('什么', PINYIN_TONE));
        $this->assertEquals(['shí', 'jǐn'], $this->pinyin->convert('什锦', PINYIN_TONE));

        // 便
        $this->assertEquals(['biàn', 'dāng'], $this->pinyin->convert('便当', PINYIN_TONE));
        $this->assertEquals(['pián', 'yí'], $this->pinyin->convert('便宜', PINYIN_TONE));

        // 剥
        $this->assertEquals(['bō', 'xuē'], $this->pinyin->convert('剥削', PINYIN_TONE));
        $this->assertEquals(['bāo', 'pí', 'qì'], $this->pinyin->convert('剥皮器', PINYIN_TONE));

        // 不
        $this->assertEquals(['péi', 'bú', 'shì'], $this->pinyin->convert('赔不是', PINYIN_TONE));
        $this->assertEquals(['pǎo', 'le', 'hé', 'shàng', 'pǎo', 'bù', 'liǎo', 'miào'], $this->pinyin->convert('跑了和尚，跑不了庙', PINYIN_TONE));

        // 降
        $this->assertEquals(['jiàng', 'wēn'], $this->pinyin->convert('降温', PINYIN_TONE));
        $this->assertEquals(['tóu', 'xiáng'], $this->pinyin->convert('投降', PINYIN_TONE));

        // 都
        $this->assertEquals(['shǒu', 'dū'], $this->pinyin->convert('首都', PINYIN_TONE));
        $this->assertEquals(['dōu', 'shén', 'me', 'nián', 'dài', 'le'], $this->pinyin->convert('都什么年代了', PINYIN_TONE));

        // 乐
        $this->assertEquals(['kuài', 'lè'], $this->pinyin->convert('快乐', PINYIN_TONE));
        $this->assertEquals(['yīn', 'yuè'], $this->pinyin->convert('音乐', PINYIN_TONE));

        // ⺁
        $this->assertEquals(['fǎn'], $this->pinyin->convert('⺁', PINYIN_TONE));

        // 长
        $this->assertEquals(['chéng', 'zhǎng'], $this->pinyin->convert('成长', PINYIN_TONE));
        $this->assertEquals(['cháng', 'jiāng'], $this->pinyin->convert('长江', PINYIN_TONE));

        // 难
        $this->assertEquals(['nàn', 'mín'], $this->pinyin->convert('难民', PINYIN_TONE));
        $this->assertEquals(['nán', 'guò'], $this->pinyin->convert('难过', PINYIN_TONE));

        // 厦
        $this->assertEquals(['dà', 'shà'], $this->pinyin->convert('大厦', PINYIN_TONE));
        $this->assertEquals(['xià', 'mén'], $this->pinyin->convert('厦门', PINYIN_TONE));

        // 曾
        $this->assertEquals(['céng', 'jīng'], $this->pinyin->convert('曾经', PINYIN_TONE));
        $this->assertEquals(['xìng', 'zēng'], $this->pinyin->convert('姓曾', PINYIN_TONE));
        $this->assertEquals(['zēng', 'xìng'], $this->pinyin->convert('曾姓', PINYIN_TONE));

        // 奇
        $this->assertEquals(['qí', 'guài'], $this->pinyin->convert('奇怪', PINYIN_TONE));
        $this->assertEquals(['jī', 'ǒu', 'jiào', 'yàn'], $this->pinyin->convert('奇偶校验', PINYIN_TONE));

        // 其它多音词
        $this->assertEquals(['náo', 'zhí', 'wéi', 'qū'], $this->pinyin->convert('挠直为曲', PINYIN_TONE));
        $this->assertEquals(['pī', 'fēng', 'mò', 'yuè'], $this->pinyin->convert('批风抹月', PINYIN_TONE));
    }

    /**
     * 测试字母+数字Bug.
     */
    public function testNumberWithAlpha()
    {
        $this->assertEquals('cè shì R60', $this->pinyin->sentence('测试R60', \PINYIN_TONE));
        $this->assertEquals('ce4 shi4 R60', $this->pinyin->sentence('测试R60', \PINYIN_ASCII_TONE));
        $this->assertEquals('ce shi R60', $this->pinyin->sentence('测试R60'));
        $this->assertEquals('ce ce5 shi Rr60', $this->pinyin->sentence('测ce5试Rr60'));
        $this->assertEquals('ce shi R50', $this->pinyin->sentence('测试R50'));
        $this->assertEquals('ce 3sh4i R50', $this->pinyin->sentence('测3sh4i R50'));
        $this->assertEquals('ce3sh4i R50', $this->pinyin->sentence('ce3sh4i R50'));
        $this->assertEquals('33ai4', $this->pinyin->sentence('33ai4'));
        $this->assertEquals('33ai4 ni', $this->pinyin->sentence('33ai4你'));
        $this->assertEquals('ai 334 ni', $this->pinyin->sentence('爱334你'));
        $this->assertEquals('aaaa1234', $this->pinyin->sentence('aaaa1234'));
        $this->assertEquals('aaaa_1234', $this->pinyin->sentence('aaaa_1234'));
        $this->assertEquals('ai45 liao wu sheng qu ce shi', $this->pinyin->sentence('ai45了无生趣测试'));
        $this->assertEquals('java gong cheng shi', $this->pinyin->sentence('java工程师'));
    }

    public function testPhrase()
    {
        $this->assertEquals('bei3-jing1', $this->pinyin->phrase('北京', '-', \PINYIN_ASCII_TONE));
    }

    public function testIssues()
    {
        $this->assertEquals('a le tai', $this->pinyin->sentence('阿勒泰'));
        $this->assertEquals('e er duo si', $this->pinyin->sentence('鄂尔多斯'));
        $this->assertEquals('zu', $this->pinyin->sentence('足'));
        $this->assertEquals('feng', $this->pinyin->sentence('冯'));
        $this->assertEquals('bao hu ping he', $this->pinyin->sentence('暴虎冯河'));
        $this->assertEquals('hé', $this->pinyin->sentence('和', PINYIN_TONE));
        $this->assertEquals('gěi', $this->pinyin->sentence('给', PINYIN_TONE));
        $this->assertEquals('là', $this->pinyin->sentence('腊', PINYIN_TONE));
        // # 28 词库不全
        $this->assertEquals('kun', $this->pinyin->sentence('堃'));

        // #29
        $this->assertEquals('dì', $this->pinyin->sentence('地', PINYIN_TONE));
        $this->assertEquals('zhi yu si di', $this->pinyin->sentence('置于死地'));

        // #35
        $this->assertEquals('ji xiao', $this->pinyin->sentence('技校'));
        $this->assertEquals('jiao zheng', $this->pinyin->sentence('校正'));

        // #45
        $this->assertEquals('luó', $this->pinyin->sentence('罗', PINYIN_TONE));

        // #58
        $this->assertEquals('fēi', $this->pinyin->sentence('飞', PINYIN_TONE));
        $this->assertEquals('jiāng', $this->pinyin->sentence('将', PINYIN_TONE));
        $this->assertEquals('míng jiàng', $this->pinyin->sentence('名将', PINYIN_TONE));
        $this->assertEquals('dì wáng jiàng xiàng', $this->pinyin->sentence('帝王将相', PINYIN_TONE));
        $this->assertEquals('dì wáng jiàng xiàng', $this->pinyin->sentence('帝王将相', PINYIN_TONE));
        $this->assertEquals('wèi shǒu wèi wěi', $this->pinyin->sentence('畏首畏尾', PINYIN_TONE));

        // #63
        $this->assertEquals('shěn', $this->pinyin->sentence('沈', PINYIN_TONE));
        $this->assertEquals('shěn yáng', $this->pinyin->sentence('沈阳', PINYIN_TONE));
        $this->assertEquals('chén yú luò yàn', $this->pinyin->sentence('沈鱼落雁', PINYIN_TONE));

        // #81
        $this->assertEquals('yuán yùn', $this->pinyin->sentence('圆晕', PINYIN_TONE));
        $this->assertEquals('guāng yùn', $this->pinyin->sentence('光晕', PINYIN_TONE));
        $this->assertEquals('yūn jué', $this->pinyin->sentence('晕厥', PINYIN_TONE));

        // #105 #112
        $this->assertEquals('ǹ', $this->pinyin->sentence('嗯', PINYIN_TONE));

        // #167
        $this->assertEquals('chǔ', $this->pinyin->sentence('褚', PINYIN_TONE));

        // #174
        $this->assertEquals('tuò', $this->pinyin->sentence('拓', PINYIN_TONE));

        // #146
        $this->assertEquals('zhōng、wén', $this->pinyin->sentence('中、文', PINYIN_TONE));

        // #96
        $this->assertEquals('shén me', $this->pinyin->sentence('什么', PINYIN_TONE));
        $this->assertEquals('hái shuō shí mǒ ne？huán gěi nǐ。hái gè pì！', $this->pinyin->sentence('还说什么呢？还给你。还个屁！', \PINYIN_TONE));

        // #82
        $this->assertEquals('wū lā tè qián qí', $this->pinyin->sentence('乌拉特前旗', PINYIN_TONE));
        $this->assertEquals('cháo yáng qū', $this->pinyin->sentence('朝阳区', PINYIN_TONE));
        $this->assertEquals('jì xī xiàn', $this->pinyin->sentence('绩溪县', PINYIN_TONE));
        $this->assertEquals('bǎi sè xiàn', $this->pinyin->sentence('百色县', PINYIN_TONE));
        $this->assertEquals('dū ān yáo zú zì zhì xiàn', $this->pinyin->sentence('都安瑶族自治县', PINYIN_TONE));
        $this->assertEquals('tǎ shí kù ěr gān', $this->pinyin->sentence('塔什库尔干', PINYIN_TONE));
        $this->assertEquals('cháng yáng tǔ jiā zú zì zhì xiàn', $this->pinyin->sentence('长阳土家族自治县', PINYIN_TONE));
        $this->assertEquals('mǎ wěi qū', $this->pinyin->sentence('马尾区', PINYIN_TONE));
        $this->assertEquals('wèi shǒu wèi wěi', $this->pinyin->sentence('畏首畏尾', PINYIN_TONE));
        $this->assertEquals('sān dū shuǐ zú zì zhì xiàn', $this->pinyin->sentence('三都水族自治县', PINYIN_TONE));

        $this->assertEquals(['lyu', 'xiu', 'cai'], $this->pinyin->convert('吕秀才'));
        $this->assertEquals(['lv', 'xiu', 'cai'], $this->pinyin->convert('吕秀才', \PINYIN_UMLAUT_V));

        #175
        $this->assertEquals('yuán', $this->pinyin->sentence('貟', PINYIN_TONE));
        $this->assertEquals(['yun', 'xiu', 'cai'], $this->pinyin->name('貟秀才'));
        $this->assertEquals(['yun', 'xiu', 'cai'], $this->pinyin->name('贠秀才'));

        #183
        $this->assertEquals('yín háng quàn', $this->pinyin->sentence('银行券', PINYIN_TONE));
        $this->assertEquals('xún chá', $this->pinyin->sentence('询查', PINYIN_TONE));

        #170
        $this->assertEquals('ké', $this->pinyin->sentence('咳', PINYIN_TONE));
        $this->assertEquals('xiǎo ér fèi ké kē lì', $this->pinyin->sentence('小儿肺咳颗粒', PINYIN_TONE));

        #151
        $this->assertEquals('gǔ tóu', $this->pinyin->sentence('骨头', PINYIN_TONE));

        #116
        $this->assertEquals(['shan', 'mou', 'mou'], $this->pinyin->name('单某某', \PINYIN_UMLAUT_V));

        #106
        $this->assertEquals('zhēn huán zhuàn', $this->pinyin->sentence('甄嬛传', PINYIN_TONE));
        $this->assertEquals('chuán qí', $this->pinyin->sentence('传奇', PINYIN_TONE));
        $this->assertEquals('zhuàn jì', $this->pinyin->sentence('传记', PINYIN_TONE));
        $this->assertEquals('liú mèng qián', $this->pinyin->sentence('刘孟乾', PINYIN_TONE));

        #164
        $this->assertEquals(['ōu', 'mǒu', 'mǒu'], $this->pinyin->name('区某某', PINYIN_TONE));
        $this->assertEquals(['yuè', 'mǒu', 'mǒu'], $this->pinyin->name('乐某某', PINYIN_TONE));
    }
}
