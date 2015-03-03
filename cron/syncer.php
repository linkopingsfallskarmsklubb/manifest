<?php
require_once('../config.php');
if(isset($_SERVER['REMOTE_ADDR'])) {
  exit('Only script execution allowed');
}

function upload_data($url, $data) {
  $ch = curl_init($url);
  $data['secret'] = WEBSITE_SECRET;
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

  if (curl_exec($ch) === false) {
    exit('Curl error: ' . curl_error($ch));
  }

  curl_close($ch);
} 

function upload_weather() {
  $data = array('dump' => '@D:\vader\downld02.txt');
  upload_data('https://dev.linkopingsfallskarmsklubb.se/templates/lfk/api/weather.php', $data);
}

function upload_database() {
  /* Upload the database to linkopingsfallskarmsklubb.se */
  $temp = tempnam('/tmp', 'dump');
  $descs = array(
    0 => array('pipe', 'r'),
    1 => array('file', $temp, 'w'),
    2 => array('pipe', 'w')
  );

  $cmd = 'mysqldump skywin -u ' . DB_USERNAME . ' -p"' . DB_PASSWORD . '"';
  $process = proc_open($cmd, $descs, $pipes, DB_MYSQL_DIR);

  $stderr = stream_get_contents($pipes[2]);
  fclose($pipes[0]);
  fclose($pipes[2]);

  $data = file_get_contents($temp);
  $compressed = gzencode($data);
  file_put_contents($temp, $compressed);

  if (proc_close($process) !== 0) {
    die('Unable to dump database: ' . $stderr);
  }

  $data = array('dump' => '@' . $temp);
  upload_data('https://dev.linkopingsfallskarmsklubb.se/templates/lfk/api/import.php', $data);
  unlink($temp);
 }
 
 echo "Initial weather upload: ";
 upload_weather();
 echo "Done\n";
 
 $oldmin = -1;
 while(true) {
   $min = (int)date('i');
   
   if ($oldmin != $min) {
     if ($min % 5 == 0) {
       echo "5 min, uploading weather information: ";
       upload_weather();
       echo "Done\n";
     }
     
     if ($min == 0) {
       echo "Whole hour, uploading database: ";
       upload_database();
       echo "Done\n";
     }
   }
   
   $oldmin = $min;
   echo "Minute is $min\r";
   
   sleep(30);
 }
?>