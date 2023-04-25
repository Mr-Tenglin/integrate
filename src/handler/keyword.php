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
    protected $words;

    public function __construct(string $string = '', $length = 10)
    {
        $this->pscws = new PSCWS4; ("utf8");
        $this->pscws->set_dict(Common::vendor('/scws/pscws4/dict/dict.utf8.xdb'));
        $this->pscws->set_rule(Common::vendor('/scws/pscws4/etc/rules.ini'));
        $this->pscws->set_ignore(true);
        $this->pscws->send_text($string);
        $this->words = $this->pscws->get_tops($length);
        $this->pscws->close();
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
        return json_encode($this->words);
    }

    public function toString($delimiter = ',')
    {
        return implode($delimiter, $this->words);
    }
}
