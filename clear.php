<?php
require_once("db.php");

if($_SERVER['REMOTE_ADDR'] != "127.0.0.1") {
  exit("Only localhost allowed");
}

$db = new SkyWinDatabase();
$db->execute("DELETE FROM Loadjumprequest");


?>