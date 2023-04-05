<?php
require_once(__DIR__ . '/inc/simple_html_dom.php');
require_once(__DIR__ . '/inc/curlm.php');
require_once(__DIR__ . '/inc/str.php');
require_once(__DIR__ . '/func.php');

$tmp_dir = __DIR__ . '/tmp'; // Каталог временных файлов
$out_dir = __DIR__ . '/out'; // Каталог файлов для сохранения
$fname_log = __DIR__ . '/log/load_main_page.log'; // Имя файла с логами
$fname_json = $out_dir. '/info'; // Каталог
$dirOutput = __DIR__ . '/img'; // Каталог для изображений
$fname_sqlite = __DIR__ . '/manga.db'; // Имя файла базы данных SQLite
$dirSaveZip = 'z:/upl2manga'; // Каталог для сохранения файлов ZIP
$url_proxy = '127.0.0.1:9050';
$url_proxy = '';

if (make_dir_if_not_exists($dirSaveZip)==false) {
    echo "Error make dir $dirSaveZip" . PHP_EOL;
    exit;
}

for($page=1;$page<=30;$page++) {
    $url = 'https://mangalib.me/manga-list?sort=last_chapter_at&dir=desc&page=' . $page;
    echo "Load url: $url" . PHP_EOL;
    $tmp_file = $tmp_dir . '/tmp_'.add_zero($page,5).'.txt';
    $ar_url = []; $ar_fname = []; $ar_code = [];
    $ar_url[0] = $url;
    $ar_fname[0] = $tmp_file;
    $is_progress = false;
    $referer = 'http://yandex.ru/';
    curl_multi_load_file($ar_url,$ar_fname,$ar_code,'',$is_progress,$url_proxy);
    $text = file_get_contents($tmp_file);
    $arUrls = get_magnalib_url_manga($text,$url);
    print_r($arUrls);
    if ($arUrls == false) {
        continue;
    }
    foreach($arUrls as $url) {
        $tmp_file = $tmp_dir . '/tmp_page.txt';
        $ar_url = []; $ar_fname = []; $ar_code = [];
        $ar_url[0] = $url;
        $ar_fname[0] = $tmp_file;
        $is_progress = false;
        $referer = 'http://yandex.ru/';
        curl_multi_load_file($ar_url,$ar_fname,$ar_code,'',$is_progress,$url_proxy);
        $text = file_get_contents($tmp_file);
        $arUrlsRead = get_magnalib_url_read($text,$url);
        echo "Read urls:" . PHP_EOL;
        //print_r($arUrlsRead);
        if ($arUrlsRead != false) {
            foreach ($arUrlsRead as $urlRead) {
                $findInfo = sqlite_get_info_by_url($urlRead);
                if ($findInfo == false) {
                    if (sqlite_insert_info_to_db($urlRead,0)) {
                        echo "ID: $sqlite_last_insert_rowid url: $urlRead" . PHP_EOL;
                    } else {
                        add_log("Error inserd info to db $urlRead");
                    }

                } else {
                    echo "Find url: $urlRead" . PHP_EOL;
                }
            }


        } else {
            add_log("Not found urls read: $url");
        }
        echo "Sleep 2" . PHP_EOL;
        sleep(2);
    }
}
