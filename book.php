<?php
require_once("db.php");
$db = new SkyWinDatabase();

if(isset($_GET['remove'])) {
  $id = (int)$_GET['remove'];
  $db->execute("DELETE FROM Loadjumprequest WHERE InternalNo = ?", array((int)$id));
  exit('[]');
}

if(!isset($_POST['jumper']) || "" ==  $_POST['jumper']) {
  exit(json_encode(array("error" => "no_data")));
}

$id = (int)$_POST['jumper'];
$comment = $_POST['comment'];
$jumptype = $_POST['jumptype'];
$aircraft = $_POST['aircraft'];
$altitude = $_POST['altitude'];
$group = (int)$_POST['group'];
$student = NULL;
if(isset($_POST['student'])) {
  $student = $_POST['student'];
}

$db = new SkyWinDatabase();
$check = $db->execute("SELECT InternalNo,ReqAsGroup FROM Loadjumprequest WHERE InternalNo = ?", array((int)$id));

if($_POST['edit'] === "true") {
  if(count($check) === 0)
    exit(json_encode(array("error" => "no_exists")));
    
  if($group !== (int)$check[0]['ReqAsGroup']) {
    /* switched group or went solo, update altitude an so on if new group */
    if($group === 0) {
      $group = time(NULL) % 864000 * 10 + $id % 10;
    } else {
      /* set same altitude, aircraft and type of jump as the other group members */
      $g = $db->execute("SELECT Altitude, PlaneReg, JumpType FROM Loadjumprequest WHERE ReqAsGroup = ?", array((int)$group));
      if(count($g) === 0)
        exit(json_encode(array("error" => "no_group")));

      $altitude = $g[0]['Altitude'];
      $aircraft = $g[0]['PlaneReg'];
      $jumptype = $g[0]['JumpType'];
    }
    $db->execute("UPDATE Loadjumprequest SET JumpType = ?, ".
      " PlaneReg = ?, Altitude = ?, ReqAsGroup = ?,".
      " Comment = ?, StudentJumpNo = ? WHERE InternalNo = ?", 
        array($jumptype, $aircraft, $altitude, $group, $comment, $student, $id));
  } else {
    /* same group, update settings for whole group */
    $db->execute("UPDATE Loadjumprequest SET JumpType = ?, ".
      " PlaneReg = ?, Altitude = ?, StudentJumpNo = ?".
      " WHERE ReqAsGroup = ?", 
      array($jumptype, $aircraft, $altitude, $student, $group));
    $db->execute("UPDATE Loadjumprequest SET Comment = ?".
      " WHERE InternalNo = ?", array($comment, $id));
  }
} else {

  if(count($check) > 0)
    exit(json_encode(array("error" => "exists")));

  if($group === 0) {
    $group = time(NULL) % 864000 * 10 + $id % 10;
  } else {
    /* set same altitude, aircraft and type of jump as the other group members */
    $g = $db->execute("SELECT Altitude, PlaneReg, JumpType FROM Loadjumprequest WHERE ReqAsGroup = ?",
        array((int)$group));
    if(count($g) == 0)
      exit(json_encode(array("error" => "no_group")));
    
    $altitude = $g[0]['Altitude'];
    $aircraft = $g[0]['PlaneReg'];
    $jumptype = $g[0]['JumpType'];  
  }
  
  $db->execute("INSERT INTO Loadjumprequest (".
    "InternalNo,TimeForRequest,JumpType,PlaneReg,".
    "Altitude,StudentJumpNo,CanopyId,Organizer,".
    "Computername,Userid,LastUpd,Validated,ReqAsGroup,Comment".
    ") VALUES (?, NOW(), ?, ?, ?, ?, NULL, 'N', 'Web', 'Web', NOW(), 'Y', ?, ?)",
    array($id, $jumptype, $aircraft, $altitude, $student, $group, $comment));
}

echo '[]';
?>
