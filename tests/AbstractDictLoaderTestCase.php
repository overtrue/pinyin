<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\Pinyin\Test;

use PHPUnit_Framework_TestCase;

abstract class AbstractDictLoaderTestCase extends PHPUnit_Framework_TestCase
{
    protected $pinyin;

    public function testConvert()
    {
        $pinyin = $this->pinyin;

        $this->assertSame(array('nin', 'hao', '2018i', 'New', 'Year'), $pinyin->convert('您好&^2018i New Year!√ç', \PINYIN_KEEP_NUMBER | \PINYIN_KEEP_ENGLISH));
        $this->assertSame(array('nin', 'hao', 'i', 'New', 'Year'), $pinyin->convert('您好&^2018i New Year!√ç', \PINYIN_KEEP_ENGLISH));
        $this->assertSame(array('nin', 'hao', '2018'), $pinyin->convert('您好&^2018i New Year!√ç', \PINYIN_KEEP_NUMBER));

        $this->assertSame(array('nin', 'hao'), $pinyin->convert('您好!'));
        $this->assertSame(array('nin', 'hao'), $pinyin->convert('您好!', PINYIN_DEFAULT));
        $this->assertSame(array('nin2', 'hao3'), $pinyin->convert('您好!', PINYIN_ASCII_TONE));
        $this->assertSame(array('nín', 'hǎo'), $pinyin->convert('您好!', PINYIN_TONE));
        $this->assertSame(array('nín', 'hǎo', '2018'), $pinyin->convert('您好2018!', PINYIN_KEEP_NUMBER | PINYIN_TONE));
        $this->assertSame(array('nín', 'hǎo'), $pinyin->convert('您好 New Year!', PINYIN_KEEP_NUMBER | PINYIN_TONE));
    }

    public function testPermalink()
    {
        $pinyin = $this->pinyin;

        $this->assertSame('dai-zhe-xi-wang-qu-lyu-xing', $pinyin->permalink('带着希望去旅行'));
        $this->assertSame('dai_zhe_xi_wang_qu_lyu_xing', $pinyin->permalink('带着希望去旅行', '_'));
        $this->assertSame('dai.zhe.xi.wang.qu.lyu.xing', $pinyin->permalink('带着希望去旅行', '.'));
        $this->assertSame('daizhexiwangqulyuxing', $pinyin->permalink('带着希望去旅行', ''));

        // with number.
        $this->assertSame('1-dai-23-zhe-56-xi-wang-qu-abc-lyu-xing-568', $pinyin->permalink('1带23着。！5_6.=希望去abc旅行568'));

        $this->setExpectedException('InvalidArgumentException', "Delimiter must be one of: '_', '-', '', '.'.");

        $this->assertSame('daizhexiwangqulyuxing', $pinyin->permalink('带着希望去旅行', '='));
    }

    public function testAbbr()
    {
        $pinyin = $this->pinyin;

        $this->assertSame('dzxwqlx', $pinyin->abbr('带着希望去旅行'));
        $this->assertSame('d z x w q l x', $pinyin->abbr('带着希望去旅行', ' '));
        $this->assertSame('d*z*x*w*q*l*x', $pinyin->abbr('带着希望去旅行', '*'));
        $this->assertSame('d--z--x--w--q--l--x', $pinyin->abbr('带着希望去旅行', '--'));

        // issue #27
        $this->assertEquals('dldnr', $pinyin->abbr('独立的女人'));
        $this->assertEquals('n', $pinyin->abbr('女'));
        $this->assertEquals('nr', $pinyin->abbr('女人'));

        // #91 #101
        $this->assertEquals('nh2017', $pinyin->abbr('你好2017！', \PINYIN_KEEP_NUMBER));
        $this->assertEquals('HNY2018', $pinyin->abbr('Happy New Year! 2018！', \PINYIN_KEEP_NUMBER | \PINYIN_KEEP_ENGLISH));
    }

    public function testSentence()
    {
        $pinyin = $this->pinyin;

        $this->assertSame('dai zhe xi wang qu lyu xing, bi dao da zhong dian geng mei hao!', $pinyin->sentence('带着希望去旅行，比到达终点更美好！'));

//        $this->assertSame('dai zhe xi 123 wang qu good lyu boy2 xing!.', $pinyin->sentence('带^着&*希123望去good旅boy2行！.'));
//        $this->assertSame('dai zhe xi 123 wang.. qu good lyu boy2 xing!.', $pinyin->sentence('--带^着&*希123望.。去good旅boy2行！.'));
    }

