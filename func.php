<?php
// Получить ссылки на файлы картинок для скачивания
function get_magnalib_url_images($content,$url) {
    $title = '';
    $strFind1 = '<title>'; // Искомая строка
    $pos1 = strpos($content,$strFind1); // Поиск строки
    if ($pos1 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $strFind2 = '</title>'; // Искомая строка
    $pos2 = strpos($content,$strFind2,$pos1);
    if ($pos2 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $title = substr($content,$pos1 + strlen($strFind1),$pos2 - $pos1 - strlen($strFind1));
    //echo $title . PHP_EOL;
    $title = str_replace('</title','',$title);
    $title = str_replace('Чтение манги','',$title);
    $title = str_replace('[Страница 1]','',$title);
    $pos1 = strpos($title,'глава');
    if ($pos1!==false) {
        $title = substr($title,0,$pos1);
    }
    $title = trim($title);
    $strFind1 = '<div data-media-up="md" class="reader-header-action__title text-truncate">'; // Искомая строка
    $pos1 = strpos($content,$strFind1);
    if ($pos1 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $strFind2 = '</div>'; // Искомая строка
    $pos2 = strpos($content,$strFind2,$pos1);
    if ($pos2 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $titleeng = substr($content,$pos1 + strlen($strFind1),$pos2 - $pos1 - strlen($strFind1));
    echo $titleeng . PHP_EOL;
    add_log("$titleeng");
    $strFind1 = 'window.__pg = '; // Искомая строка
    $pos1 = strpos($content,$strFind1); // Поиск строки
    if ($pos1 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $strFind2 = '}];'; // Искомая строка
    $pos2 = strpos($content,$strFind2,$pos1);
    if ($pos2 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $contentJson = substr($content,$pos1 + strlen($strFind1),$pos2 - $pos1 - strlen($strFind1) + strlen($strFind2) - 1);
    //echo "---" . $contentJson . "---";
    $jsonImages = json_decode($contentJson,true); // Содержит номера страниц и имена файлов
    //print_r($jsonImages);

    $strFind1 = 'window.__info = '; // Искомая строка
    $pos1 = strpos($content,$strFind1); // Поиск строки
    if ($pos1 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $strFind2 = '}};'; // Искомая строка
    $pos2 = strpos($content,$strFind2,$pos1);
    if ($pos2 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $contentJson = substr($content,$pos1 + strlen($strFind1),$pos2 - $pos1 - strlen($strFind1) + strlen($strFind2) - 1);
    //echo "---" . $contentJson . "---";
    $jsonServer = json_decode($contentJson,true); // Содержит данные о сервере где находятся картинки 
    //print_r($jsonServer); //
    //add_log(print_r($jsonServer,true));

    $strFind1 = 'window.__DATA__ = '; // Искомая строка
    $pos1 = strpos($content,$strFind1); // Поиск строки
    if ($pos1 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $strFind2 = '}};'; // Искомая строка
    $pos2 = strpos($content,$strFind2,$pos1);
    if ($pos2 === false) { // Если ничего не нашли тогда выводим ошибку и возврат FALSE
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $contentJson = substr($content,$pos1 + strlen($strFind1),$pos2 - $pos1 - strlen($strFind1) + strlen($strFind2) - 1);
    //echo "---" . $contentJson . "---";
    $jsonData = json_decode($contentJson,true); // Содержит данные о сервере где находятся картинки 
    //add_log(print_r($jsonData,true));
    //print_r($jsonData); //

    $urlImg = $jsonServer['img']['url'];
    $serverImg = $jsonServer['img']['server'];
    if (!isset($jsonServer['servers'][$serverImg])) {
        $error = 'Error in file ' . __FILE__ .' LINE ' . __LINE__ . ' Url: ' . $url;
        echo $error . PHP_EOL;
        add_log($error);
        return false; // возврат FALSE
    }
    $startUrlImg = $jsonServer['servers'][$serverImg] . $urlImg;
    $urlImages = [];
    foreach($jsonImages as $imgInfo) {
        $urlImages[] = [
            'page' => $imgInfo['p'],
            'url' => $startUrlImg . $imgInfo['u']
        ];
    }
    $ret = [
        'title' => $title,
        'titleeng' => $titleeng,
        'urlImages' => $urlImages,
        'data' => $jsonData,
        'server' => $jsonServer
    ];

    return $ret;
}

function get_download_link_hentachan($content,$url,&$title) {
    $html = str_get_html($content);
    if ($html == false) {
        echo "Error parse body\r\n";
        return false;
    }
    $title = '';
    $elm_h1 = $html->find('div#right',0);
    if ($elm_h1!=false) {
        $ar_div = $elm_h1->find('div');
        $find_text = '';
        foreach($ar_div as $elm_div) {
            $find_text = $elm_div->plaintext;
            //echo $find_text . PHP_EOL;
            if (strpos($find_text,'Скачать хентай')!==false) {
                $title = str_replace('Скачать хентай','',$find_text);
                $title = trim($title);
            }
        }
    }
    
    $ret = [];
    $ar_a = $html->find('a');
    if ($ar_a != false) {
        foreach($ar_a as $elm_a) {
            $href = trim($elm_a->href);
            if (empty($href)) {
                continue;
            }
            if (strpos($href,'download.php?')===false) {
                continue;
            }
            if (!in_array($href,$ret)) {
                $ret[] = $href;
            }
        }
    } else {
        add_log("Error find links");
        return false;
    }
    return $ret;    
}

function download_images_to_dir($arUrlImages,$dirOutput,$referrer='') {
    $cntImg = 0; // Счетчик
    $cntMax = count($arUrlImages);
    while($cntImg < $cntMax) {
        $ar_url=array();$ar_fname=array();$ar_code=array();
        for($a=0;$a<=5;$a++) {
            if ($cntImg>=$cntMax) {
                continue;
            }
            $ar_url[]=$arUrlImages[$cntImg]['url'];
            $fileExt = '.png';
            if (strpos($arUrlImages[$cntImg]['url'],'.jpg')) {
                $fileExt = '.jpg';
            }
            $fileNameSave = $dirOutput.'/'.$arUrlImages[$cntImg]['page'].$fileExt;
            echo "Image: $fileNameSave " . PHP_EOL;
            $ar_fname[] = $fileNameSave;
            $cntImg ++;
        }
        $is_progress=false;
        curl_multi_load_file($ar_url,$ar_fname,$ar_code,$referrer,$is_progress);
        //print_r($ar_code);
    }
}

function zip_all_files_in_dir($dirFiles,$zipName) {
    $arFileName = dir_to_array_nr($dirFiles,true);
    if ($arFileName == false) {
        echo "Not files in dir $dirFiles" . PHP_EOL;
        add_log("Not files in dir $dirFiles");
        return false;
    }
    $zip = new ZipArchive;
    if ($zip->open($zipName,ZipArchive::CREATE) !== TRUE) {
        echo 'Error zip' . PHP_EOL;
        add_log("Error create zip $zipName");
        return false;
    }
    foreach($arFileName as $fileName) {
        $zip->addFile($fileName, basename($fileName));
    }
    $zip->close();
    return true;
}

/* С главной страницы получить ссылки на другие страницы манги 
*/
function get_magnalib_url_manga($content,$url) {
    $content = str_replace('media-card','media_card',$content);
    $html = str_get_html($content);
    if ($html == false) {
        echo "Error parse body\r\n";
        return false;
    }
    $ret = [];
    $ar_a = $html->find('a.media_card');
    if ($ar_a != false) {
        foreach($ar_a as $elm_a) {
            $href = trim($elm_a->href);
            if (empty($href)) {
                continue;
            }
            if (!in_array($href,$ret)) {
                $ret[] = $href;
            }
        }
    } else {
        add_log("Error find links");
        return false;
    }
    return $ret;
}

/* Кнопка начать читать
*/
function get_magnalib_url_read($content,$url) {

    $content = str_replace('button button_block button_primary','button_button_block_button_primary',$content);
    $html = str_get_html($content);
    if ($html == false) {
        echo "Error parse body\r\n";
        return false;
    }
    $ret = [];
    $ar_a = $html->find('a.button_button_block_button_primary');
    if ($ar_a != false) {
        foreach($ar_a as $elm_a) {
            $href = trim($elm_a->href);
            if (empty($href)) {
                continue;
            }
            if (strpos($href,'https://mangalib.me/')!==0) {
                continue;
            }
            if (!in_array($href,$ret)) {
                $ret[] = $href;
            }
        }
    } else {
        add_log("Error find links");
        return false;
    }
    return $ret;
}

function get_create_table_main() {
    // Возвращает текст запроса для создания таблицы
    return "CREATE TABLE mangamanager(
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                loaded INTEGER,
                url TEXT
        )";
}

function get_create_table_info() {
    // Возвращает текст запроса для создания таблицы
    return "CREATE TABLE mangainfo(
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                timeupload INTEGER,
                url TEXT,
                title TEXT,
                titleeng TEXT,
                manganame TEXT,
                mangavolume TEXT,
                manganumber TEXT,
                mangaslug TEXT,
                mangaautor TEXT
        )";
}

function get_create_table_index() {
    $querys = [
        "CREATE INDEX IF NOT EXISTS mangamanager_url ON mangamanager (url)",
        "CREATE INDEX IF NOT EXISTS mangainfo_url ON mangainfo (url)",
        "CREATE INDEX IF NOT EXISTS mangainfo_title ON mangainfo (title)",
        "CREATE INDEX IF NOT EXISTS mangainfo_titleeng ON mangainfo (titleeng)",
        "CREATE INDEX IF NOT EXISTS mangainfo_manganame ON mangainfo (manganame)",
        "CREATE INDEX IF NOT EXISTS mangainfo_mangavolume ON mangainfo (mangavolume)",
        "CREATE INDEX IF NOT EXISTS mangainfo_mangaslug ON mangainfo (mangaslug)",
        "CREATE INDEX IF NOT EXISTS mangainfo_mangaautor ON mangainfo (mangaautor)",
        "CREATE INDEX IF NOT EXISTS mangainfo_timeupload ON mangainfo (timeupload)"
    ];
    return $querys;
}

function sqlite_insert_info_to_db($url,$loaded) {
    GLOBAL $fname_sqlite,$sqlite_last_insert_rowid;
    $sqlite_last_insert_rowid = false;
    if(!file_exists($fname_sqlite)) {
        // Создаем все таблицы нужные для работы
        $db = new SQLite3($fname_sqlite);
        $db->query(get_create_table_main()); 
        $db->query(get_create_table_info()); 
        foreach(get_create_table_index() as  $query) {
            $db->query($query); 
        }
    } else {$db = new SQLite3($fname_sqlite);}
    $st=$db->prepare('insert into mangamanager (url,loaded) values (?,?)');
    if ($st == false) {
        $error_message = $db->lastErrorMsg();
        add_log("Error prepare $fname_sqlite $error_message");
        $db->close();
        return false;
    }
    $st->bindParam(1, $url, SQLITE3_TEXT);
    $st->bindParam(2, $loaded, SQLITE3_INTEGER);
    $r = $st->execute();
    if ($r==false) {
        $error_message = $db->lastErrorMsg();
        add_log("Error update sqlite3 $error_message");
        $db->close();
        return false;
    }
    $sqlite_last_insert_rowid = $db->lastInsertRowid();
    $db->close();
    return true;
}

function sqlite_insert_info_to_table_info($timeupload,$url,$title,$titleeng,$manganame,$mangavolume,$manganumber,$mangaslug,$mangaautor) {
    GLOBAL $fname_sqlite,$sqlite_last_insert_rowid;
    $sqlite_last_insert_rowid = false;
    if(!file_exists($fname_sqlite)) {
        // Создаем все таблицы нужные для работы
        $db = new SQLite3($fname_sqlite);
        $db->query(get_create_table_main()); 
        $db->query(get_create_table_info()); 
        foreach(get_create_table_index() as  $query) {
            $db->query($query); 
        }
    } else {$db = new SQLite3($fname_sqlite);}
    /*
    timeupload INTEGER,
    url TEXT,
    title TEXT,
    manganame TEXT,
    mangavolume TEXT,
    manganumber TEXT,
    mangaslug TEXT,
    mangaautor TEXT
    */

    $st=$db->prepare('insert into mangainfo (timeupload,url,title,titleeng,manganame,mangavolume,manganumber,mangaslug,mangaautor) values (?,?,?,?,?,?,?,?,?)');
    if ($st == false) {
        $error_message = $db->lastErrorMsg();
        add_log("Error prepare $fname_sqlite $error_message");
        $db->close();
        return false;
    }
    $st->bindParam(1, $timeupload, SQLITE3_INTEGER);
    $st->bindParam(2, $url, SQLITE3_TEXT);
    $st->bindParam(3, $title, SQLITE3_TEXT);
    $st->bindParam(4, $titleeng, SQLITE3_TEXT);
    $st->bindParam(5, $manganame, SQLITE3_TEXT);
    $st->bindParam(6, $mangavolume, SQLITE3_TEXT);
    $st->bindParam(7, $manganumber, SQLITE3_TEXT);
    $st->bindParam(8, $mangaslug, SQLITE3_TEXT);
    $st->bindParam(9, $mangaautor, SQLITE3_TEXT);
    $r = $st->execute();
    if ($r==false) {
        $error_message = $db->lastErrorMsg();
        add_log("Error update sqlite3 $error_message");
        $db->close();
        return false;
    }
    $sqlite_last_insert_rowid = $db->lastInsertRowid();
    $db->close();
    return true;
}

function sqlite_get_info_by_id($id) {
    GLOBAL $fname_sqlite;
    if(!file_exists($fname_sqlite)) {add_log("Not found file $fname_sqlite");return false;} else {$db = new SQLite3($fname_sqlite);}
    $st=$db->prepare('SELECT * FROM mangamanager WHERE id = ?');
    if ($st==false) {
        add_log("Error prepare $fname_sqlite");
        $db->close();
        return false;
    }
    $st->bindParam(1, $id, SQLITE3_INTEGER);
    $r = $st->execute();
    if ($r==false) {
        add_log($db->lastErrorMsg());
        $db->close();
        return false;
    }
    $ret=array();
    while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
        $ret=$row;
    }
    $db->close();
    return $ret;
}

function sqlite_get_info_by_url($url) {
    GLOBAL $fname_sqlite;
    if(!file_exists($fname_sqlite)) {add_log("Not found file $fname_sqlite");return false;} else {$db = new SQLite3($fname_sqlite);}
    $st=$db->prepare('SELECT * FROM mangamanager WHERE url = ?');
    if ($st==false) {
        add_log("Error prepare $fname_sqlite");
        $db->close();
        return false;
    }
    $st->bindParam(1, $url, SQLITE3_TEXT);
    $r = $st->execute();
    if ($r==false) {
        add_log($db->lastErrorMsg());
        $db->close();
        return false;
    }
    $ret=array();
    while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
        $ret=$row;
    }
    $db->close();
    return $ret;
}

function sqlite_get_max_id() {
    GLOBAL $fname_sqlite;
    if(!file_exists($fname_sqlite)) {add_log("Not found file $fname_sqlite");return false;} else {$db = new SQLite3($fname_sqlite);}
    $st=$db->prepare('SELECT MAX(id) AS maxid FROM mangamanager');
    if ($st==false) return false;
    $r = $st->execute();
    if ($r==false) {add_log($db->lastErrorMsg());$db->close();;return false;}
    $maxid=0;
    while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
        $maxid=$row['maxid'];
    }
    $db->close();
    return $maxid;
}

function sqlite_get_min_id() {
    GLOBAL $fname_sqlite;
    if(!file_exists($fname_sqlite)) {add_log("Not found file $fname_sqlite");return false;} else {$db = new SQLite3($fname_sqlite);}
    $st=$db->prepare('SELECT MIN(id) AS minid FROM mangamanager WHERE loaded = 0');
    if ($st==false) {add_log($db->lastErrorMsg());$db->close();return false;}
    $r = $st->execute();
    if ($r==false) {add_log($db->lastErrorMsg());$db->close();return false;}
    $minid=false;
    while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
        $minid=$row['minid'];
    }
    $db->close();
    return $minid;
}

function sqlite_update_loaded($id,$loaded) {
    GLOBAL $fname_sqlite,$error_message;
    if(!file_exists($fname_sqlite)) {add_log("Not found file $fname_sqlite");return false;} else {$db = new SQLite3($fname_sqlite);}
    $st=$db->prepare('UPDATE mangamanager SET loaded = ? WHERE id = ?');
    $st->bindParam(1, $loaded, SQLITE3_INTEGER);
    $st->bindParam(2, $id, SQLITE3_TEXT);
    if ($st==false) {add_log($db->lastErrorMsg());$db->close();return false;}
    $r = $st->execute();
    if ($r==false) {add_log($db->lastErrorMsg());$db->close();return false;}
    $changes = $db->changes();
    $db->close();
    return $changes;
}
