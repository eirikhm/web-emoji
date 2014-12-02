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

        $this->assertEquals(":sunny:",$e->emoji2text("☀️"));
        $this->assertEquals(":beer:",$e->emoji2text("🍺"));
        $this->assertEquals(":snowflake:",$e->emoji2text("❄️"));
        $this->assertEquals(":small_blue_diamond:",$e->emoji2text("🔹"));
        $this->assertEquals(":coffee:",$e->emoji2text("☕️"));
        $this->assertEquals(":warning:",$e->emoji2text("⚠️"));
        $this->assertEquals(":de:",$e->emoji2text("🇩🇪"));
    }

    public function testThatItReplacesSymbolsCorrectlyInSentence()
    {
        $path = '../../data';
        $e = new Emojificator($path);
        $this->assertEquals($e->emoji2text("I want a 🍺"),"I want a :beer:");
        $this->assertEquals($e->emoji2text("🍺 I want"),":beer: I want");
    }

    public function testThatItReplacesAllSymbolsCorrectly()
    {
        $path = '../../data';
        $emojiData = json_decode(file_get_contents($path. '/emoji.json'), true);
        $e = new Emojificator($path);
        foreach($emojiData as $emoji)
        {
            $symbolToCheck = $emoji[0];
            $expectedName = ':'.$emoji[3][0].':';
            $actualName = $e->emoji2text($symbolToCheck);
            $this->assertEquals($expectedName,$actualName,'We expected '.$expectedName);
            $this->assertFalse(strpos($actualName,'"'));
        }
    }

}