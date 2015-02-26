<?php
function member_text($member, $certno = true) {
  $txt = "";
  if($member['NickName'] != '')
    $txt = $member['FirstName'].' "'.$member['NickName'].'" '.$member['LastName'];
  else
    $txt = $member['FirstName'].' '.$member['LastName'];
    
  if((int)$member['MemberNo'] > 0 && $certno) {
     $txt = $txt . ' ('.$member['MemberNo'].')';
  }
  
  return $txt;
}
?>
