<?php
namespace r28\EraJp\libs;

use r28\AstroTime\AstroTime;

class Kyuureki
{

    /**
     * 日本で新暦(グレゴリオ暦)を使用するようになった日のユリウス日
     *  => 1873年1月1日 (旧 明治5年12月3日)
     * @constant integer
     */
    const JP_START_GREGORIAN_JD = 2405160;

    /**
     * 新旧暦対照表CSVファイル名
     * @var string
     */
    static $kyuureki_mapping_file = 'kyuureki-map.csv';

    /**
     * 旧暦 年
     * @var integer
     */
    public $year;

    /**
     * 旧暦 月
     * @var integer
     */
    public $month;

    /**
     * 旧暦 日
     * @var integer
     */
    public $day;

    /**
     * 指定の新暦日時は日本では旧暦を使用していたか
     * @var boolean
     */
    public $use_kyuureki;

    /**
     * Constructor
     *  - AstroTimeオブジェクトを指定した場合は旧暦を求め旧暦使用フラグをセット
     * 
     * @param AstroTime     $new_time   旧暦を求めたい新暦AstroTimeオブジェクト
     */
    public function __construct(AstroTime $new_time=null) {
        if (! is_null($new_time)) {
            $this->setKyuurekiDate($new_time);
            $this->setKyuurekiFlag($new_time);
        }
    }

    /**
     * 旧暦年月日をセット
     * 
     * @param AstroTime     $new_time   新暦日時AstroTimeオブジェクト
     * @return Kyuureki
     */
    public function setKyuurekiDate(AstroTime $new_time) {
        $reki_lists = static::searchForSetting($new_time);
        if (! $reki_lists) {
            throw Exception("[Kyuureki] No date in setting file");
            return false;
        }

        $old = $reki_lists->old->date;
        list($year, $month, $day) = explode('-', $old);

        $this->year  = (int)$year;
        $this->month = (int)$month;
        $this->day   = (int)$day;

        return $this;
    }

    /**
     * 旧暦使用フラグをセット
     * 
     * @param AstroTime     $new_time   新暦日時AstroTimeオブジェクト
     * @return Kyuureki
     */
    public function setKyuurekiFlag(AstroTime $new_time) {
        $this->use_kyuureki = static::useKyuureki($new_time);
        return $this;
    }

    /**
     * 旧暦を使用していた時代か?
     * 
     * @param AstroTime     $new_time   新暦日時AstroTimeオブジェクト
     * @return boolean
     */
    public static function useKyuureki(AstroTime $new_time) {
        $jd = $new_time->jd;
        return ($jd < static::JP_START_GREGORIAN_JD) ? true : false;
    }

    /**
     * 新旧暦対照表CSVファイルから新暦のユリウス日に対応するレコードを検索
     * 
     * @param   AstroTime   $new_time   新暦から生成したAstroTimeオブジェクト
     * @return  stdObj
     */
    public static function searchForSetting(AstroTime $new_time) {
        $jd = ceil($new_time->jd);

        $file = static::mappingFilePath();
        $com = "/usr/bin/env awk -F, '$1 == {$jd} {print $0}' {$file}";
        exec($com, $outputs, $result);
        if ($result !== 0 || count($outputs) != 1) {
            return false;
        }

        $list = static::explodeCsvRow($outputs[0]);
        return $list;
    }

    /**
     * 新旧暦対照表CSVファイルの当該行をパース
     * 
     * @param string    $row
     * @return stdObj
     */
    private static function explodeCsvRow($row) {
        $list = explode(",", $row);
        $new_jd = $list[0];
        $new_date = $list[1];
        $old_jd = $list[2];
        $old_date = $list[3];
        return (object) [
            'new' => (object) [ 'jd' => $new_jd, 'date' => $new_date ],
            'old' => (object) [ 'jd' => $old_jd, 'date' => $old_date ],
        ];
    }

    /**
     * 新旧暦対照表CSVファイルPATH
     * 
     * @param   string  $file_name
     * @return  string
     */
    private static function mappingFilePath() {
        return __DIR__."/../settings/".static::$kyuureki_mapping_file;
    }
}