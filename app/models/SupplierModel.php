<?php
class SupplierModel {
  private $db;
  public function __construct($db){ $this->db = $db; }

  public function listAll(){
    $st = $this->db->query("SELECT id, name, contact, phone, email, address FROM suppliers ORDER BY name");
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public function create($data){
    $sql = "INSERT INTO suppliers (name, contact, phone, email, address)
            VALUES (:name, :contact, :phone, :email, :address)";
    $st = $this->db->prepare($sql);
    $st->execute([
      ':name'    => trim($data['name'] ?? ''),
      ':contact' => trim($data['contact'] ?? ''),
      ':phone'   => trim($data['phone'] ?? ''),
      ':email'   => trim($data['email'] ?? ''),
      ':address' => trim($data['address'] ?? ''),
    ]);
    return (int)$this->db->lastInsertId();
  }

  public function find(int $id){
    $st = $this->db->prepare('SELECT id,name,contact,phone,email,address FROM suppliers WHERE id = ? LIMIT 1');
    $st->execute([$id]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
  }

  public function update(int $id, array $data){
    $sql = "UPDATE suppliers SET name = :name, contact = :contact, phone = :phone, email = :email, address = :address WHERE id = :id";
    $st = $this->db->prepare($sql);
    $st->execute([
      ':name' => trim($data['name'] ?? ''),
      ':contact' => trim($data['contact'] ?? ''),
      ':phone' => trim($data['phone'] ?? ''),
      ':email' => trim($data['email'] ?? ''),
      ':address' => trim($data['address'] ?? ''),
      ':id' => $id
    ]);
    return (int)$id;
  }
}