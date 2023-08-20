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
        if (PHP_VERSION_ID > 80000) {
            $PSCWS4_Content = file(Common::vendor('/scws/pscws4/src/PSCWS4.php'));
            if (strpos(implode('', $PSCWS4_Content), '= create_function') !== false) {
                $PSCWS4_Content[314] = '        $cmp_func = function($a, $b) { return ($b[\'weight\'] > $a[\'weight\'] ? 1 : -1); };' . PHP_EOL;
                Common::file_save(Common::vendor('/scws/pscws4/src/PSCWS4.php'), implode('', $PSCWS4_Content));
            }
            $XDB_R_Content = file(Common::vendor('/scws/pscws4/src/XDB_R.php'));
            if (strpos(implode('', $XDB_R_Content), 'var $version;') === false) {
                $XDB_R_Content[36] = '    var $hash_prime = 0;' . PHP_EOL . '    var $version;' . PHP_EOL . '    var $fsize;' . PHP_EOL . '    var $_io_times;' . PHP_EOL;
                Common::file_save(Common::vendor('/scws/pscws4/src/XDB_R.php'), implode('', $XDB_R_Content));
            }
        }

        $this->pscws = new PSCWS4('utf8');
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
