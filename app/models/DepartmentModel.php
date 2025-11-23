<?php
class DepartmentModel {
  private $db;
  public function __construct($db){ $this->db = $db; }

  /** Return all departments ordered by name */
  public function listAll(){
    $st = $this->db->query("SELECT id, code, name FROM departments ORDER BY name");
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  /** Check if department exists */
  public function exists($id){
    $st = $this->db->prepare("SELECT 1 FROM departments WHERE id=?");
    $st->execute([(int)$id]);
    return (bool)$st->fetchColumn();
  }
}