<?php $menu = ['painel' => ['admin', 'Painel'], 'animais' => ['admin/animais', 'Animais'], 'pedidos' => ['admin/pedidos', 'Pedidos']]; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= e($titulo ?? '') ?> · Admin</title>
  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body class="admin">
<header class="topo"><div class="wrap topo-in">
  <span class="logo">⚙️ Painel</span>
  <nav aria-label="Admin">
    <?php foreach ($menu as $chave => [$caminho, $rotulo]): ?>
      <a href="<?= e(url($caminho)) ?>"<?= ($ativo ?? '') === $chave ? ' aria-current="page"' : '' ?>><?= e($rotulo) ?></a>
    <?php endforeach; ?>
    <a href="<?= e(url('/')) ?>">Ver site</a>
    <form method="post" action="<?= e(url('admin/logout')) ?>" class="inline">
      <?= csrf_field() ?>
      <button class="link" type="submit">Sair (<?= e($admin->nome) ?>)</button>
    </form>
  </nav>
</div></header>
<main class="wrap">
  <?= flash_render() ?>
  <?= $conteudo ?>
</main>
</body>
</html>
