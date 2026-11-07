<?php
$entidade = config('entidade.nome');
$menu = ['' => 'Início', 'animais' => 'Quero adotar', 'sobre' => 'Sobre', 'contato' => 'Contato'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo ?? '') ?> · <?= e($entidade) ?></title>
  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body>
<a class="skip" href="#conteudo">Ir para o conteúdo</a>
<header class="topo"><div class="wrap topo-in">
  <a class="logo" href="<?= e(url('/')) ?>">🐾 <?= e($entidade) ?></a>
  <nav aria-label="Principal">
    <?php foreach ($menu as $caminho => $rotulo): ?>
      <a href="<?= e(url($caminho)) ?>"<?= ($ativo ?? null) === $caminho || (($ativo ?? '') === 'index' && $caminho === '') ? ' aria-current="page"' : '' ?>><?= e($rotulo) ?></a>
    <?php endforeach; ?>
  </nav>
</div></header>
<main id="conteudo" class="wrap">
  <?= flash_render() ?>
  <?= $conteudo ?>
</main>
<footer class="rodape"><div class="wrap">
  <p><?= e($entidade) ?> · <?= e(config('entidade.email')) ?> · <?= e(config('entidade.telefone')) ?></p>
  <p><a href="<?= e(url('admin/login')) ?>">Área administrativa</a></p>
</div></footer>
</body>
</html>
