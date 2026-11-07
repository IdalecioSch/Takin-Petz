<div class="login">
  <h1>Área administrativa</h1>
  <?php if ($erro): ?><div class="alert alert-erro" role="alert"><?= e($erro) ?></div><?php endif; ?>
  <form method="post" action="<?= e(url('admin/login')) ?>">
    <?= csrf_field() ?>
    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" value="<?= old('email') ?>" required autofocus autocomplete="username">
    <label for="senha">Senha</label>
    <input type="password" id="senha" name="senha" required autocomplete="current-password">
    <p><button class="btn" type="submit">Entrar</button></p>
  </form>
</div>
