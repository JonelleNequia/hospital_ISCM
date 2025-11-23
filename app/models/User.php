<?php
class User {
  private $pdo;
  public function __construct($pdo){ $this->pdo=$pdo; }
  public function findByEmail($email){
    $st = $this->pdo->prepare("SELECT u.id,u.name,u.email,u.password_hash,u.role_id,r.name as role
                               FROM users u JOIN roles r ON r.id=u.role_id
                               WHERE LOWER(u.email)=LOWER(?) AND u.status='ACTIVE'");
    $st->execute([trim((string)$email)]);
    return $st->fetch();
  }
}
