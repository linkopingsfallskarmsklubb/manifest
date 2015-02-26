<?php
require_once("db.php");
require_once("util.php");

$db = new SkyWinDatabase();
$values = $db->execute("SELECT * FROM Plane WHERE InUse = 'Y' ORDER BY PlaneReg ASC");

$result = array();
foreach($values as $value) {
  $result[] = array('aircraft' => $value['PlaneReg'],
    'altitude' => (int)$value['MaxAltitude']);
}

include("cacheable.php");
echo json_encode($result);
?>
