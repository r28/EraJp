<?php
namespace r28\EraJp\libs;

use r28\AstroTime\AstroTime;

class Kyuureki
{

    const JP_START_GREGORIAN_JD = 2405160;

    /**
     * 新旧暦対照表CSVファイル名
     * @var string
     */
    static $kyuureki_mapping_file = 'kyuureki-map.csv';


    public $year;
    public $month;
    public $day;

    public function __construct(AstroTime $new_time=null) {
        if (! is_null($new_time)) {
            $this->setKyuurekiDate($new_time);
        }
    }

    public function setKyuurekiDate(AstroTime $new_time) {
        $reki_lists = static::searchForSetting($new_time);
        if (! $reki_lists) {
            throw Exception("[Kyuureki] No date in setting file");
            return false;
        }

        $old = $reki_lists->old->date;
        list($year, $month, $day) = explode('-', $old);

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
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