    public function testName()
    {
        $pinyin = $this->pinyin;

        $this->assertSame(array('shan'), $pinyin->name('单'));
        $this->assertSame(array('gu', 'dan'), $pinyin->name('孤单'));
        $this->assertSame(array('shan', 'dan', 'dan'), $pinyin->name('单单单'));
        $this->assertSame(array('chan', 'yu', 'dan'), $pinyin->name('单于单'));
        $this->assertSame(array('piao', 'dan', 'pu'), $pinyin->name('朴单朴'));
        $this->assertSame(array('yu', 'chi', 'pu'), $pinyin->name('尉迟朴'));
        $this->assertSame(array('wei', 'mou', 'mou'), $pinyin->name('尉某某'));
        $this->assertSame(array('yu', 'chi', 'mou', 'mou'), $pinyin->name('尉迟某某'));
        $this->assertSame(array('zhai', 'di', 'di'), $pinyin->name('翟翟翟'));

        // 以下两词在任何位置都不变
        $this->assertSame(array('mou', 'mou', 'yu', 'chi'), $pinyin->name('某某尉迟'));
        $this->assertSame(array('shan', 'chan', 'yu', 'dan'), $pinyin->name('单单于单'));
    }

    // test special words
    public function testSpecialWords()
    {
        $pinyin = $this->pinyin;

        $this->assertEquals('hello, world!', $pinyin->sentence('hello, world!'));
        $this->assertEquals('DNA jiàn dìng', $pinyin->sentence('DNA鉴定', \PINYIN_TONE));
        $this->assertEquals('21 sān tǐ zōng hé zhèng', $pinyin->sentence('21三体综合症', \PINYIN_TONE));
        $this->assertEquals('C pán', $pinyin->sentence('C盘', \PINYIN_TONE));
        $this->assertEquals('G diǎn', $pinyin->sentence('G点', \PINYIN_TONE));
        $this->assertEquals('zhōng dù xìng fèi shuǐ zhǒng', $pinyin->sentence('中度性肺水肿', \PINYIN_TONE));
    }

    // test Polyphone
    public function testPolyphone()
    {
        $pinyin = $this->pinyin;

        // 了
        $this->assertEquals(array('liǎo', 'rán'), $pinyin->convert('了然', PINYIN_TONE));
        $this->assertEquals(array('lái', 'le'), $pinyin->convert('来了', PINYIN_TONE));

        // 还
        $this->assertEquals(array('hái', 'yǒu'), $pinyin->convert('还有', PINYIN_TONE));
        $this->assertEquals(array('jiāo', 'huán'), $pinyin->convert('交还', PINYIN_TONE));

        // 什
        $this->assertEquals(array('shén', 'me'), $pinyin->convert('什么', PINYIN_TONE));
        $this->assertEquals(array('shí', 'jǐn'), $pinyin->convert('什锦', PINYIN_TONE));

        // 便
        $this->assertEquals(array('biàn', 'dāng'), $pinyin->convert('便当', PINYIN_TONE));
        $this->assertEquals(array('pián', 'yí'), $pinyin->convert('便宜', PINYIN_TONE));

        // 剥
        $this->assertEquals(array('bō', 'xuē'), $pinyin->convert('剥削', PINYIN_TONE));
        $this->assertEquals(array('bāo', 'pí', 'qì'), $pinyin->convert('剥皮器', PINYIN_TONE));

        // 不
        $this->assertEquals(array('péi', 'bú', 'shì'), $pinyin->convert('赔不是', PINYIN_TONE));
        $this->assertEquals(array('pǎo', 'le', 'hé', 'shàng', 'pǎo', 'bù', 'liǎo', 'miào'), $pinyin->convert('跑了和尚，跑不了庙', PINYIN_TONE));

        // 降
        $this->assertEquals(array('jiàng', 'wēn'), $pinyin->convert('降温', PINYIN_TONE));
        $this->assertEquals(array('tóu', 'xiáng'), $pinyin->convert('投降', PINYIN_TONE));

        // 都
        $this->assertEquals(array('shǒu', 'dū'), $pinyin->convert('首都', PINYIN_TONE));
        $this->assertEquals(array('dōu', 'shén', 'me', 'nián', 'dài', 'le'), $pinyin->convert('都什么年代了', PINYIN_TONE));

        // 乐
        $this->assertEquals(array('kuài', 'lè'), $pinyin->convert('快乐', PINYIN_TONE));
        $this->assertEquals(array('yīn', 'yuè'), $pinyin->convert('音乐', PINYIN_TONE));

        // 长
        $this->assertEquals(array('chéng', 'zhǎng'), $pinyin->convert('成长', PINYIN_TONE));
        $this->assertEquals(array('cháng', 'jiāng'), $pinyin->convert('长江', PINYIN_TONE));

        // 难
        $this->assertEquals(array('nàn', 'mín'), $pinyin->convert('难民', PINYIN_TONE));
        $this->assertEquals(array('nán', 'guò'), $pinyin->convert('难过', PINYIN_TONE));

        // 厦
        $this->assertEquals(array('dà', 'shà'), $pinyin->convert('大厦', PINYIN_TONE));
        $this->assertEquals(array('xià', 'mén'), $pinyin->convert('厦门', PINYIN_TONE));

        // 曾
        $this->assertEquals(array('céng', 'jīng'), $pinyin->convert('曾经', PINYIN_TONE));
        $this->assertEquals(array('xìng', 'zēng'), $pinyin->convert('姓曾', PINYIN_TONE));
        $this->assertEquals(array('zēng', 'xìng'), $pinyin->convert('曾姓', PINYIN_TONE));

        // 奇
        $this->assertEquals(array('qí', 'guài'), $pinyin->convert('奇怪', PINYIN_TONE));
        $this->assertEquals(array('jī', 'ǒu', 'jiào', 'yàn'), $pinyin->convert('奇偶校验', PINYIN_TONE));

        // 其它多音词
        $this->assertEquals(array('náo', 'zhí', 'wéi', 'qū'), $pinyin->convert('挠直为曲', PINYIN_TONE));
        $this->assertEquals(array('pī', 'fēng', 'mò', 'yuè'), $pinyin->convert('批风抹月', PINYIN_TONE));
    }

