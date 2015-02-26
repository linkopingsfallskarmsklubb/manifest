<?php
ini_set('display_errors', '1');
require_once('fpdf/fpdf.php');
require_once("db.php");
require_once("util.php");

$date = date("Y-m-d");
$db = new SkyWinDatabase();
$loads = $db->execute("SELECT LoadNo,PlaneReg, Call15min, Call30min".
  " FROM `Load` WHERE Regdate = ? AND LoadNo = ?",
  array($date." 00:00:00", $_GET['load']));

$load = $loads[0];
$loadno = $load['LoadNo'];
$aircraft = $load['PlaneReg'];
$db_jumpers = $db->execute("SELECT InternalNo, GroupNo, Altitude, tj.JumpTypeName, Comment".
  " FROM Loadjump as l, Typejumps as tj WHERE LoadNo = ".$loadno." AND PlaneReg = '".$aircraft."'".
  " AND Regdate = '".$date." 00:00:00' AND tj.JumpType = l.JumpType ORDER BY Altitude, l.Jumptype, l.LastUpd ASC");

$roles = $db->execute("SELECT InternalNo, RoleType FROM Loadrole".
   " WHERE LoadNo = ".$loadno." AND PlaneReg = '".$aircraft."'".
   " AND Regdate = '".$date." 00:00:00'");

$jumpers = array();
foreach($db_jumpers as $jumper) {
  $jid = $jumper['InternalNo'];
  $rjumper = array();
  $member = $db->get_member($jid);
  $rjumper['jumper'] = utf8_decode(member_text($member, false));
  $rjumper['internal'] = $jid;
  $rjumper['member'] = $member;
  $rjumper['altitude'] = $jumper['Altitude'];
  $rjumper['group'] = $jumper['GroupNo'];
  $rjumper['type'] = utf8_decode($jumper['JumpTypeName']);
  $rjumper['comment'] = utf8_decode($jumper['Comment']);
  $rjumper['roles'] = array();
  foreach($roles as $role) {
    if($role['InternalNo'] == $jid) {
      $rjumper['roles'][] = $role['RoleType'];
    }
  }

  $jumpers[] = $rjumper;
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',16);
$pdf->Cell(40,8,'Load sheet',0,1);
$pdf->SetFont('Arial','',9);
$pdf->Cell(40,10,'Datum: '.$date);
$pdf->Cell(40,10,'Lift: '.$loadno . ' / '.$aircraft);
$pdf->Ln();

$prev_altitude = 0;
$pdf->SetFont('Arial','B',9);
$pdf->Cell(20,4,'Nr');
$pdf->Cell(70,4,'Hoppare');
$pdf->Cell(10,4,utf8_decode('Höjd'));
$pdf->Cell(10,4,'Vikt');
$pdf->Cell(20,4,'Typ');
$pdf->Cell(50,4,'Kommentar');
$pdf->Ln();

$pdf->SetFont('Arial','',9);
foreach($jumpers as $jumper) {
  $no = $jumper['member']['MemberNo'];
  if($no == 0) {
    $no = '('.$jumper['member']['InternalNo'].')';
  }
  if($prev_altitude != $jumper['altitude']) {
    $pdf->Ln();
  }
  $prev_altitude = $jumper['altitude'];
  $pdf->Cell(20,4,$no);
  $pdf->Cell(70,4,$jumper['jumper']);
  $pdf->Cell(10,4,$jumper['altitude']);
  $pdf->Cell(10,4,$jumper['member']['Weight']);
  $pdf->Cell(20,4,$jumper['type']);
  $pdf->Cell(50,4,$jumper['comment']);
  $pdf->Ln();
}
$pdf->Output();
?>
