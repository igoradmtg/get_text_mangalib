<?php
//chdir('/usr/www/telegraph');
while(true) {
  $time_min = date("Y-m-d H:i");
  $time_sec = date("Y-m-d H:i:s");
  system("php -f load_manga_to_zip.php");
  sleep(2);
  echo "Time: $time_sec \r\n";
}
