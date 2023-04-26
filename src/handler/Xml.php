<?php
/**
 * GitHub Project: PHP7 library for encode/decode xml
 * Copy Project Code: https://github.com/darkfriend/php7-xml
 */

declare (strict_types = 1);

namespace tenglin\integrate\handler;

use darkfriend\helpers\Xml as XmlHandler;
use Exception;

class Xml
{
    protected $data;
    protected $option;

    public function __construct($args = [])
    {
        $this->data = (empty($args[0]) ? '' : $args[0]);
        $this->option = (empty($args[1]) ? [] : $args[1]);
    }

    public function convert($data, $option = [])
    {
        $converter = new self([$data, $option]);
        if (is_array($data)) {
            return $converter->toXml();
        } else {
            return $converter->toArray();
        }
    }

    public function toXml()
    {
        $data = '';
        try {
            $data = XmlHandler::encode($this->data, $this->option);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        return $data;
    }

    public function toArray()
    {
        $data = [];
        try {
            $data = XmlHandler::decode($this->data, $this->option);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        return $data;
    }
}
