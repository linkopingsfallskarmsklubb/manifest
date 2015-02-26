<?php
require_once("db.php");
require_once("util.php");

$db = new SkyWinDatabase();
$members = $db->find_members($_GET['term']);

$result = array();
$cacheable = true;
foreach($members as $member) {
  if('PAX' == $member['MemberType'])
    continue;
  $label = member_text($member);
  $student = null;
  
  if($member['LicenseType'] == 'E' && $member['Education'] != NULL
     && isset($_GET['details'])) {
    $cacheable = false;
    $jumpno = $db->execute("SELECT StudentJumpNo FROM Member".
      " WHERE InternalNo = ?", array($member['InternalNo']));
    $jumpno = $jumpno[0]['StudentJumpNo'] + 1;
    $sjn = $db->execute("SELECT StudentJumpNo FROM Typeeducations".
      " WHERE Education = ?", array($member['Education']));
    $jump = $db->execute("SELECT * FROM Typestudentjumprow".
      " WHERE StudentJumpNo = ? AND StudentJumpRowNo = ?",
      array($sjn[0]['StudentJumpNo'], $jumpno));
    if(isset($jump[0])) {
      $jump = $jump[0];
      $student = array('nextjump' => $jumpno,
        'education' => $sjn[0]['StudentJumpNo']);
    }
  }
  
  $result[] = array('label' => $label, 'value' => $member['InternalNo'],
    'student' => $student);
}

if($cacheable)
  include("cacheable.php");

echo json_encode($result);

?>
