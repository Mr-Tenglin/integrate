<?php
/**
 * GitHub Project: Convert an Chinese to Pinyin
 * Copy Project Code: https://github.com/overtrue/pinyin
 */

declare (strict_types = 1);

namespace tenglin\integrate\handler;

use Exception;
use Overtrue\Pinyin\Pinyin as PinyinHandler;

class Pinyin
{
    protected $pinyin;
    /**
     * 载入模式
     *
     * @var string 小内存型: 将字典分片载入内存 (默认空)
     * @var string memory = 内存型: 将所有字典预先载入内存
     * @var string io = I/O型: 不载入内存，将字典使用文件流打开逐行遍历并运用php5.5生成器(yield)特性分配单行内存
     */
    protected $model;
    protected $word;
    /**
     * 载入选项
     *
     * @var int PINYIN_TONE = UNICODE 式音调：měi hǎo
     * @var int PINYIN_ASCII_TONE = 带数字式音调：mei3 hao3
     * @var int PINYIN_NO_TONE = 无音调：mei hao
     * @var int PINYIN_KEEP_NUMBER = 保留数字
     * @var int PINYIN_KEEP_ENGLISH = 保留英文
     * @var int PINYIN_KEEP_PUNCTUATION = 保留标点
     * @var int PINYIN_UMLAUT_V = 使用 v 代替 yu, 例如：吕 lyu 将会转为 lv
     */
    protected $option;
    protected $delimiter = '-';
    protected $use = 'convert';

    public function __construct($args = [])
    {
        if (!empty($args[0])) {
            $this->word = $args[0];
        }
    }

    public function convert($word = '', $option = PINYIN_DEFAULT, $type = 'array', $use = 'convert')
    {
        if (!empty($word)) {
            $this->word = $word;
        }
        $this->option = $option;
        $this->use = $use;
        if ($type == 'json') {
            return $this->toJson();
        } elseif ($type == 'xml') {
            return $this->toXml();
        } elseif ($type == 'string') {
            return $this->toString();
        } else {
            return $this->toArray();
        }
    }

    public function model(string $model = '')
    {
        $this->model = $model;
        return $this;
    }

    public function word(string $string = '')
    {
        $this->word = $string;
        return $this;
    }

    public function option($option = PINYIN_DEFAULT)
    {
        $this->option = $option;
        return $this;
    }

    public function delimiter($delimiter = '-')
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    public function symbol()
    {
        $this->use = 'symbol';
        return $this;
    }

    public function link()
    {
        $this->use = 'link';
        return $this;
    }

    public function abbr()
    {
        $this->use = 'abbr';
        return $this;
    }

    public function name()
    {
        $this->use = 'name';
        return $this;
    }

    public function toArray()
    {
        $this->object_load();
        return $this->pinyin;
    }

    public function toJson()
    {
        $this->object_load();
        return json_encode($this->pinyin, JSON_UNESCAPED_UNICODE);
    }

    public function toXml()
    {
        $this->object_load();
        return (new xml())->convert(['pinyin' => $this->pinyin]);
    }

    public function toString()
    {
        $this->object_load();
        return implode($this->delimiter, $this->pinyin);
    }

    protected function object_load()
    {
        if (empty($this->word)) {
            throw new Exception('Word cannot be empty');
        }
        if ($this->model == 'memory') {
            $obj = new PinyinHandler('\\Overtrue\\Pinyin\\MemoryFileDictLoader');
        } elseif ($this->model == 'io') {
            $obj = new PinyinHandler('\\Overtrue\\Pinyin\\GeneratorFileDictLoader');
        } else {
            $obj = new PinyinHandler();
        }
        if ($this->use == 'symbol') {
            $pinyin = $obj->sentence($this->word, $this->option);
            $this->pinyin = explode(' ', $pinyin);
        } elseif ($this->use == 'link') {
            $pinyin = $obj->permalink($this->word, $this->delimiter);
            $this->pinyin = explode($this->delimiter, $pinyin);
        } elseif ($this->use == 'abbr') {
            $delimiter = $this->delimiter;
            if ($this->option != PINYIN_DEFAULT) {
                $delimiter = $this->option;
            }
            $pinyin = $obj->abbr($this->word, $this->delimiter);
            if ($delimiter != '') {
                $this->pinyin = explode($this->delimiter, $pinyin);
            } else {
                $this->pinyin = str_split($pinyin);
            }
        } elseif ($this->use == 'name') {
            $this->pinyin = $obj->name($this->word, $this->option);
        } else {
            $this->pinyin = $obj->convert($this->word, $this->option);
        }
        return $this;
    }
}
