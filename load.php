<?php
require_once("db.php");
require_once("util.php");

$date = date("Y-m-d");
$db = new SkyWinDatabase();
$loads = $db->execute("SELECT LoadNo,PlaneReg, Call15min, Call30min".
  " FROM `Load` WHERE (LoadStatus = 1 or LoadStatus = 2) AND Regdate = '".
  $date." 00:00:00' ORDER BY LiftedAt ASC");

$result = array();
$i = 0;
foreach($loads as $load) {
  $i++;
  if($i > 3)
    break;
    
  $loadno = $load['LoadNo'];
  $aircraft = $load['PlaneReg'];
  $jumpers = $db->execute("SELECT InternalNo, GroupNo, Altitude, StudentJumpNo".
    " FROM Loadjump WHERE LoadNo = ".$loadno." AND PlaneReg = '".$aircraft."'".
    " AND Regdate = '".$date." 00:00:00' ORDER BY Altitude,LastUpd ASC");
    
  $roles = $db->execute("SELECT InternalNo, RoleType FROM Loadrole".
     " WHERE LoadNo = ".$loadno." AND PlaneReg = '".$aircraft."'".
     " AND Regdate = '".$date." 00:00:00'");
  
  $rload = array("aircraft" => $aircraft, "load" => $loadno,
    "call" => array("30" => $load['Call30min'], "15" => $load['Call15min']),
    "jumpers" => array());
  foreach($jumpers as $jumper) {
    $jid = $jumper['InternalNo'];
    $rjumper = array();
	$member = $db->get_member($jid);
	if($member['LicenseType'] == 'E' && $member['Education'] != NULL) {
		$jumpno = $jumper['StudentJumpNo'];
		$sjn = $db->execute("SELECT StudentJumpNo FROM Typeeducations".
		  " WHERE Education = ?", array($member['Education']));
		$jump = $db->execute("SELECT * FROM Typestudentjumprow".
		  " WHERE StudentJumpNo = ? AND StudentJumpRowNo = ?",
		  array($sjn[0]['StudentJumpNo'], $jumpno));
		if(isset($jump[0])) {
		  $jump = $jump[0];
		  $rjumper['student'] = array('jump' => $jumpno,
			'education' => $sjn[0]['StudentJumpNo']);
		}
	}
    $rjumper['jumper'] = member_text($member);
    $rjumper['internal'] = $jid;
    $rjumper['altitude'] = $jumper['Altitude'];
    $rjumper['group'] = $jumper['GroupNo'];
    $rjumper['roles'] = array();
    foreach($roles as $role) {
      if($role['InternalNo'] == $jid) {
        $rjumper['roles'][] = $role['RoleType'];
      }
    }
    
    $rload['jumpers'][] = $rjumper;
  }
  
  $result[] = $rload;
}

echo json_encode($result);
?>
