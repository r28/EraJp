<?php
/**
 * [旧暦と先発グレゴリオ暦の対応の一覧表] ダウンロード => CSV化
 * https://raw.githubusercontent.com/manakai/data-locale/master/data/calendar/kyuureki-map.txt
 * 
 * ※参考: https://wiki.suikawiki.org/n/%E6%97%A7%E6%9A%A6#header-section-%E9%95%B7%E6%9A%A6%E3%80%81%E5%AF%BE%E7%85%A7%E8%A1%A8%E3%81%A8%E5%A4%89%E6%8F%9B%E3%83%84%E3%83%BC%E3%83%AB%E2%80%A8%E5%85%A8%E6%97%A5%E4%BB%98%E5%AF%BE%E7%85%A7%E8%A1%A8
 *         https://github.com/sugi/wareki 
 */
define('BASE_DIR', dirname(__FILE__).'/');
define('OUTPUT_DIR', BASE_DIR.'../settings/');
define('OUTPUT_FILE', OUTPUT_DIR.'kyuureki-map.csv');
define('MAP_FILE_SAVE', BASE_DIR.'kyuureki-map.txt');
define('MAP_FILE_URL', 'https://raw.githubusercontent.com/manakai/data-locale/master/data/calendar/kyuureki-map.txt');

require_once BASE_DIR . '../../../vendor/autoload.php';

use r28\AstroTime\AstroTime;

$longopts = [
    "not-download"
];
$opts = getopt("", $longopts);

$not_download = (array_key_exists('not-download', $opts)) ? true : false;

if (! $not_download) {
    echo "[DOWNLOAD_MAP_FILE]".PHP_EOL;
    if (! downloadMapFile()) {
        echo "FAILURE!".PHP_EOL;
        exit(1);
    }
}

echo "[EXCHANGE_DATE]".PHP_EOL;
if (! exchangeDate()) {
    echo "FAILURE!".PHP_EOL;
    exit(1);
}
exit;


exit(0);

function downloadMapFile() {
    // ダウンロード
    $fp = @fopen(MAP_FILE_URL, 'r');
    $fpw = @fopen(MAP_FILE_SAVE, 'w');
    if (! $fp) {
        echo "[DOWNLOAD_MAP] 'Map file' url open failure: ".MAP_FILE_URL.PHP_EOL;
        return false;
    }
    if (! $fpw) {
        echo "[DOWNLOAD_MAP] 'Map file' save file open failure: ".MAP_FILE_SAVE.PHP_EOL;
        return false;
    }

    $size = 0;
    while (!feof($fp)) {
        $buff = fread($fp, 1024);
        if ($buff === false) {
            $size = false;
            break;
        }
 
        $wsize = fwrite($fpw, $buff);
        if ($wsize === false) {
            $size = false;
            break;
        }
 
        $size += $wsize;
    }
 
    fclose($fp);
    fclose($fpw);
    return $size;
}

function exchangeDate() {
    $fpw = @fopen(OUTPUT_FILE, 'w');
    if (! $fpw) {
        echo "[OUTPUT_FILE] open failure!".PHP_EOL;
        return false;
    }

    if (($fp = @fopen(MAP_FILE_SAVE, 'r')) !== false) {
        while (($line = fgetcsv($fp, 2048, "\t")) !== false) {
            $new_date = $line[0];

            // 新暦が 0000年 以降のみ
            if (substr($new_date, 0, 1) == "-") continue;

            // 閏月の"'"を削除
            $old = $line[1];
            $old_date = preg_replace("/(\d+)-(\d+)'-(\d+)/", "$1-$2-$3", $old);

            $jd_new = ceil(jdForGregorian($new_date));
            $jd_old = ceil(jdForGregorian($old_date));
            echo $jd_new." : ".$jd_old.PHP_EOL;
            $str = "{$jd_new},{$new_date},{$jd_old},{$old}".PHP_EOL;
            $res = fwrite($fpw, $str);
            if (! $res) {
                fclose($fpw);
                fclose($fp);
                return false;
            }
        }
    } else {
        echo "[OUTPUT_FILE] 'Map file' read failure: ".MAP_FILE_SAVE.PHP_EOL;
        return false;
    }

    fclose($fpw);
    fclose($fp);
}

function jdForGregorian($dateString) {
    $t = new AstroTime($dateString, 'Asia/Tokyo', true);
    $jd = $t->jd_gregorian;
    return $jd;
}
