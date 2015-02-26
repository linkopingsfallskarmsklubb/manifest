<?php
/* Upload the database to linkopingsfallskarmsklubb.se */

require_once('../config.php');
set_time_limit(300);

if($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  exit('Only localhost allowed');
}

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

// cURL is much easier to handle if we use local files
$ch = curl_init('https://dev.linkopingsfallskarmsklubb.se/templates/lfk/api/import.php');
$data = array('dump' => '@' . $temp);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

if (curl_exec($ch) === false) {
  exit('Curl error: ' . curl_error($ch));
}

curl_close($ch);
unlink($temp);
?>