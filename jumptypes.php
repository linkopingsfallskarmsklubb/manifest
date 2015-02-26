<?php
require_once("db.php");
require_once("util.php");

$db = new SkyWinDatabase();
$values = $db->execute("SELECT * FROM Typejumps WHERE JumpTypeWishlist = 'Y' ORDER BY JumpType ASC");

$result = array();
foreach($values as $value) {
  $result[] = array('jumptype' => $value['JumpType'],
    'label' => $value['JumpTypeName']);
}

include("cacheable.php");
echo json_encode($result);
?>