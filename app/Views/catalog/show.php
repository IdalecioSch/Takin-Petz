<?php $photos = $Animal->getPhotos(); ?>
<div class="detail">
  <div class="gallery">
    <img class="main" src="<?= e(photo_url($photos ? $photos[0]->arquivo : null)) ?>" alt="Foto principal de <?= e($Animal->nome) ?>">
    <?php if (count($photos) > 1): ?>
      <div class="thumbs">
        <?php foreach (array_slice($photos, 1) as $i => $Photo): ?>
          <a href="<?= e($Photo->getUrl()) ?>" target="_blank" rel="noopener"><img src="<?= e($Photo->getUrl()) ?>" alt="Foto <?= $i + 2 ?> de <?= e($Animal->nome) ?>" loading="lazy"></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="info-card">
    <h1><?= e($Animal->nome) ?></h1>
    <ul class="chips">
      <li class="chip"><?= e($Animal->getSpeciesLabel()) ?></li>
      <li class="chip"><?= e($Animal->getSexLabel()) ?></li>
      <li class="chip teal">Porte <?= e(strtolower($Animal->getSizeLabel())) ?></li>
      <li class="chip"><?= e($Animal->getAgeGroupLabel()) ?></li>
      <?php if ($Animal->getBreedName() !== ''): ?><li class="chip teal"><?= e($Animal->getBreedName()) ?></li><?php endif; ?>
      <?php if ($Animal->getColorName() !== ''): ?><li class="chip"><?= e($Animal->getColorName()) ?></li><?php endif; ?>
    </ul>
    <?php if ($Animal->descricao): ?><h3 style="margin-top:20px">Sobre</h3><p><?= nl2br(e($Animal->descricao)) ?></p><?php endif; ?>
    <?php if ($Animal->temperamento): ?><h3>Temperamento</h3><p><?= nl2br(e($Animal->temperamento)) ?></p><?php endif; ?>
    <h3>Saúde</h3>
    <ul class="health-list">
      <li>Castrado: <span class="<?= $Animal->castrado ? 'pill-ok' : 'pill-no' ?>"><?= yes_no($Animal->castrado) ?></span></li>
      <li>Vacinado: <span class="<?= $Animal->vacinado ? 'pill-ok' : 'pill-no' ?>"><?= yes_no($Animal->vacinado) ?></span></li>
      <li>Vermifugado: <span class="<?= $Animal->vermifugado ? 'pill-ok' : 'pill-no' ?>"><?= yes_no($Animal->vermifugado) ?></span></li>
    </ul>
    <?php if ($vaccinations): ?>
      <h3 style="margin-top:16px">Vacinas aplicadas</h3>
      <ul class="health-list">
        <?php foreach ($vaccinations as $Vaccination): ?>
          <li><?= e($Vaccination->vacina_nome) ?> · <?= e(date_br($Vaccination->aplicada_em, false)) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <div class="cta"><a class="btn" href="<?= e(url('animais/' . $Animal->getId() . '/adotar')) ?>">Quero adotar <?= e($Animal->nome) ?> 🐾</a></div>
  </div>
</div>
