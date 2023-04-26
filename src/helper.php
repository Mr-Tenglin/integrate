<?php
declare (strict_types = 1);

namespace tenglin\integrate;

/**
 * == IP ==
 * Helper::Ip(string $ip)->toArray(); //or ->toJson() or ->toXml()
 * Helper::Ip()->convert(string $ip, string $type) // json,xml,array
 *
 * == Keyword ==
 * Helper::Keyword(String, Length)->toArray(); //or ->toJson() or ->toXml()
 * Helper::Keyword()->convert(String, Length, 'array');
 *
 * == Pinyin ==
 * Helper::Pinyin()->word(String)->option(PINYIN_TONE|PINYIN_ASCII_TONE|PINYIN_NO_TONE|PINYIN_KEEP_NUMBER|PINYIN_KEEP_ENGLISH|PINYIN_KEEP_PUNCTUATION|PINYIN_UMLAUT_V)->delimiter('-')->name()->toArray();
 * Helper::Pinyin()->convert(String, PINYIN_TONE|PINYIN_ASCII_TONE|PINYIN_NO_TONE|PINYIN_KEEP_NUMBER|PINYIN_KEEP_ENGLISH|PINYIN_KEEP_PUNCTUATION|PINYIN_UMLAUT_V, 'array', 'name');
 *
 * == Xml ==
 * Helper::Xml(Xml Data, Param)->toArray();
 * Helper::Xml(Array Data, Param)->toXml();
 * Helper::Xml()->convert(Array Data, Param); // Automatically identify Array or Xml
 */

class Helper
{
    public static function create_load_function($name, $arguments = [])
    {
        $namespace = ucfirst($name);
        $application = '\\tenglin\\integrate\\handler\\' . $namespace;
        return new $application($arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::create_load_function($name, $arguments);
    }
}
