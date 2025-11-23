<?php
class RequisitionModel {
  private $db;
  public function __construct(PDO $db){ $this->db=$db; }

  public function departments(){
    return $this->db->query("SELECT id, code, name FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
  }
  public function items(){
    return $this->db->query("SELECT id, sku, name, uom FROM items WHERE active=1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
  }
  private function nextNo(){
    $no = $this->db->query("SELECT CONCAT('REQ-', LPAD(COALESCE(MAX(id)+1,1),6,'0')) v FROM requisitions")->fetchColumn();
    return $no ?: 'REQ-000001';
  }

  public function createRequisition(array $data, int $userId){
    $this->db->beginTransaction();
    try {
      $req_no = $this->nextNo();
      $dept_id = (int)($data['department_id'] ?? 0);
      $priority = $data['priority'] ?? 'NORMAL';

      $stmt = $this->db->prepare("INSERT INTO requisitions (req_no, department_id, requested_by, status, priority) VALUES (?,?,?,?,?)");
      $stmt->execute([$req_no, $dept_id, $userId, 'SUBMITTED', $priority]);
      $req_id = (int)$this->db->lastInsertId();

      // items[] at qty_requested[] arrays
      $items = $data['item_id'] ?? [];
      $qtys  = $data['qty_requested'] ?? [];
      $ri = $this->db->prepare("INSERT INTO requisition_items (requisition_id, item_id, qty_requested) VALUES (?,?,?)");
      for ($i=0; $i<count($items); $i++){
        $it = (int)$items[$i];
        $q  = max(0, (int)$qtys[$i]);
        if ($it>0 && $q>0) $ri->execute([$req_id, $it, $q]);
      }

      $this->db->commit();
      return $req_id;
    } catch(\Throwable $e){
      $this->db->rollBack();
      throw $e;
    }
  }
}
