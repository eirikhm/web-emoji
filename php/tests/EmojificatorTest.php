<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Emojificator.php';

class EmojificatorTest extends PHPUnit_Framework_TestCase
{
    private $dataPath;

    public function setUp()
    {
        $this->dataPath = __DIR__ . '/../../data';
    }

    public function testThatInstantiates()
    {
        $e = new Emojificator($this->dataPath);
        $this->assertNotNull($e);
    }

    public function testThatItReplacesSymbolsCorrectly()
    {
        $e = new Emojificator($this->dataPath);

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
        $e = new Emojificator($this->dataPath);
        $this->assertEquals($e->emoji2text("I want a 🍺"), "I want a :beer:");
        $this->assertEquals($e->emoji2text("🍺 I want"), ":beer: I want");
    }

    public function testThatItCreatesHtmlForEachEntry()
    {
        $emojiData = json_decode(file_get_contents($this->dataPath . '/emojiNames.json'), true);
        $e = new Emojificator($this->dataPath);

        foreach ($emojiData as $name => $value) {
            $markup = $e->text2html(':' . $name . ':');
            $this->assertTrue(strpos($markup, '<span') === 0, "Markup for $name is incorrect");
        }
    }

    public function testThatItReplacesAllSymbolsCorrectly()
    {
        $emojiData = json_decode(file_get_contents($this->dataPath . '/emoji.json'), true);
        $e = new Emojificator($this->dataPath);
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
        $e = new Emojificator($this->dataPath);
        $markup = $e->text2html(':+1:');
        $this->assertTrue(strpos($markup, '<span') === 0, "Markup for :+1: is incorrect");
    }

    public function testThatItRespectLength()
    {
        $e = new Emojificator($this->dataPath);

        $this->assertEquals('I want a...',$e->text2html('I want a :beer:',8));

        $expected = <<< EOF
I want a <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> right...
EOF;
       $this->assertEquals($expected,$e->text2html('I want a :beer: right now, ok?',16));


        $expected = <<< EOF
I want a <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span>...
EOF;
        $this->assertEquals($expected,$e->text2html('I want a :beer: right now, ok?',10));


        $expected = <<< EOF
<span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> and...
EOF;
        $this->assertEquals($expected,$e->text2html(':beer: and :beer: and :beer:',5));

        $expected = <<< EOF
<span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> and <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span>...
EOF;
        $this->assertEquals($expected,$e->text2html(':beer: and :beer: and :beer:',7));

        $expected = <<< EOF
<span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> and <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span> and <span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="background: url(/img/emoji_apple_64_indexed_256colors.png);background-position:27.586206896552% 51.724137931034%;background-size:3000%"></span></span>
EOF;
        $this->assertEquals($expected,$e->text2html(':beer: and :beer: and :beer:',13));
    }

    public function testThatItReportsCorrectStringLengthForEmojiString()
    {
        $e = new Emojificator($this->dataPath);
        $textLength = $e->getTextLength(':beer: and :rage:');
        $this->assertEquals($textLength,7);
    }

    public function testThatMultipleEmojiTextsWithNoSpacingResolveCorrectly()
    {
        $e = new Emojificator($this->dataPath);
        $markup = $e->text2html(':beer::beer');
        $this->assertTrue(strpos($markup, '<span') === 0, "Markup for :beer::beer: is incorrect");

    }

    public function testThatItDetectsPresenceOfEmoji()
    {
        $e = new Emojificator($this->dataPath);
        $this->assertTrue($e->textContainsEmoji("Contains 🍺"));
    }

    public function testThatItDontDoFalseEmojiPresenceDetects()
    {
        $e = new Emojificator($this->dataPath);
        $this->assertFalse($e->textContainsEmoji("Contains beer"));
        $this->assertFalse($e->textContainsEmoji("Contains :beer:"));
        $this->assertFalse($e->textContainsEmoji("Contains æ"));
    }

    public function testWillNotChangeLocale()
    {
        // Get the original locale
        $currentLocale = setlocale(LC_NUMERIC,0);

        setlocale(LC_NUMERIC, 'nb_NO.utf8');

        $e = new Emojificator($this->dataPath);
        $e->text2html(':beer:');

        $this->assertEquals('nb_NO.utf8', setlocale(LC_NUMERIC,0));

        // Reset locale back to original
        setlocale(LC_NUMERIC, $currentLocale);
    }

    public function testThatEmptyStringDoesNotIdentifyAsContainingEmoji()
    {
        $e = new Emojificator($this->dataPath);
        $this->assertFalse($e->textContainsEmoji(''));
    }

    public function testThatNullDoesNotIdentifyAsContainingEmoji()
    {
        $e = new Emojificator($this->dataPath);
        $this->assertFalse($e->textContainsEmoji(null));
    }
}
