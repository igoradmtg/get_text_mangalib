<?php
require_once(__DIR__ . '/inc/simple_html_dom.php');
require_once(__DIR__ . '/inc/curlm.php');
require_once(__DIR__ . '/inc/str.php');
require_once(__DIR__ . '/func.php');

$tmp_dir = __DIR__ . '/tmp';
$out_dir = __DIR__ . '/out';
$fname_log = __DIR__ . '/log/test.log';
$fname_json = $out_dir. '/info';
$url_proxy = '127.0.0.1:9050';

$tmp_file = $tmp_dir . '/tmp3.txt';
for($mainId=44088;$mainId<48000;$mainId++) {
//$mainId = 44198; // https://hentaichan.live/manga/44198-pregnancy-mesugaki.html
    $url = 'https://y.hentaichan.live/download/'.$mainId.'-shibin-no-shishi-rankou-no-party.html?cacheId=1679945817';
    $ar_url=array();$ar_fname=array();$ar_code=array();
    $ar_url[0]=$url;
    $ar_fname[0]=$tmp_file;
    $is_progress=true;
    curl_multi_load_file($ar_url,$ar_fname,$ar_code,'',false,$url_proxy);
    print_r($ar_code);
    $content = file_get_contents($tmp_file);
    $title = '';
    $links = get_download_link_hentachan($content,$url,$title);
    print_r($links);
    $ar_url=array();$ar_fname=array();$ar_code=array();
    $is_progress=true;
    $cnt = 1;
    foreach($links as $urlFile) {
        $tmpFile = $tmp_dir . '/' . add_zero($mainId,5).'_'.add_zero($cnt,5).'.zip';
        $ar_url[] = $urlFile;
        $ar_fname[] = $tmpFile;
        echo "$urlFile $tmpFile". PHP_EOL;
        $cnt ++;
    }
    curl_multi_load_file($ar_url,$ar_fname,$ar_code,$url);
    if (!empty($title)) {
        file_put_contents($tmp_dir. '/' . add_zero($mainId,5).'.txt',$title);
    }
    echo "ID: $mainId Title: $title" . PHP_EOL;
}