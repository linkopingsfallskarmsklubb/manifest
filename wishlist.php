<?php
require_once("db.php");
require_once("util.php");

$db = new SkyWinDatabase();
$requests = $db->execute("SELECT * FROM Loadjumprequest ORDER BY TimeForRequest ASC");

$result = array();
foreach($requests as $request) {
  $jump_type = $db->execute("SELECT JumpTypeName FROM Typejumps".
    " WHERE JumpType = ?", array($request['JumpType']));
   
  $student = null;
  if($request['StudentJumpNo']) {
    $edu = $db->execute("SELECT Education FROM Member".
      " WHERE InternalNo = ?", array($request['InternalNo']));
    $sjn = $db->execute("SELECT StudentJumpNo FROM Typeeducations".
      " WHERE Education = ?", array($edu[0]['Education']));
    $student = array('jump' => $request['StudentJumpNo'],
      'education' => $sjn[0]['StudentJumpNo']);
  }
  
  $waited = time(NULL) - strtotime($request['TimeForRequest']);
  $result[] = array(
    'jumper' => member_text($db->get_member($request['InternalNo'])),
    'aircraft' => $request['PlaneReg'], 
    'altitude' => $request['Altitude'],
    'jumptype' => $jump_type[0]['JumpTypeName'],
    'jumptype_internal' => $request['JumpType'],
    'student' => $student,
    'time' => $request['TimeForRequest'],
    'internal' => $request['InternalNo'],
    'waited' => $waited,
    'group' => $request['ReqAsGroup'],
    'comment' => $request['Comment']);
}

echo json_encode($result);
?>
