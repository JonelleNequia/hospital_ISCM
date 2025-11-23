<?php
class ItemModel {
  private PDO $pdo;
  public function __construct(PDO $pdo){ $this->pdo = $pdo; }

  public function list(string $q = ''): array {
    if ($q === '') {
      return $this->pdo->query("SELECT * FROM items ORDER BY name")->fetchAll();
    }
    $st = $this->pdo->prepare("SELECT * FROM items WHERE name LIKE ? OR sku LIKE ? ORDER BY name");
    $like = "%$q%";
    $st->execute([$like, $like]);
    return $st->fetchAll();
  }

  public function find(int $id): ?array {
    $st = $this->pdo->prepare("SELECT * FROM items WHERE id=?");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public function create(array $d): int {
    $sql = "INSERT INTO items (sku,name,category,uom,is_controlled,expiry_required,lot_tracking,min_stock,max_stock,reorder_point,lead_time_days,active)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    $st = $this->pdo->prepare($sql);
    $st->execute([
      trim($d['sku'] ?? ''), trim($d['name'] ?? ''), trim($d['category'] ?? ''),
      trim($d['uom'] ?? 'EA'),
      !empty($d['is_controlled']) ? 1 : 0,
      !empty($d['expiry_required']) ? 1 : 0,
      !empty($d['lot_tracking']) ? 1 : 0,
      (int)($d['min_stock'] ?? 0),
      (int)($d['max_stock'] ?? 0),
      (int)($d['reorder_point'] ?? 0),
      (int)($d['lead_time_days'] ?? 0),
      !empty($d['active']) ? 1 : 0,
    ]);
    return (int)$this->pdo->lastInsertId();
  }

  public function update(int $id, array $d): void {
    $sql = "UPDATE items SET
              sku=?, name=?, category=?, uom=?,
              is_controlled=?, expiry_required=?, lot_tracking=?,
              min_stock=?, max_stock=?, reorder_point=?, lead_time_days=?, active=?
            WHERE id=?";
    $st = $this->pdo->prepare($sql);
    $st->execute([
      trim($d['sku'] ?? ''), trim($d['name'] ?? ''), trim($d['category'] ?? ''),
      trim($d['uom'] ?? 'EA'),
      !empty($d['is_controlled']) ? 1 : 0,
      !empty($d['expiry_required']) ? 1 : 0,
      !empty($d['lot_tracking']) ? 1 : 0,
      (int)($d['min_stock'] ?? 0),
      (int)($d['max_stock'] ?? 0),
      (int)($d['reorder_point'] ?? 0),
      (int)($d['lead_time_days'] ?? 0),
      !empty($d['active']) ? 1 : 0,
      $id
    ]);
  }
}
