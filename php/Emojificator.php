<?php

class Emojificator
{
    private $emojiData = null;
    private $emojiNames = null;
    private $pattern;

    public function __construct($emojiRoot = null)
    {
        if (!$emojiRoot)
        {
            throw new Exception('Main emoji data folder must be: '.$emojiRoot, 500);
        }

        $mainFile = $emojiRoot . '/emoji.json';
        $namesFile = $emojiRoot . '/emojiNames.json';

        if (!is_readable($mainFile))
        {
            throw new Exception('Could not read main emoji file'.$mainFile, 500);
        }

        if (!is_readable($namesFile))
        {
            throw new Exception('Could not read emoji name file'.$namesFile, 500);
        }

        $this->emojiData = $this->loadArrayFromJsonFile($emojiRoot . '/emoji.json');
        $this->emojiNames = $this->loadArrayFromJsonFile($emojiRoot . '/emojiNames.json');
    }

    /**
     * Replaces :beer: with a <span> for the icon
     * @param $text
     * @return mixed
     */
    public function text2html($text,$length = null)
    {

        $htmlLength = [];
        $completeString =  preg_replace_callback(
            '/:[a-zA-Z0-9+_-]+:/',
            function ($matches) use (&$htmlLength)
            {
                $rawName = $matches[0];
                $name = substr($rawName, 1, -1);
                $code = $this->getEmojiCodeByName($name);
                if (!$code)
                {
                    return $name;
                }
                else
                {
                    $htmlElement = trim($this->getHtmlElementFromEmojiData($code));
                    $htmlLength[$rawName] = strlen($htmlElement)-1;
                    return $htmlElement;
                }
            },
            $text
        );

        if ($length == null)
        {
            return $completeString;
        }

        preg_match_all('/:[a-zA-Z0-9+_-]+:/', $text, $matches2, PREG_OFFSET_CAPTURE);

        $lengthWithElementsIncluded = $length;
        $tmp = 0;
        foreach($matches2[0] as $match)
        {
            $token = $match[0];
            $position = $match[1];
            if ($position < $length+$tmp)
            {
                $lengthWithElementsIncluded += $htmlLength[$token];
                $tmp += strlen($token);
            }
        }


        return substr($completeString,0,$lengthWithElementsIncluded);
    }

    public function getTextLength($text)
    {
        $tmp = preg_replace_callback(
            '/:([a-zA-Z0-9+_-]+):/',
            function ($matches)
            {
                $name = substr($matches[0], 1, -1);
                $code = $this->getEmojiCodeByName($name);
                if (!$code)
                {
                    return $matches[0];
                }
                else
                {
                    return 'X';
                }
            },
            $text
        );

        return strlen($tmp);
    }

    /**
     * Replaces 🍺 with :beer:
     * @param $text
     * @return mixed|string
     */
    public function emoji2text($text)
    {
        return $this->substituteEmojiInText($this->buildDehancePattern(), $text);
    }

    private function buildDehancePattern()
    {
        if ($this->pattern)
        {
            return $this->pattern;
        }

        $pattern = '/';
        foreach ($this->emojiData as $emoji)
        {
            $symbolToCheck = $emoji[0];
            $pattern .= $symbolToCheck . '|';
        }

        $pattern = mb_substr($pattern, 0, -1);
        $pattern .= '/ui';

        $this->pattern = $pattern;
        return $pattern;
    }

    private function loadArrayFromJsonFile($emojiFile)
    {
        return json_decode(file_get_contents($emojiFile), true);
    }

    private function resolveEmoji($sign)
    {
        $charCodeAt0 = $this->utf8CharCodeAt($sign, 0);
        $charCodeAt1 = $this->utf8CharCodeAt($sign, 1);
        $hexCodeForFirstCodePoint = dechex($charCodeAt0);
        $hexCodeForFirstCodePoint2 = dechex($charCodeAt1);

        $hexCodeForFirstCodePoint = strtolower($hexCodeForFirstCodePoint);
        $hexCodeForFirstCodePoint2 = strtolower($hexCodeForFirstCodePoint2);

        if (strlen($hexCodeForFirstCodePoint) < 4)
        {
            $hexCodeForFirstCodePoint = str_pad($hexCodeForFirstCodePoint, 4, "0", STR_PAD_LEFT);
        }

        // TODO: This depends on the range.
        if ($hexCodeForFirstCodePoint2 != 'fe0f' && $hexCodeForFirstCodePoint2 != 0)
        {
            $hexCodeForFirstCodePoint .= '-' . $hexCodeForFirstCodePoint2;

            if (strlen($hexCodeForFirstCodePoint) < 8)
            {
                $hexCodeForFirstCodePoint = str_pad($hexCodeForFirstCodePoint, 8, "0", STR_PAD_LEFT);
            }
        }

        if (!isset($this->emojiData[$hexCodeForFirstCodePoint]))
        {
            return '';
        }
        return $this->emojiData[$hexCodeForFirstCodePoint][3][0];
    }

    private function substituteEmojiInText($pattern, $text)
    {
        return preg_replace_callback(
            $pattern,
            function ($matches)
            {
                $foundEmoji = $matches[0];
                $resolvedName = $this->resolveEmoji($foundEmoji);
                // weird hack for :hash:. It seems to be listed with 2x emojis in list, but cant figure out how.

                if (!$resolvedName)
                {
                    return $matches[0];
                }

                return ':' . $resolvedName . ':';
            },
            $text
        );
    }

    private function utf8CharCodeAt($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');

        if (mb_check_encoding($char, 'UTF-8'))
        {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            return hexdec(bin2hex($ret));
        }
        else
        {
            return null;
        }
    }

    private function getEmojiCodeByName($name)
    {
        if (isset($this->emojiNames[$name]))
        {
            return $this->emojiNames[$name];
        }
        return null;
    }

    private function getHtmlElementFromEmojiData($code)
    {
        $locale = setlocale(LC_NUMERIC,0);
        setlocale(LC_NUMERIC, 'POSIX');
        $data = $this->emojiData[$code];
        $x = $data[4];
        $y = $data[5];
        $rows = 30;
        $img = '/img/emoji_apple_64_indexed_256colors.png';
        $d = 100 / ($rows - 1);
        $b = "background: url(" . $img . ");background-position:" . ($d * $x) . "% " . ($d * $y) . "%;background-size:" . $rows . "00%";
        return '<span class="emoji-outer emoji-sizer"><span class="emoji-inner" style="' . $b . '"' . ">" . "</span></span>";
        setlocale(LC_NUMERIC, $locale);
    }
}
