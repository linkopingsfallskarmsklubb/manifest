<?php
require_once("db.php");
require_once("util.php");

$db = new SkyWinDatabase();
$educations = array();
$values = $db->execute("SELECT * FROM Typestudentjump");
foreach($values as $value) {
  $educations[$value['StudentJumpNo']] = $value['StudentJumpName'];
}

$values = $db->execute("SELECT * FROM Typestudentjumprow ORDER BY StudentJumpNo, StudentJumpRowNo ASC");

$result = array();
foreach($values as $value) {
  $result[] = array('education' => $value['StudentJumpNo'],
    'form' => $educations[$value['StudentJumpNo']],
    'altitude' => $value['Altitude'],
    'jumptype' => $value['JumpType'],
    'jump' => $value['StudentJumpRowNo'],
    'program' => $value['Program']);
}

include("cacheable.php");
echo json_encode($result);
?>