<?php
$menu = ['' => 'Início', 'animais' => 'Quero adotar', 'sobre' => 'Sobre', 'contato' => 'Contato'];
$siteName = \App\Core\Messages::SITE_NAME;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> · <?= e($siteName) ?></title>
  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
</head>
<body data-base="<?= e(url('/')) ?>">
<a class="skip" href="#conteudo">Ir para o conteúdo</a>
<header class="site-header"><div class="container header-in">
  <a class="brand" href="<?= e(url('/')) ?>"><?php partial('logo'); ?>Takin <b>Petz</b></a>
  <nav class="nav" aria-label="Principal">
    <?php foreach ($menu as $path => $text): ?>
      <a href="<?= e(url($path)) ?>"<?= $active === $path ? ' aria-current="page"' : '' ?>><?= e($text) ?></a>
    <?php endforeach; ?>
    <a class="btn btn-sm" href="<?= e(url('animais')) ?>">Adotar agora</a>
  </nav>
</div></header>
<main id="conteudo" class="container">
  <?= flash_render() ?>
  <?= $content ?>
</main>
<footer class="site-footer"><div class="container row between">
  <div>
    <span class="brand"><?php partial('logo'); ?>Takin <b>Petz</b></span>
    <p style="margin:10px 0 0">Em parceria com <strong><?= e(config('entidade.nome')) ?></strong><br>
      <?= e(config('entidade.email')) ?> · <?= e(config('entidade.telefone')) ?></p>
  </div>
  <p><a href="<?= e(url('admin/login')) ?>">Área administrativa</a></p>
</div></footer>
<script src="<?= e(url('js/app.js')) ?>"></script>
</body>
</html>
