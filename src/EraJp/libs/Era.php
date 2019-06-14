<?php
namespace r28\EraJp\libs;

use r28\AstroTime\AstroTime;

class Era
{

    /**
     * 元号設定ファイル名 (南朝用)
     * @var string
     */
    static $era_setting_file = 'era.csv';

    /**
     * 元号設定ファイル名 (北朝用)
     * @var string
     */
    static $era_setting_file_north = 'era_north.csv';

    /**
     * 南北朝時代=>北朝の元号を採用
     * @var boolean
     */
    public $is_north = false;

    /**
     * 元号名称
     * @var string
     */
    public $name = null;

    /**
     * 元号名称($nameのエイリアス)
     * @var string
     */
    public $gengou = null;

    /**
     * 元号年
     * @var integer
     */
    public $year;


    /**
     * Constructor
     *  - AstroTimeオブジェクトを指定した場合は元号を求めてセット
     * 
     * @param AstroTime     $new_time   新暦日時AstroTimeオブジェクト
     * @param array         $params     その他パラメータ
     */
    public function __construct(AstroTime $new_time=null, $params=null) {
        if (! is_null($params)) {
            $this->is_north = (isset($params['is_north']) && $params['is_north']) ? true : false;
        }

        if (! is_null($new_time)) {
            $this->setEra($new_time);
        }
    }

    /**
     * 元号をセット
     * 
     * @param AstroTime     $new_time   新暦日時AstroTimeオブジェクト
     * @return Era
     */
    public function setEra(AstroTime $new_time) {
        $era = static::searchForSetting($new_time);
        if (! $era) {
            throw Exception("[Era] No date in setting file");
            return false;
        }

        $this->name = $this->gengou = $era['name'];
        $this->year = (int)$era['year'];
        return $this;
    }


    /**
     * 元号設定ファイルより指定年月の元号を検索
     *  - [元号設定ファイル] カンマ区切りCSVファイル
     *      1) 元号名
     *      2) 開始西暦年
     *      3) 開始ユリウス日(グレゴリオ暦またはユリウス暦)
     *      4) 終了ユリウス日(同上、この日未満)
     * 
     * @param   AstroTime   $time       指定日時(jdを使用)
     * @param   boolean     $is_north   南北朝時代は北朝の元号を返す
     * @return  array
     */
    public static function searchForSetting(AstroTime $time, $is_north=false) {
        $jd = ceil($time->jd);
        $file = ($is_north) ? static::$era_setting_file_north : static::$era_setting_file;
        $file = static::settingFilePath($file);

        $com = "/usr/bin/env awk -F, '$3 <= {$jd} && $4 > {$jd} {print $0}' {$file}";
        exec($com, $outputs, $result);
        if ($result !== 0 || count($outputs) != 1) {
            throw new \Exception("Era setting file search failure: {$com}");
            return false;
        }

        $list = explode(",", $outputs[0]);
        $name = $list[0];
        $year = $time->year - $list[1] + 1;
        return array('name'=>$name, 'year'=>$year);
    }

    /**
     * 設定ファイルPATH
     * 
     * @param   string  $file_name
     * @return  string
     */
    private static function settingFilePath($file_name) {
        return __DIR__."/../settings/{$file_name}";
    }

}