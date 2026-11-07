<?php $fotos = $animal->fotos(); ?>
<div class="detalhe">
  <div class="galeria">
    <img src="<?= e(foto_url($fotos[0]->arquivo ?? null)) ?>" alt="Foto principal de <?= e($animal->nome) ?>">
    <?php if (count($fotos) > 1): ?>
      <div class="miniaturas">
        <?php foreach (array_slice($fotos, 1) as $i => $f): ?>
          <a href="<?= e(foto_url($f->arquivo)) ?>" target="_blank" rel="noopener">
            <img src="<?= e(foto_url($f->arquivo)) ?>" alt="Foto <?= $i + 2 ?> de <?= e($animal->nome) ?>" loading="lazy"></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div>
    <h1><?= e($animal->nome) ?></h1>
    <ul class="tags">
      <li><?= e($animal->especieNome()) ?></li>
      <li><?= e($animal->sexoNome()) ?></li>
      <li>Porte <?= e(mb_strtolower($animal->porteNome())) ?></li>
      <li><?= e($animal->faixaNome()) ?></li>
      <?php if ($animal->racaNome() !== ''): ?><li><?= e($animal->racaNome()) ?></li><?php endif; ?>
      <?php if ($animal->corNome() !== ''): ?><li><?= e($animal->corNome()) ?></li><?php endif; ?>
    </ul>
    <?php if ($animal->descricao): ?><h2>Sobre</h2><p><?= nl2br(e($animal->descricao)) ?></p><?php endif; ?>
    <?php if ($animal->temperamento): ?><h2>Temperamento</h2><p><?= nl2br(e($animal->temperamento)) ?></p><?php endif; ?>
    <h2>Saúde</h2>
    <ul>
      <li>Castrado: <?= sim_nao($animal->castrado) ?></li>
      <li>Vacinado: <?= sim_nao($animal->vacinado) ?></li>
      <li>Vermifugado: <?= sim_nao($animal->vermifugado) ?></li>
    </ul>
    <a class="btn" href="<?= e(url('animais/' . $animal->id() . '/adotar')) ?>">Quero adotar <?= e($animal->nome) ?></a>
  </div>
</div>
