<?php
require_once(__DIR__ . '/inc/simple_html_dom.php');
require_once(__DIR__ . '/inc/curlm.php');
require_once(__DIR__ . '/inc/str.php');
require_once(__DIR__ . '/func.php');

$tmp_dir = __DIR__ . '/tmp';
$out_dir = __DIR__ . '/out';
$fname_log = __DIR__ . '/log/test.log';
$fname_json = $out_dir. '/info';
$dirOutput = __DIR__ . '/img';
$dirSaveZip = 'z:/upl2manga';
$url_proxy = '127.0.0.1:9050';
$url_proxy = '';

if (make_dir_if_not_exists($dirSaveZip)==false) {
    echo "Error make dir $dirSaveZip" . PHP_EOL;
    exit;
}

$url = 'https://mangalib.me/manga-list?sort=last_chapter_at&dir=desc&page=1';
$tmp_file = $tmp_dir . '/tmp1.txt';
$ar_url=array();$ar_fname=array();$ar_code=array();
$ar_url[0]=$url;
$ar_fname[0]=$tmp_file;
$is_progress=false;
$post = [
    'dir' => "desc",
    'page' => 1,
    'sort' => "rate",
    'types' => '["1"]'
];

$referer = 'http://yandex.ru/';
curl_multi_load_file($ar_url,$ar_fname,$ar_code,'',$is_progress,$url_proxy);
$text = file_get_contents($tmp_file);

exit;

$tmp_file = $tmp_dir . '/tmp2.txt';
$url = 'https://mangalib.me/kimetsu-no-yaiba/v1/c1?page=1';
while(true) {
    $ar_url=array();$ar_fname=array();$ar_code=array();
    $ar_url[0]=$url;
    $ar_fname[0]=$tmp_file;
    $is_progress=false;
    curl_multi_load_file($ar_url,$ar_fname,$ar_code,'',$is_progress,$url_proxy);
    print_r($ar_code);
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
    if (isset($infoManga['chapter_volume'])) {
        $textTom = 'Том '.$infoManga['chapter_volume'] . ' ';
    }
    if (isset($infoManga['volume'])) {
        $textTom = 'Том '.$infoManga['volume'] . ' ';
    }
    $textGlava = 'Глава 1';
    if (isset($infoManga['number'])) {
        $textGlava = 'Глава ' . $infoManga['number']. ' ';
    }
    if (isset($infoManga['chapter_number'])) {
        $textGlava = 'Глава ' . $infoManga['chapter_number'] . ' ';
    }
    $textName = '';
    if (isset($infoManga['chapter_name'])) {
        $textName = $infoManga['chapter_name'];
    }
    if (isset($infoManga['name'])) {
        $textName = $infoManga['name'];
    }
    
    $infoManga['title'] = $arInfo['title'] . '/' . trim($textTom . $textGlava . $textName);
    $zipName = $dirSaveZip . '/' . $infoManga['slug'] . '-' . $infoManga['chapter_volume'] . '-' . $infoManga['chapter_number'];
    zip_all_files_in_dir($dirOutput,$zipName . '.zip');
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