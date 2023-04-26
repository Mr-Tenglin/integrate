<?php
/**
 * GitHub Project: GeoIP2
 * Copy Project Code: https://github.com/maxmind/GeoIP2-php
 *
 * GitHub Project: ip2region sdk php
 * Copy Project Code: https://github.com/chinayin/ip2region-sdk-php
 */

declare (strict_types = 1);

namespace tenglin\integrate\handler;

use GeoIp2\Database\Reader;
use ip2region\Ip2Region;
use tenglin\integrate\Common;

class Ip
{
    protected $region;

    public function __construct($args = [])
    {
        $ip = (empty($args[0]) ? '' : $args[0]);
        if (is_file(dirname(dirname(__FILE__)) . '/data/GeoLite2-City.mmdb.0.myArchive')) {
            Common::file_combine(dirname(dirname(__FILE__)) . '/data/GeoLite2-City.mmdb', '', true);
        }
        $Ip2Region = Ip2Region::newWithFileOnly();
        list($country, $area, $province, $city, $isp) = explode('|', $Ip2Region->search($ip));
        $GeoIp2 = new Reader(dirname(dirname(__FILE__)) . '/data/GeoLite2-City.mmdb');
        $record = $GeoIp2->city($ip);

        $this->region = [
            'ip' => $ip,
            'inet' => $this->ip2num($ip),
            'continent' => [
                'en' => $record->continent->name,
                'zh-cn' => $record->continent->names['zh-CN'],
            ],
            'country' => [
                'isoCode' => $record->country->isoCode,
                'en' => $record->country->name,
                'zh-cn' => isset($record->country->names['zh-CN']) ? $record->country->names['zh-CN'] : $country,
            ],
            'province' => [
                'isoCode' => $record->mostSpecificSubdivision->isoCode,
                'en' => $record->mostSpecificSubdivision->name,
                'zh-cn' => isset($record->mostSpecificSubdivision->names['zh-CN']) ? $record->mostSpecificSubdivision->names['zh-CN'] : $province,
            ],
            'city' => [
                'en' => $record->city->name,
                'zh-cn' => isset($record->city->names['zh-CN']) ? $record->city->names['zh-CN'] : $city,
            ],
            'location' => [
                'latitude' => $record->location->latitude,
                'longitude' => $record->location->longitude,
                'timeZone' => $record->location->timeZone,
            ],
            'traits' => [
                'ipAddress' => $record->traits->ipAddress,
                'network' => $record->traits->network,
            ],
            'isp' => $isp,
        ];
    }

    public function convert(string $ip, string $type)
    {
        $region = new self($ip);
        if ($type == 'json') {
            return $region->toJson();
        } elseif ($type == 'xml') {
            return $region->toXml();
        } else {
            return $region->toArray();
        }
    }

    public function ip2num($ip = '')
    {
        if (empty($ip)) {
            $ip = $this->region['ip'];
        }
        $ipNum = [];
        $ipArr = explode('.', $this->region['ip']);
        foreach ($ipArr as $num) {
            $ipHex = dechex((int) $num);
            if (strlen($ipHex) < 2) {
                $ipHex = '0' . $ipHex;
            }
            $ipNum[] = $ipHex;
        }
        return hexdec(implode('', $ipNum));
    }

    public function num2ip($num = '')
    {
        if (empty($num)) {
            $num = $this->region['inet'];
        }
        $ipHex = dechex($num);
        $len = strlen($ipHex);
        if (strlen($ipHex) < 8) {
            $ipHex = '0' . $ipHex;
            $len = strlen($ipHex);
        }
        for ($i = 0, $j = 0; $j < $len; $i = $i + 1, $j = $j + 2) {
            $ipPart = substr($ipHex, $j, 2);
            $fiPart = substr($ipPart, 0, 1);
            if ($fiPart == '0') {
                $ipPart = substr($ipPart, 1, 1);
            }
            $ip[] = hexdec($ipPart);
        }
        return implode('0', array_reverse($ip));
    }

    public function toJson()
    {
        return json_encode($this->region, JSON_UNESCAPED_UNICODE);
    }

    public function toXml()
    {
        return (new xml())->convert($this->region);
    }

    public function toArray()
    {
        return $this->region;
    }
}
