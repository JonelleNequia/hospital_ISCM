<?php /** @var App $app */ $base = $app->baseUrl(); ?>
<h1>New Supplier</h1>
<div class="card">
  <form method="post" action="<?=$base?>/suppliers/store" class="form">
    <input type="hidden" name="csrf" value="<?=$app->csrfToken()?>">
    <div class="grid cols-2">
      <div>
        <div class="label">Name *</div>
        <input class="input" name="name" required>
      </div>
      <div>
        <div class="label">Contact</div>
        <input class="input" name="contact">
      </div>
      <div>
        <div class="label">Phone</div>
        <input class="input" name="phone">
      </div>
      <div>
        <div class="label">Email</div>
        <input class="input" name="email">
      </div>
      <div class="col-span-2">
        <div class="label">Address</div>
        <input class="input" name="address">
      </div>
    </div>
    <div style="margin-top:1rem">
      <button class="btn">Save</button>
      <a class="btn" href="<?=$base?>/suppliers" style="background:#777">Cancel</a>
    </div>
  </form>
</div>