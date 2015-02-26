<?php
require_once("db.php");
require_once("util.php");
$db = new SkyWinDatabase();

if(!isset($_POST['from']) || "" ==  $_POST['from']) {
  exit(json_encode(array("error" => "no_data")));
}

if(!isset($_POST['to']) || "" ==  $_POST['to']) {
  exit(json_encode(array("error" => "no_data")));
}

if(!isset($_POST['amount']) || "" ==  $_POST['amount']) {
  exit(json_encode(array("error" => "no_data")));
}

if(!isset($_POST['comment']) || "" ==  $_POST['comment']) {
  exit(json_encode(array("error" => "no_data")));
}

$from_id = (int)$_POST['from'];
$to_id = (int)$_POST['to'];
$amount = (int)$_POST['amount'];
$comment = $_POST['comment'];

if(0 >= $from_id || 0 >= $to_id || 0 >= $amount) {
  exit(json_encode(array("error" => "invalid_data")));
}

$from = $db->get_member($from_id);
$to = $db->get_member($to_id);

if(false == $from || false == $to) {
  exit(json_encode(array("error" => "no_such_member")));
}

$from_comment = "Till ".member_text($to)." - ".$comment;
$to_comment = "Från ".member_text($from)." - ".$comment;

ignore_user_abort(true);
$db->begin_transaction();
try {

  $to_acc = (int)($db->execute("SELECT AccountNo FROM Member WHERE InternalNo=?", array($to["InternalNo"]))[0][0]);
  $from_acc = (int)($db->execute("SELECT AccountNo FROM Member WHERE InternalNo=?", array($from["InternalNo"]))[0][0]);

  $from_data = 
    $db->execute("SELECT TransNo AS MaxNo, Balance FROM Trans WHERE TransNo=(SELECT MAX(TransNo) FROM Trans WHERE AccountNo=?) AND AccountNo=?",
      array($from_acc, $from_acc));
      
  $to_data = 
    $db->execute("SELECT TransNo AS MaxNo, Balance FROM Trans WHERE TransNo=(SELECT MAX(TransNo) FROM Trans WHERE AccountNo=?) AND AccountNo=?",
      array($to_acc, $to_acc));
      
  if(count($from_data) != 1) {
    exit(json_encode(array("error" => "from_no_history")));
  }

  if(count($to_data) != 1) {
    exit(json_encode(array("error" => "to_no_history")));
  }

  $from_new_balance = (int)$from_data[0]['Balance'] - $amount;
  $from_new_transno = (int)$from_data[0]['MaxNo'] + 1;

  $to_new_balance = (int)$to_data[0]['Balance'] + $amount;
  $to_new_transno = (int)$to_data[0]['MaxNo'] + 1;
      
  $insert = "INSERT INTO Trans (Transno, 
    AccountNo,
    TransType,
    AccountType,
    Regdate,
    Amount,
    Balance,
    `Check`,
    Comment,
    Fromdate,
    Todate,
    BoogieNo,
    Discount,
    PaymentType,
    RelatedInternalNo,
    Userid,
    PaymentReasonCode,
    LastUpd) 
    VALUES (?, ?, 'HOPPKONTO', 'BET', NOW(), ?, ?, ?, ?, Null,
            Null, 0, Null, Null, ?, 'Administrator', 1, NOW())";
            
  $db->execute($insert, array($from_new_transno, $from_acc, -$amount, 
    $from_new_balance, 1337, $from_comment, (int)$to['InternalNo']));

  $db->execute($insert, array($to_new_transno, $to_acc, $amount, 
    $to_new_balance, 1337, $to_comment, (int)$from['InternalNo']));
  
  $db->execute("UPDATE Member SET Balance=?, Userid='Administrator', LastUpd=NOW() WHERE InternalNo=?", 
    array($from_new_balance, (int)$from['InternalNo']));
  $db->execute("UPDATE Member SET Balance=?, Userid='Administrator', LastUpd=NOW() WHERE InternalNo=?", 
    array($to_new_balance, (int)$to['InternalNo']));
    
} catch (Exception $e) {
  $db->rollback();
  exit(json_encode(array("error" => "sql_exception")));
}

try {
  $db->commit();
} catch (Exception $e) {
  exit(json_encode(array("error" => "commit_failed")));
}

echo '[]';
?>