    /**
     * 测试字母+数字Bug.
     */
    public function testNumberWithAlpha()
    {
        $pinyin = $this->pinyin;

        $this->assertEquals('cè shì R60', $pinyin->sentence('测试R60', \PINYIN_TONE));
        $this->assertEquals('ce4 shi4 R60', $pinyin->sentence('测试R60', \PINYIN_ASCII_TONE));
        $this->assertEquals('ce shi R60', $pinyin->sentence('测试R60'));
        $this->assertEquals('ce ce5 shi Rr60', $pinyin->sentence('测ce5试Rr60'));
        $this->assertEquals('ce shi R50', $pinyin->sentence('测试R50'));
        $this->assertEquals('ce 3sh4i R50', $pinyin->sentence('测3sh4i R50'));
        $this->assertEquals('ce3sh4i R50', $pinyin->sentence('ce3sh4i R50'));
        $this->assertEquals('33ai4', $pinyin->sentence('33ai4'));
        $this->assertEquals('33ai4 ni', $pinyin->sentence('33ai4你'));
        $this->assertEquals('ai 334 ni', $pinyin->sentence('爱334你'));
        $this->assertEquals('aaaa1234', $pinyin->sentence('aaaa1234'));
        $this->assertEquals('aaaa_1234', $pinyin->sentence('aaaa_1234'));
        $this->assertEquals('ai45 liao wu sheng qu ce shi', $pinyin->sentence('ai45了无生趣测试'));
        $this->assertEquals('java gong cheng shi', $pinyin->sentence('java工程师'));
    }

