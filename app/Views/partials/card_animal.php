<article class="card">
  <a href="<?= e(url('animais/' . $a->id())) ?>">
    <img src="<?= e(foto_url($a->fotoPrincipal())) ?>" alt="Foto de <?= e($a->nome) ?>" loading="lazy">
    <div class="card-corpo">
      <h3><?= e($a->nome) ?></h3>
      <p><?= e($a->especieNome() . ' · ' . $a->porteNome() . ' · ' . $a->faixaNome()) ?></p>
    </div>
  </a>
</article>
