<?php

//include __DIR__ . '../vendor/autoload.php';
include __DIR__ . '/../src/Overtrue/Pinyin.php';

use Overtrue\Pinyin;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected $pinyin;

    public function setUp()
    {
        # code...
    }

    // test delimiter
    public function testDelimiter()
    {
        $this->assertEquals('nín hǎo', Pinyin::pinyin('您好'));
    }

    // test temporary changes delimiter
    public function testTemporaryDelimiter()
    {
        $this->assertEquals('nín-hǎo', Pinyin::pinyin('您好', '-'));
        Pinyin::set('delimiter', '*');
        $this->assertEquals('nín*hǎo', Pinyin::pinyin('您好'));
        $this->assertEquals('nín hǎo', Pinyin::pinyin('您好'));
    }

    // test get first letter
    public function testLetters()
    {
        $this->assertEquals('N H', Pinyin::letter('您好'));
        $this->assertEquals('N-H', Pinyin::letter('您好', '-'));
        $this->assertEquals('NH', Pinyin::letter('您好', ''));
        $this->assertEquals('KXLL', Pinyin::letter('康熙来了', ''));
        $this->assertEquals('D Z X W Q L X B D D Z D G M H', Pinyin::letter('带着希望去旅行，比到达终点更美好'));
        $this->assertEquals('Z Q S L Z W Z W', Pinyin::letter('赵钱孙李 周吴郑王'));
    }

    // test return with tone
    public function testResultWithTone()
    {
        $this->assertEquals('dài zhe xī wàng qù lǚ xíng , bǐ dào dá zhōng diǎn gèng měi hǎo', Pinyin::pinyin('带着希望去旅行，比到达终点更美好'));
    }

    // test without tone
    public function testResultWithoutTone()
    {
        $this->assertEquals('dai zhe xi wang qu lu xing , bi dao da zhong dian geng mei hao', Pinyin::pinyin('带着希望去旅行，比到达终点更美好', ['accent' => false]));
    }

    // test user added words
    public function testAdditionalWords()
    {
        $this->assertEquals('liǎo wú shēng qù', Pinyin::pinyin('了无生趣'));
    }

    // test place name
    public function testPlaceName()
    {
        $this->assertEquals('běi jīng', Pinyin::pinyin('北京'));
        $this->assertEquals('shàng hǎi', Pinyin::pinyin('上海'));
        $this->assertEquals('jiāng sū', Pinyin::pinyin('江苏'));
        $this->assertEquals('guǎng dōng', Pinyin::pinyin('广东'));
        $this->assertEquals('nán jīng', Pinyin::pinyin('南京'));
        $this->assertEquals('hū hé hào tè', Pinyin::pinyin('呼和浩特'));
        $this->assertEquals('gān sù', Pinyin::pinyin('甘肃'));
        $this->assertEquals('níng xià', Pinyin::pinyin('宁夏'));
        $this->assertEquals('yún nán', Pinyin::pinyin('云南'));
        $this->assertEquals('hǎi nán', Pinyin::pinyin('海南'));
        $this->assertEquals('liáo níng', Pinyin::pinyin('辽宁'));
        $this->assertEquals('hēi lóng jiāng', Pinyin::pinyin('黑龙江'));
    }

    // test special words
    public function testSpecialWords()
    {
        $this->assertEquals('hello, world!', Pinyin::pinyin('hello, world!'));
        $this->assertEquals('D N A jiàn dìng', Pinyin::pinyin('DNA鉴定'));
        $this->assertEquals('èr shí yī sān tǐ zōng hé zhèng', Pinyin::pinyin('21三体综合症'));
        $this->assertEquals('C pán', Pinyin::pinyin('C盘'));
        $this->assertEquals('G diǎn', Pinyin::pinyin('G点'));
        $this->assertEquals('zhōng dù xìng fèi shuǐ zhǒng', Pinyin::pinyin('中度性肺水肿'));
    }

    // test Polyphone
    public function testPolyphone()
    {
        // 了
        $this->assertEquals('liǎo rán', Pinyin::pinyin('了然'));
        $this->assertEquals('lái le', Pinyin::pinyin('来了'));

        // 还
        $this->assertEquals('hái yǒu', Pinyin::pinyin('还有'));
        $this->assertEquals('jiāo huán', Pinyin::pinyin('交还'));

        // 什
        $this->assertEquals('shén me', Pinyin::pinyin('什么'));
        $this->assertEquals('shí jǐn', Pinyin::pinyin('什锦'));

        // 便
        $this->assertEquals('biàn dāng', Pinyin::pinyin('便当'));
        $this->assertEquals('pián yí', Pinyin::pinyin('便宜'));

        // 剥
        $this->assertEquals('bāo pí', Pinyin::pinyin('剥皮'));
        $this->assertEquals('bō pí qì', Pinyin::pinyin('剥皮器'));

        // 不
        $this->assertEquals('péi bú shi', Pinyin::pinyin('赔不是'));
        $this->assertEquals('pǎo le hé shàng , pǎo bù liǎo miào', Pinyin::pinyin('跑了和尚，跑不了庙'));

        // 降
        $this->assertEquals('jiàng wēn', Pinyin::pinyin('降温'));
        $this->assertEquals('tóu xiáng', Pinyin::pinyin('投降'));

        // 都
        $this->assertEquals('shǒu dū', Pinyin::pinyin('首都'));
        $this->assertEquals('dōu shén me nián dài le', Pinyin::pinyin('都什么年代了'));

        // 乐
        $this->assertEquals('kuài lè', Pinyin::pinyin('快乐'));
        $this->assertEquals('yīn yuè', Pinyin::pinyin('音乐'));

        // 长
        $this->assertEquals('chéng zhǎng', Pinyin::pinyin('成长'));
        $this->assertEquals('cháng jiāng', Pinyin::pinyin('长江'));

        // 难
        $this->assertEquals('nàn mín', Pinyin::pinyin('难民'));
        $this->assertEquals('nán guò', Pinyin::pinyin('难过'));
    }

}