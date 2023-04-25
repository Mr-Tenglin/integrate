<?php
/**
 * GitHub Project: Convert an Chinese to Pinyin
 * Copy Project Code: https://github.com/overtrue/pinyin
 */

declare (strict_types = 1);

namespace tenglin\integrate\handler;

use Overtrue\Pinyin\Pinyin as PyModel;

class Pinyin
{
    protected $pinyin;
    protected $word;
    protected $option;
    protected $delimiter = '';
    protected $symbol = false;
    protected $data = [];

    /**
     * @param string $model symbol 声调符号，例如 pīn yīn（默认） | none 不输出拼音，例如 pin yin | number 末尾数字模式的拼音，例如 pin1 yin1
     */
    public function __construct(string $model = '', $option = \PINYIN_DEFAULT)
    {
        if ($model == 'memory') {
            $this->pinyin = new PyModel('\\Overtrue\\Pinyin\\MemoryFileDictLoader');
        } elseif ($model == 'io') {
            $this->pinyin = new PyModel('\\Overtrue\\Pinyin\\GeneratorFileDictLoader');
        } else {
            $this->pinyin = new PyModel();
        }
        $this->option = $option;
    }

    public function word(string $string = '')
    {
        $this->word = $string;
    }

    /**
     * Undocumented function
     *
     * @param string $option PINYIN_TONE = UNICODE 式音调：měi hǎo | PINYIN_ASCII_TONE = 带数字式音调：mei3 hao3 | PINYIN_NO_TONE = 无音调：mei hao | PINYIN_KEEP_NUMBER = 保留数字 | PINYIN_KEEP_ENGLISH = 保留英文 | PINYIN_KEEP_PUNCTUATION = 保留标点 | PINYIN_UMLAUT_V = 使用 v 代替 yu, 例如：吕 lyu 将会转为 lv
     * @return void
     */
    public function option($option = \PINYIN_DEFAULT)
    {
        $this->option = $option;
    }

    public function delimiter($delimiter = '')
    {
        $this->delimiter = $delimiter;
    }

    public function symbol($symbol = true)
    {
        $this->symbol = $symbol;
    }

    public function sentence()
    {
        if (!$this->symbol) {
            return $this->data = $this->pinyin->convert($this->word, $this->option);
        } else {
            return $this->data = $this->pinyin->sentence($this->word, $this->option);
        }
    }

    public function link()
    {
        $this->data = $this->pinyin->permalink($this->word, $this->delimiter);
        return $this->data = explode($this->delimiter, $this->data);
    }

    public function abbr()
    {
        $delimiter = $this->delimiter;
        if ($this->option != \PINYIN_DEFAULT) {
            $delimiter = $this->option;
        }
        $this->data = $this->pinyin->abbr($this->word, $this->delimiter);
        if ($this->delimiter != '') {
            return $this->data = explode($this->delimiter, $this->data);
        } else {
            return $this->data = str_split($this->data);
        }
    }

    public function name()
    {
        return $this->data = $this->pinyin->name($this->word, $this->option);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function toJson()
    {
        return json_encode($this->data);
    }

    public function toString()
    {
        return implode($this->delimiter, $this->data);
    }
}
