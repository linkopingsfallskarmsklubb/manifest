<?php
require_once("db.php");

if($_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
  exit("Only localhost allowed");
}

$db = new SkyWinDatabase();
$requests = $db->execute("SELECT RequestNo FROM Loadjumprequest ORDER BY TimeForRequest ASC");

$result = array();
foreach($requests as $request) {
  $result[] = $request['RequestNo'];
}

shuffle($result);

foreach($result as $id => $db_id) {
  $db->execute("UPDATE Loadjumprequest SET TimeForRequest = ".
   "DATE_ADD(DATE_ADD(NOW(),INTERVAL -1 hour), INTERVAL " . $id . " second) ".
   "WHERE RequestNo = ? LIMIT 1" , array($db_id));
}
?>