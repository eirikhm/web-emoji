<?php

require_once '../../vendor/autoload.php';
require_once '../Emojificator.php';

class EmojificatorTest extends PHPUnit_Framework_TestCase
{
    public function testThatInstantiates()
    {
        $path = '../../data';
        $e = new Emojificator($path);
        $this->assertNotNull($e);
    }

    public function testThatItReplacesSymbolsCorrectly()
    {
        $path = '../../data';
        $e = new Emojificator($path);

        $this->assertEquals(":sunny:", $e->emoji2text("☀️"));
        $this->assertEquals(":beer:", $e->emoji2text("🍺"));
        $this->assertEquals(":snowflake:", $e->emoji2text("❄️"));
        $this->assertEquals(":small_blue_diamond:", $e->emoji2text("🔹"));
        $this->assertEquals(":coffee:", $e->emoji2text("☕️"));
        $this->assertEquals(":warning:", $e->emoji2text("⚠️"));
        $this->assertEquals(":de:", $e->emoji2text("🇩🇪"));
        $this->assertEquals(":non-potable_water:", $e->emoji2text("🚱"));
    }

    public function testThatItReplacesSymbolsCorrectlyInSentence()
    {
        $path = '../../data';
        $e = new Emojificator($path);
        $this->assertEquals($e->emoji2text("I want a 🍺"), "I want a :beer:");
        $this->assertEquals($e->emoji2text("🍺 I want"), ":beer: I want");
    }

    public function testThatItCreatesHtmlForEachEntry()
    {
        $path = '../../data';
        $emojiData = json_decode(file_get_contents($path . '/emojiNames.json'), true);
        $e = new Emojificator($path);

        foreach ($emojiData as $name => $value) {
            $markup = $e->text2html(':' . $name . ':');
            $this->assertTrue(strpos($markup, '<span') === 0, "Markup for $name is incorrect");
        }
    }

    public function testThatItReplacesAllSymbolsCorrectly()
    {
        $path = '../../data';
        $emojiData = json_decode(file_get_contents($path . '/emoji.json'), true);
        $e = new Emojificator($path);
        foreach ($emojiData as $emoji)
        {
            $symbolToCheck = $emoji[0];
            $expectedName = ':' . $emoji[3][0] . ':';
            $actualName = $e->emoji2text($symbolToCheck);
            $this->assertEquals($expectedName, $actualName, 'We expected ' . $expectedName);
            $this->assertFalse(strpos($actualName, '"'));
        }
    }

    public function testThatItCreatesHtmlForCertainSymbols()
    {
        $path = '../../data';
        $e = new Emojificator($path);
        $markup = $e->text2html(':+1:');
        $this->assertTrue(strpos($markup, '<span') === 0, "Markup for :+1: is incorrect");
    }

    public function testThatItRespectLength()
    {
        $path = '../../data';
        $e = new Emojificator($path);

        $this->assertEquals('I want a',$e->text2html('I want a :beer:',8));

        $expected = <<< EOF
I want a <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> right
EOF;
       $this->assertEquals($expected,$e->text2html('I want a :beer: right now, ok?',16));


        $expected = <<< EOF
I want a <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span>
EOF;
        $this->assertEquals($expected,$e->text2html('I want a :beer: right now, ok?',10));


        $expected = <<< EOF
<span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> and
EOF;
        $this->assertEquals($expected,$e->text2html(':beer: and :beer: and :beer:',5));

        $expected = <<< EOF
<span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> and <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span>
EOF;
        $this->assertEquals($expected,$e->text2html(':beer: and :beer: and :beer:',7));

        $expected = <<< EOF
<span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> and <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> and <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span>
EOF;
        $this->assertEquals($expected,$e->text2html(':beer: and :beer: and :beer:',13));
    }

    public function testThatItReportsCorrectStringLengthForEmojiString()
    {
        $path = '../../data';
        $e = new Emojificator($path);
        $textLength = $e->getTextLength(':beer: and :rage:');
        $this->assertEquals($textLength,7);
    }

    public function testThatItIgnoresUnknownStrings()
    {
        $path = '../../data';
        $e = new Emojificator($path);

        $this->assertEquals(':unknownabc: and :unknownabc123:',$e->text2Html(':unknownabc: and :unknownabc123:'));
        $this->assertEquals(':unkn',$e->text2Html(':unknownabc: and :unknownabc123:',5));
    }

    public function testThatMultipleEmojiTextsWithNoSpacingResolveCorrectly()
    {
        $path = '../../data';
        $e = new Emojificator($path);
        $markup = $e->text2html(':beer::beer');
        $this->assertTrue(strpos($markup, '<span') === 0, "Markup for :beer::beer: is incorrect");

    }
}