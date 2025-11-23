<h1>Edit Supplier</h1>
<div class="card">
<form class="form" method="post" action="suppliers/update">
  <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
  <input type="hidden" name="id" value="<?=htmlspecialchars($supplier['id'] ?? '')?>">
  <div class="grid cols-2">
    <div><div class="label">Name</div><input class="input" name="name" value="<?=htmlspecialchars($supplier['name'] ?? '')?>" required></div>
    <div><div class="label">Contact</div><input class="input" name="contact" value="<?=htmlspecialchars($supplier['contact'] ?? '')?>"></div>
    <div><div class="label">Email</div><input class="input" type="email" name="email" value="<?=htmlspecialchars($supplier['email'] ?? '')?>"></div>
    <div><div class="label">Phone</div><input class="input" name="phone" value="<?=htmlspecialchars($supplier['phone'] ?? '')?>"></div>
    <div class="col-span-2"><div class="label">Address</div><input class="input" name="address" value="<?=htmlspecialchars($supplier['address'] ?? '')?>"></div>
  </div>
  <button class="btn">Update Supplier</button>
</form>
</div>
