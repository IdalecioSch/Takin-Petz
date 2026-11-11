<?php
$menu = ['painel' => ['admin', 'Painel'], 'animais' => ['admin/animais', 'Animais'], 'pedidos' => ['admin/pedidos', 'Pedidos']];
$registries = ['admin/racas' => 'Raças', 'admin/cores' => 'Cores', 'admin/veterinarios' => 'Veterinários', 'admin/vacinas' => 'Vacinas', 'admin/sintomas' => 'Sintomas'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= e($title) ?> · Admin · Takin Petz</title>
  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body data-base="<?= e(url('/')) ?>">
<div class="admin-shell">
  <aside class="sidebar">
    <a class="brand" href="<?= e(url('admin')) ?>" style="padding:0"><?php partial('logo'); ?>Takin <b>Petz</b></a>
    <nav aria-label="Admin">
      <?php foreach ($menu as $key => $item): ?>
        <a href="<?= e(url($item[0])) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($item[1]) ?></a>
      <?php endforeach; ?>
      <h4>Cadastros</h4>
      <?php foreach ($registries as $path => $text): ?>
        <a href="<?= e(url($path)) ?>"<?= $active === $path ? ' aria-current="page"' : '' ?>><?= e($text) ?></a>
      <?php endforeach; ?>
      <h4><?= e($Admin->nome) ?></h4>
      <a href="<?= e(url('/')) ?>">Ver o site</a>
      <form method="post" action="<?= e(url('admin/logout')) ?>"><?= csrf_field() ?><button class="link-btn" type="submit">Sair</button></form>
    </nav>
  </aside>
  <main class="admin-main">
    <?= flash_render() ?>
    <?= $content ?>
  </main>
</div>
<script src="<?= e(url('js/app.js')) ?>"></script>
</body>
</html>
