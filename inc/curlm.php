<?php 
//*****************************************************
// Функции для работы с множеством потоков curl
//*****************************************************
function curl_multiple_req($url_array,&$text_body_arr,&$code_arr,$referer='',$user_agent='') { 
    $mh = curl_multi_init(); 
    $curl_array = array(); 
    $headers   = array();
    if (empty($user_agent)) {
        $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2) Gecko/20100115 MRA 5.6 (build 03278) Firefox/3.6';
    }
    //$headers[] = 'Content-type: application/x-www-form-urlencoded';
    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
    $headers[] = 'Accept-Language: ru,en-us;q=0.7,en;q=0.3';
    $headers[] = 'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7';
    if ($referer!='') {$headers[] = 'Referer: '.$referer;}
    foreach($url_array as $i => $url) { 
        $curl_array[$i] = curl_init($url); 
        curl_setopt($curl_array[$i],CURLOPT_URL,$url);
        curl_setopt($curl_array[$i],CURLOPT_TIMEOUT ,600);
        //Замечание: Если вам нужно, чтобы эта функция вернула результат, а не вывела его в браузер, 
        //используйте опцию CURLOPT_RETURNTRANSFER с функцией curl_setopt(). 
        curl_setopt($curl_array[$i],CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_array[$i],CURLOPT_HEADER, 0);
        curl_setopt($curl_array[$i],CURLOPT_USERAGENT, $user_agent);
        curl_setopt($curl_array[$i],CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($curl_array[$i],CURLOPT_HTTPHEADER,$headers);  
        curl_multi_add_handle($mh, $curl_array[$i]); 
    } 
    $running = NULL; 
    do { 
        usleep(10); 
        curl_multi_exec($mh,$running); 
    } while($running > 0); 
    $res=true;
    $text_body_arr = array(); 
    $code_arr = array();
    foreach($url_array as $i => $url) { 
        $text_body_arr[$i] = curl_multi_getcontent($curl_array[$i]); 
        $code_arr[$i] = curl_getinfo($curl_array[$i],CURLINFO_HTTP_CODE);
        if ($code_arr!='200') {$res=false;}
    } 

    foreach($url_array as $i => $url){ 
        curl_multi_remove_handle($mh, $curl_array[$i]); 
    } 
    curl_multi_close($mh);        
    return $res; 
} 

function curl_multi_load_file($ar_url,$ar_fname,&$code_arr,$referer='',$is_progress=false,$proxy = '',$time_out = 300,$user_agent='') {
    if (empty($user_agent)) {
        $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2) Gecko/20100115 MRA 5.6 (build 03278) Firefox/3.6';
    }
    $headers = [];
    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
    $headers[] = 'Accept-Language: ru,en-us;q=0.7,en;q=0.3';
    $headers[] = 'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7';
    if ($referer!='') {$headers[] = 'Referer: '.$referer;}
    $conn = array();
    $fp = array();
    $mh = curl_multi_init();
    foreach ($ar_url as $i => $url) {
        $fname = $ar_fname[$i];
        $conn[$i]=curl_init($url);
        $fp[$i]=fopen ($fname, "wb");
        if ($fp[$i]==false) {echo "Error create file $fname <br>\r\n";return false;}
        curl_setopt ($conn[$i], CURLOPT_SSL_VERIFYPEER, false);    // No certificate
        curl_setopt ($conn[$i], CURLOPT_FOLLOWLOCATION, true);
        curl_setopt ($conn[$i], CURLOPT_FILE, $fp[$i]);
        curl_setopt ($conn[$i], CURLOPT_HEADER ,0);
        curl_setopt ($conn[$i], CURLOPT_CONNECTTIMEOUT,60);
        curl_setopt ($conn[$i], CURLOPT_TIMEOUT, $time_out);
        curl_setopt ($conn[$i], CURLOPT_MAXCONNECTS, 10);
        curl_setopt ($conn[$i], CURLOPT_USERAGENT, $user_agent);
        curl_setopt ($conn[$i], CURLOPT_HTTPHEADER, $headers);  
        if ($is_progress) {
            curl_setopt ($conn[$i], CURLOPT_PROGRESSFUNCTION, function ($resource,$download_size, $downloaded, $upload_size, $uploaded)
            {
                if($download_size > 0)
                $progress = round($downloaded / $download_size  * 100);
                echo "Load: $progress % $resource " . PHP_EOL;
                flush();
            });
            curl_setopt($conn[$i], CURLOPT_NOPROGRESS, false);
        }
        if (!empty($proxy)) {
            curl_setopt($conn[$i] ,CURLOPT_PROXY, $proxy);
            curl_setopt($conn[$i] ,CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
        }
        curl_multi_add_handle ($mh,$conn[$i]);
    }
    $active = null;
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        // ждём какую-нибудь активность от потоков
        if (curl_multi_select($mh) == -1) {
            usleep(100);
        }

        // опрашиваем curl_multi_exec на предмет, есть ли ещё активные потоки
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }  

    foreach ($ar_url as $i => $url) {
        $code_arr[$i] = curl_getinfo($conn[$i],CURLINFO_HTTP_CODE);
        curl_multi_remove_handle($mh,$conn[$i]);
        curl_close($conn[$i]);
        fclose($fp[$i]);
    }
    curl_multi_close($mh);
    return true;
}

