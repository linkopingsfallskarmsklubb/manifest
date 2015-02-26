<?php
require_once "config.php";

function compare_members($val1, $val2) {
  return (int)$val1['InternalNo'] - (int)$val2['InternalNo'];
}

class SkyWinDatabase {

  private $db = NULL;

  function __construct() {
    $this->db = new PDO(DB_CONNECTION, DB_USERNAME, DB_PASSWORD)
      or die ("No MySQL connection");
      
    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  function __destruct() {
  }
  
  function begin_transaction() {
    $this->db->query("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");
    $this->db->beginTransaction();
  }
  
  function commit() {
    $this->db->commit();
  }
  
  function rollback() {
    $this->db->rollback();
  }

  function execute($sql, $arguments = array()) {
    $stmt = $this->db->prepare($sql);
    $stmt->execute($arguments);
    if($stmt->columnCount() == 0)
      return true;
    return $stmt->fetchAll();
  }
  
  function get_member($id) {
    $data = $this->execute("SELECT * FROM Member WHERE InternalNo = ?", array((int)$id));
    if(count($data) == 0)
      return false;
      
    return $data[0];
  }

  function find_members($critera) {
    $data = NULL;

    if(is_numeric($critera)) {
      $data = $this->execute("SELECT * FROM Member WHERE MemberNo LIKE ? OR InternalNo = ?",
        array("%$critera%", $critera));
    } else {
      $data = array();
      $parts = explode(" ", $critera);
      foreach($parts as $part) {
        if(strlen($part) > 2) {
          $data[] = $this->execute("SELECT * FROM Member WHERE FirstName LIKE ? OR ".
            "LastName LIKE ? OR NickName LIKE ? ORDER BY LastUpd DESC",
            array("%$part%", "%$part%", "%$part%"));
        }
      }
      
      $result = $data[0];
      for($i = 1; $i < count($data); $i++) {
        $result = array_uintersect($result, $data[$i], 'compare_members');
      }
      
      $data = $result;
    }

    return $data;
  }
}
?>
