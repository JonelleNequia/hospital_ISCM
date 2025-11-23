<?php // login view: centered auth UI (animation removed) ?>

<div class="eg-login eg-login--centered">
  <!-- Centered Brand + FORM card -->
  <main class="eg-right eg-right--top">
    <header class="eg-brand eg-brand--tight">
      <h1 class="eg-title">EVERGREEN MEDICAL HOSPITAL</h1>
      <p class="eg-sub">INVENTORY AND SUPPLY CHAIN MANAGEMENT</p>
    </header>

    <section class="eg-card eg-card--form">
      <h2 class="eg-card-title eg-card-title--upper">Welcome</h2>
      <p class="eg-card-sub">Sign in to your workspace.</p>

      <?php if(!empty($error)): ?>
        <div class="flash" style="background:#ffecec;border:1px solid #fadadaff;color:#c91010ff;padding:10px 12px;border-radius:10px;margin-bottom:12px">
          <?=$error?>
        </div>
      <?php endif; ?>

      <form method="post" action="login" class="eg-form eg-form--xl">
        <label class="eg-lab">Email
          <input class="eg-inp eg-inp--xl" type="email" name="email" required>
        </label>
        <label class="eg-lab">Password
          <input class="eg-inp eg-inp--xl" type="password" name="password" required>
        </label>
        <button class="eg-btn eg-btn--xl" type="submit">Login</button>
      </form>
    </section>
  </main>
</div>

