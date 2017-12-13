<?php
namespace VictEntry;

class EntryMeta {

    const RAW = 0;
    const FORMATTED = 1;

    const ADDRESS = 100;
    const PERCENT = 101;

    protected static $address_maxlen;
    protected static $percent_maxlen;

    function __construct() {
        if (!isset(self::$address_maxlen)) {
            self::$address_maxlen = 0;
        }
        if (!isset(self::$percent_maxlen)) {
            self::$percent_maxlen = 0;
        }
    }

    public static function getProperty($property) {
        switch ($property) {
            case EntryMeta::ADDRESS:
                return self::$address_maxlen;

            case EntryMeta::PERCENT:
                return self::$percent_maxlen;

            default:
                return 0;
        }
    }

    public static function setProperty($input, $property) {
        switch ($property) {
            case EntryMeta::ADDRESS:
                self::$address_maxlen = self::getMaxLength($input, self::$address_maxlen);
                break;

            case EntryMeta::PERCENT:
                self::$percent_maxlen = self::getMaxLength($input, self::$percent_maxlen);
                break;
        }
    }

    protected static function getFormatted($input, $property, $format) {
        switch ($format) {
            case EntryMeta::FORMATTED:
                return self::mb_str_pad($input, self::getProperty($property));

            case EntryMeta::RAW:
            default:
                return $input;
        }
    }

    private static function getMaxLength($str, $maxlen) {
        $ln = iconv_strlen($str);
        return $ln > $maxlen
            ? $ln
            : $maxlen;
    }

    private static function mb_str_pad($input, $pad_length, $pad_string=' ', $pad_style=STR_PAD_RIGHT, $encoding="UTF-8") {
        return str_pad($input,strlen($input)-iconv_strlen($input,$encoding)+$pad_length, $pad_string, $pad_style);
    }
}

class Entry extends EntryMeta {

    private $date = '';
    private $kbk = '';
    private $address = '';
    private $percent_sum = 0;
    private $percent_cnt = 0;
    private $percent = '';

    function __construct($date, $kbk, $address, $percent) {
        parent::__construct();
        $this->date = $date;
        $this->kbk  = $kbk;
        $this->address = trim($address);
        $this->percent_sum = intval($percent);
        $this->percent_cnt = 1;
        $this->calculatePercent();
        parent::setProperty($this->address, EntryMeta::ADDRESS);
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function addPercent($percent_sum, $percent_cnt=1) {
        $this->percent_sum += $percent_sum;
        $this->percent_cnt += $percent_cnt;
        $this->calculatePercent();
    }

    public function getLine() {
        return "| {$this->date} | {$this->kbk} | {$this->getAddress()} | {$this->getPercent()} |";
    }

    public function getAddress() {
        return $this->getFormatted($this->address, EntryMeta::ADDRESS, EntryMeta::FORMATTED);
    }

    public function getPercent() {
        return $this->getFormatted($this->percent, EntryMeta::PERCENT, EntryMeta::FORMATTED);
    }

    protected function calculatePercent() {
        $val = floor($this->percent_sum / $this->percent_cnt);
        $this->percent = '' . $val . '%';
        if ($this->percent_cnt > 1) {
            $this->percent .= ' (' . $this->percent_cnt . ')';
        }
        parent::setProperty($this->percent, EntryMeta::PERCENT);
    }
}