<?php
declare (strict_types = 1);

namespace tenglin\integrate;

/**
 * == ArrayToXml ==
 * Helper::ArrayToXml(array $array, string $rootElement = '', boolean $spacesKey = true, string $encoding = null, string $version = 1.0, array $attr = [], string $sta = null)->toXml();
 * Helper::ArrayToXml()->convert(array $array, string $rootElement = '', boolean $spacesKey = true, string $encoding = null, string $version = 1.0, array $attr = [], string $sta = null);
 *
 * == XmlToArray ==
 * Helper::XmlToArray(string $xml)->toArray();
 * Helper::XmlToArray()->convert(string $xml);
 *
 * == IP ==
 * Helper::Ip(string $ip)->toArray(); //or ->toJson() or ->toXml()
 * Helper::Ip()->convert(string $ip, string $type) // json,xml,array
 *
 * == Pinyin ==
 * Helper::Pinyin()->word(string)->option()->toString();
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
