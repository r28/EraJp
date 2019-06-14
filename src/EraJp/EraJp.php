<?php

namespace r28\EraJp;

set_include_path(get_include_path().':'.dirname(__FILE__).'/settings/');
set_include_path(get_include_path().':'.dirname(__FILE__).'/libs/');

use r28\AstroTime\AstroTime;
use r28\EraJp\libs\Era;
use r28\EraJp\libs\Kyuureki;

class EraJp
{
    /**
     * TimezoneName (日本が前提)
     * @constant
     */
    const TIMEZONE_NAME = 'Asia/Tokyo';

    /**
     * 対象日付のAstroTimeオブジェクト
     * @var AstroTime
     */
    public $time;

    public $jd;
    public $jd_gregorian;

    public $era;
    public $kyuureki;

    public function __construct($date=null) {
        date_default_timezone_set(static::TIMEZONE_NAME);
        if(! empty($date)) {
            $this->setDate($date);
            $this->setEra($this->time);
            $this->setKyuureki($this->time);
        }
    }


    /**
     * 対象日付セット
     *
     * @param   AstroTime|string    $date   AstroTimeオブジェクト または 日付文字列
     * @return  EraJp
     */
    public function setDate($date) {
        $dt = (gettype($date) == 'object') ? $date : new AstroTime($date, static::TIMEZONE_NAME);
        $this->time = $dt;
        $this->jd = ceil($dt->jd);
        $this->jd_gregorian = ceil($dt->jd_gregorian);
        return $this;
    }

    public function setEra(AstroTime $time) {
        $this->era = new Era($time);
    }

    public function setKyuureki(AstroTime $time) {
        $this->kyuureki = new Kyuureki($time);
    }
}