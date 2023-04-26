<?php
/**
 * GitHub Project: Chinese participle
 * Copy Project Code: https://github.com/hightman/scws
 */

declare (strict_types = 1);

namespace tenglin\integrate\handler;

use SCWS\PSCWS4;
use tenglin\integrate\Common;

class Keyword
{
    protected $pscws;
    protected $words = [];

    public function __construct($args)
    {
        $string = (!empty($args[0]) ? $args[0] : '');
        $length = (!empty($args[1]) ? (int) $args[1] : 10);
        $PSCWS4_Content = Common::file_open(Common::vendor('/scws/pscws4/src/PSCWS4.php'));
        if (strpos($PSCWS4_Content, '= create_function') !== false) {
            $PSCWS4_Content = str_replace('= create_function', '= @create_function', $PSCWS4_Content);
            Common::file_save(Common::vendor('/scws/pscws4/src/PSCWS4.php'), $PSCWS4_Content);
        }
        $this->pscws = new PSCWS4; ("utf8");
        $this->pscws->set_dict(Common::vendor('/scws/pscws4/dict/dict.utf8.xdb'));
        $this->pscws->set_rule(Common::vendor('/scws/pscws4/etc/rules.ini'));
        $this->pscws->set_ignore(true);
        $this->pscws->send_text($string);
        foreach ($this->pscws->get_tops($length) as $v) {
            $this->words[] = $v['word'];
        }
        $this->pscws->close();
        return $this;
    }

    public function convert(string $string = '', $length = 10, $type = 'array')
    {
        $converter = new self($string, $length);
        if ($type == 'json') {
            return $converter->toJson();
        } elseif ($type == 'string') {
            return $converter->toString();
        } else {
            return $converter->toArray();
        }
    }

    public function toArray()
    {
        return $this->words;
    }

    public function toJson()
    {
        return json_encode($this->words, JSON_UNESCAPED_UNICODE);
    }

    public function toXml()
    {
        return (new xml())->convert(['word' => $this->words], ['root' => '<words/>']);
    }

    public function toString($delimiter = ',')
    {
        return implode($delimiter, $this->words);
    }
}