function curl_multi_load_file_post($ar_url,$ar_fname,&$code_arr,$post=array(),$is_progress=false,$proxy = '',$time_out = 300,$user_agent='',$referer = '') {
  if (empty($user_agent)) {
      $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2) Gecko/20100115 MRA 5.6 (build 03278) Firefox/3.6';
  }
  $conn = array();
  $fp = array();
  $mh = curl_multi_init();
  $headers = [];
  $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
  $headers[] = 'Accept-Language: ru,en-us;q=0.7,en;q=0.3';
  $headers[] = 'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7';
  if ($referer!='') {$headers[] = 'Referer: '.$referer;}

  foreach ($ar_url as $i => $url) {
      $fname = $ar_fname[$i];
      $conn[$i]=curl_init($url);
      $fp[$i]=fopen ($fname, "wb");
      if ($fp[$i]==false) {echo "Error create file $fname <br>\r\n";return false;}
      curl_setopt ($conn[$i], CURLOPT_SSL_VERIFYPEER, false);    // No certificate
      curl_setopt ($conn[$i], CURLOPT_FOLLOWLOCATION, true);
      curl_setopt ($conn[$i], CURLOPT_FILE, $fp[$i]);
      curl_setopt ($conn[$i], CURLOPT_HEADER ,0);
      curl_setopt ($conn[$i], CURLOPT_CONNECTTIMEOUT,60);
      curl_setopt ($conn[$i], CURLOPT_TIMEOUT, $time_out);
      curl_setopt ($conn[$i], CURLOPT_MAXCONNECTS, 10);
      curl_setopt ($conn[$i], CURLOPT_USERAGENT, $user_agent);
      curl_setopt ($conn[$i], CURLOPT_POST, true);
      curl_setopt ($conn[$i], CURLOPT_POSTFIELDS, $post);
      curl_setopt ($conn[$i], CURLOPT_HTTPHEADER, $headers);  

      if ($is_progress) {
          curl_setopt ($conn[$i], CURLOPT_PROGRESSFUNCTION, function ($resource,$download_size, $downloaded, $upload_size, $uploaded)
          {
              if($download_size > 0) {
                $progress = round($downloaded / $download_size  * 100);
                echo "Load: $progress " . PHP_EOL;
                flush();
              }
          });
          curl_setopt($conn[$i], CURLOPT_NOPROGRESS, false);
      }
      if (!empty($proxy)) {
          curl_setopt($conn[$i] ,CURLOPT_PROXY, $proxy);
          curl_setopt($conn[$i] ,CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME);
      }
      curl_multi_add_handle ($mh,$conn[$i]);
  }
  $active = null;
  do {
      $mrc = curl_multi_exec($mh, $active);
  } while ($mrc == CURLM_CALL_MULTI_PERFORM);

  while ($active && $mrc == CURLM_OK) {
      // ждём какую-нибудь активность от потоков
      if (curl_multi_select($mh) == -1) {
          usleep(100);
      }

      // опрашиваем curl_multi_exec на предмет, есть ли ещё активные потоки
      do {
          $mrc = curl_multi_exec($mh, $active);
      } while ($mrc == CURLM_CALL_MULTI_PERFORM);
  }  

  foreach ($ar_url as $i => $url) {
      $code_arr[$i] = curl_getinfo($conn[$i],CURLINFO_HTTP_CODE);
      curl_multi_remove_handle($mh,$conn[$i]);
      curl_close($conn[$i]);
      fclose($fp[$i]);
  }
  curl_multi_close($mh);
  return true;
}

// TEST
//$ar_url=array();$ar_fname=array();$ar_code=array();
//$ar_url[0]='http://127.0.0.1/src001.zip';$ar_fname[0]='tmp/src001.zip';
//$ar_url[1]='http://127.0.0.1/src002.zip';$ar_fname[1]='tmp/src002.zip';
//$ar_url[2]='http://127.0.0.1/src003.zip';$ar_fname[2]='tmp/src003.zip';
//$ar_url[3]='http://127.0.0.1/src004.zip';$ar_fname[3]='tmp/src004.zip';
//$is_progress=true;
//curl_multi_load_file($ar_url,$ar_fname,$ar_code,'',$is_progress);
//print_r($ar_code);