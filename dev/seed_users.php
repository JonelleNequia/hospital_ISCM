<?php
// CLI helper to seed sample roles and users for testing/demo
// Usage: php dev/seed_users.php

$pdo = new PDO('mysql:host=localhost;dbname=hospital_scms', 'root', '');
$roles = [
  'Admin' => 1,
  'Procurement' => 2,
  'Receiver' => 3,
  'InventoryClerk' => 4,
  'Pharmacy' => 5,
  'DeptReq' => 6,
  'Auditor' => 7,
];
$users = [
  [ 'name'=>'System Admin',      'email'=>'admin@hospital.local',       'role'=>'Admin',          'password'=>'admin123' ],
  [ 'name'=>'Pharma',            'email'=>'pharmacy@hospital.local',    'role'=>'Pharmacy',       'password'=>'pharmacy123' ],
  [ 'name'=>'Procurement User',  'email'=>'procurement@hospital.local', 'role'=>'Procurement',    'password'=>'procurement123' ],
  [ 'name'=>'Inventory Clerk',   'email'=>'invclerk@hospital.local',    'role'=>'InventoryClerk', 'password'=>'clerk123' ],
  [ 'name'=>'Receiver User',     'email'=>'receiver@hospital.local',    'role'=>'Receiver',       'password'=>'receiver123' ],
  [ 'name'=>'Dept Requester',    'email'=>'deptreq@hospital.local',     'role'=>'DeptReq',        'password'=>'deptreq123' ],
  [ 'name'=>'Auditor User',      'email'=>'auditor@hospital.local',     'role'=>'Auditor',        'password'=>'auditor123' ],
];

foreach ($users as $u) {
  $role_id = $roles[$u['role']];
  $hash = password_hash($u['password'], PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("INSERT INTO users (role_id, name, email, password_hash, status) VALUES (?,?,?,?, 'ACTIVE') ON DUPLICATE KEY UPDATE name=VALUES(name), password_hash=VALUES(password_hash), status='ACTIVE'");
  $stmt->execute([$role_id, $u['name'], $u['email'], $hash]);
  echo "Seeded: {$u['name']} ({$u['email']}) [{$u['role']}] Password: {$u['password']}\n";
}
echo "Done.\n";
