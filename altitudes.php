<?php
require_once("db.php");
require_once("util.php");

$db = new SkyWinDatabase();
$values = $db->execute("SELECT * FROM TypeAltitudes WHERE AltitudeWishlist = 'Y' ORDER BY Altitude DESC");

$result = array();
foreach($values as $value) {
  $result[] = (int)$value['Altitude'];
}

include("cacheable.php");
echo json_encode($result);
?>
