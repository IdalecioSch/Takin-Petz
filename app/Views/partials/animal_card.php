<article class="pet-card">
  <a href="<?= e(url('animais/' . $Animal->getId())) ?>">
    <img src="<?= e(photo_url($Animal->getMainPhoto())) ?>" alt="Foto de <?= e($Animal->nome) ?>" loading="lazy">
    <div class="body">
      <h3><?= e($Animal->nome) ?></h3>
      <ul class="chips">
        <li class="chip"><?= e($Animal->getSpeciesLabel()) ?></li>
        <li class="chip teal"><?= e($Animal->getSizeLabel()) ?></li>
        <li class="chip"><?= e($Animal->getAgeGroupLabel()) ?></li>
      </ul>
    </div>
  </a>
</article>
