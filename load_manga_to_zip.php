<?php
require_once(__DIR__ . '/inc/simple_html_dom.php');
require_once(__DIR__ . '/inc/curlm.php');
require_once(__DIR__ . '/inc/str.php');
require_once(__DIR__ . '/func.php');

$tmp_dir = __DIR__ . '/tmp'; // Каталог временных файлов
$out_dir = __DIR__ . '/out'; // Каталог файлов для сохранения
$fname_log = __DIR__ . '/log/load_manga_to_zip.log'; // Имя файла с логами
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
$id_load = sqlite_get_min_id();
if ($id_load === false) {
    add_log("Error sqlite_get_min_id");
    exit;
}
$infoLoad = sqlite_get_info_by_id($id_load);
if ($infoLoad==false) {
    add_log("Error load sqlite_get_info_by_id $id_load");
    exit;
}
sqlite_update_loaded($id_load,1);
$tmp_file = $tmp_dir . '/tmp2_'.add_zero($id_load,5).'.txt';
$url = $infoLoad['url'];
while(true) {
    echo "Load url: $url" . PHP_EOL;
    $ar_url=array();$ar_fname=array();$ar_code=array();
    $ar_url[0]=$url;
    $ar_fname[0]=$tmp_file;
    $is_progress=false;
    curl_multi_load_file($ar_url,$ar_fname,$ar_code,'',$is_progress,$url_proxy);
    if (isset($ar_code[0])) {
        //print_r($ar_code);
        echo "Code: {$ar_code[0]}" . PHP_EOL;
        if (strval($ar_code[0]) == '404')  {
            break;
        }
    }
    $text = file_get_contents($tmp_file);
    delete_all_files_in_dir($dirOutput);
    $arInfo = get_magnalib_url_images($text,$url);
    //print_r($arInfo);
    add_log(print_r($arInfo,true));
    if (isset($arInfo['urlImages'])) {
        download_images_to_dir($arInfo['urlImages'],$dirOutput,$url);
    }
    $infoManga = $arInfo['data']['current'];
    $infoManga['slug'] = $arInfo['data']['manga']['slug'];
    $infoManga['slugid'] = $arInfo['data']['manga']['id'];
    $textTom = 'Том 1 ';
    $mangaautor = '';
    $mangavolume = '';
    $manganumber = '';
    if (isset($infoManga['chapter_volume'])) {
        $textTom = 'Том '.$infoManga['chapter_volume'] . ' ';
        $mangavolume = strval($infoManga['chapter_volume']);
    }
    if (isset($infoManga['volume'])) {
        $textTom = 'Том '.$infoManga['volume'] . ' ';
        $mangavolume = strval($infoManga['volume']);
    }
    $textGlava = 'Глава 1';
    if (isset($infoManga['number'])) {
        $textGlava = 'Глава ' . $infoManga['number']. ' ';
        $manganumber = strval($infoManga['number']);
    }
    if (isset($infoManga['chapter_number'])) {
        $textGlava = 'Глава ' . $infoManga['chapter_number'] . ' ';
        $manganumber = strval($infoManga['chapter_number']);
    }
    $textName = '';
    if (isset($infoManga['chapter_name'])) {
        $textName = $infoManga['chapter_name'];
    }
    if (isset($infoManga['name'])) {
        $textName = $infoManga['name'];
    }
    add_log("Title: " . $arInfo['title'] );
    $infoManga['title'] = trim($arInfo['title']) . '/' . trim($arInfo['title']) .' ' . trim($textTom . $textGlava . $textName);
    $infoManga['name'] = $textName;
    $infoManga['ishtitleeng'] = $arInfo['titleeng'];
    $infoManga['ishtitle'] = $arInfo['title'];
    $infoManga['number'] = $manganumber;
    $infoManga['volume'] = $mangavolume;
    $infoManga['autor'] = $mangaautor;

    $zipName = $dirSaveZip . '/' . $infoManga['slug'] . '-' . $infoManga['chapter_volume'] . '-' . $infoManga['chapter_number'];
    $res_zip = zip_all_files_in_dir($dirOutput,$zipName . '.zip');
    if ($res_zip == false) {
        add_log("Error zip files $url");
        continue;
    }
    $timeupload = time();
    $title = $arInfo['title']; 
    $titleeng = $arInfo['titleeng']; 
    $manganame = $textName; 
    $mangaslug = $infoManga['slug'];
    sqlite_insert_info_to_table_info($timeupload,$url,$title,$titleeng,$manganame,$mangavolume,$manganumber,$mangaslug,$mangaautor);
    $infoManga['manga_id'] = $sqlite_last_insert_rowid;

    file_put_contents($zipName . '.json',json_encode($infoManga));
    $url = '';
    if (isset($arInfo['server']['next']['url'])) {
        $url = $arInfo['server']['next']['url'];
    }
    if (empty($url)) {
        break;
    }
}

//$fname = $tmp_dir . '/test1.html';
//file_put_contents($fname,$text);
//$url = 'https://y.hentaichan.live/download/42200-nesostoyavshayasya-mamaneudacha-materi.html?cacheId=1654620746';