    /**
     * 测试单个音的字.
     *
     * bug: #19
     * bug: #22
     * bug: #23
     * bug: #24
     * bug: #29
     * bug: #235
     * bug: #81
     */
    public function testSingleAccent()
    {
        $pinyin = $this->pinyin;

        $this->assertEquals('a le tai', $pinyin->sentence('阿勒泰'));
        $this->assertEquals('e er duo si', $pinyin->sentence('鄂尔多斯'));
        $this->assertEquals('zu', $pinyin->sentence('足'));
        $this->assertEquals('feng', $pinyin->sentence('冯'));
        $this->assertEquals('bao hu ping he', $pinyin->sentence('暴虎冯河'));
        $this->assertEquals('hé', $pinyin->sentence('和', PINYIN_TONE));
        $this->assertEquals('gěi', $pinyin->sentence('给', PINYIN_TONE));
        $this->assertEquals('là', $pinyin->sentence('腊', PINYIN_TONE));
        // # 28 词库不全
        $this->assertEquals('kun', $pinyin->sentence('堃'));

        // #29
        $this->assertEquals('dì', $pinyin->sentence('地', PINYIN_TONE));
        $this->assertEquals('zhi yu si di', $pinyin->sentence('置于死地'));

        // #35
        $this->assertEquals('ji xiao', $pinyin->sentence('技校'));
        $this->assertEquals('jiao zheng', $pinyin->sentence('校正'));

        // #45
        $this->assertEquals('luó', $pinyin->sentence('罗', PINYIN_TONE));

        // #58
        $this->assertEquals('fēi', $pinyin->sentence('飞', PINYIN_TONE));
        $this->assertEquals('jiāng', $pinyin->sentence('将', PINYIN_TONE));
        $this->assertEquals('míng jiàng', $pinyin->sentence('名将', PINYIN_TONE));
        $this->assertEquals('dì wáng jiàng xiàng', $pinyin->sentence('帝王将相', PINYIN_TONE));
        $this->assertEquals('dì wáng jiàng xiàng', $pinyin->sentence('帝王将相', PINYIN_TONE));
        $this->assertEquals('wèi shǒu wèi wěi', $pinyin->sentence('畏首畏尾', PINYIN_TONE));

        // #63
        $this->assertEquals('shěn', $pinyin->sentence('沈', PINYIN_TONE));
        $this->assertEquals('shěn yáng', $pinyin->sentence('沈阳', PINYIN_TONE));
        $this->assertEquals('chén yú luò yàn', $pinyin->sentence('沈鱼落雁', PINYIN_TONE));

        // #81
        $this->assertEquals('yuán yùn', $pinyin->sentence('圆晕', PINYIN_TONE));
        $this->assertEquals('guāng yùn', $pinyin->sentence('光晕', PINYIN_TONE));
        $this->assertEquals('yūn jué', $pinyin->sentence('晕厥', PINYIN_TONE));

        // #105 #112
        $this->assertEquals('èn', $pinyin->sentence('嗯', PINYIN_TONE));

        // #96
        $this->assertEquals('shén me', $pinyin->sentence('什么', PINYIN_TONE));
        $this->assertEquals('hái shuō shén me ne? huán gěi nǐ. hái gè pì!', $pinyin->sentence('还说什么呢？还给你。还个屁！', \PINYIN_TONE));

        // #82
        $this->assertEquals('wū lā tè qián qí', $pinyin->sentence('乌拉特前旗', PINYIN_TONE));
        $this->assertEquals('cháo yáng qū', $pinyin->sentence('朝阳区', PINYIN_TONE));
        $this->assertEquals('jì xī xiàn', $pinyin->sentence('绩溪县', PINYIN_TONE));
        $this->assertEquals('bǎi sè xiàn', $pinyin->sentence('百色县', PINYIN_TONE));
        $this->assertEquals('dū ān yáo zú zì zhì xiàn', $pinyin->sentence('都安瑶族自治县', PINYIN_TONE));
        $this->assertEquals('tǎ shí kù ěr gān', $pinyin->sentence('塔什库尔干', PINYIN_TONE));
        $this->assertEquals('cháng yáng tǔ jiā zú zì zhì xiàn', $pinyin->sentence('长阳土家族自治县', PINYIN_TONE));
        $this->assertEquals('mǎ wěi qū', $pinyin->sentence('马尾区', PINYIN_TONE));
        $this->assertEquals('sān dū shuǐ zú zì zhì xiàn', $pinyin->sentence('三都水族自治县', PINYIN_TONE));
    }

    public function testYu()
    {
        $this->assertEquals(array('lyu', 'xiu', 'cai'), $this->pinyin->convert('吕秀才'));
        $this->assertEquals(array('lv', 'xiu', 'cai'), $this->pinyin->convert('吕秀才', \PINYIN_UMLAUT_V));
    }

    public function testPhrase()
    {
        $pinyin = $this->pinyin;
        $this->assertEquals('bei3-jing1', $pinyin->phrase('北京', '-', \PINYIN_ASCII_TONE));
    }
}
