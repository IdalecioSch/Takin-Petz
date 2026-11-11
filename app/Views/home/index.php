<section class="hero">
  <h1><?= e(\App\Core\Messages::SITE_SLOGAN) ?> 🐾</h1>
  <p>Conheça os bichinhos que esperam por um lar e envie seu pedido de adoção em poucos minutos.</p>
  <div class="row">
    <a class="btn" href="<?= e(url('animais')) ?>">Ver animais disponíveis</a>
    <a class="btn btn-ghost" href="<?= e(url('sobre')) ?>">Como funciona</a>
  </div>
  <div class="stats">
    <div class="stat"><strong><?= (int)$availableCount ?></strong>esperando por um lar</div>
    <div class="stat"><strong><?= (int)$adoptedCount ?></strong>já adotados</div>
  </div>
</section>

<section class="section">
  <div class="section-head"><h2>Chegaram recentemente</h2><a href="<?= e(url('animais')) ?>">Ver todos →</a></div>
  <?php if (!$recent): ?>
    <p class="muted">Nenhum animal disponível no momento. Volte em breve!</p>
  <?php else: ?>
    <div class="cards"><?php foreach ($recent as $Animal) { partial('animal_card', ['Animal' => $Animal]); } ?></div>
  <?php endif; ?>
</section>

<section class="section">
  <h2>Como adotar</h2>
  <div class="steps">
    <div class="step"><div class="num">1</div><h3>Escolha</h3><p class="muted">Veja os animais disponíveis e conheça a história de cada um.</p></div>
    <div class="step"><div class="num">2</div><h3>Peça</h3><p class="muted">Preencha o pedido de adoção contando um pouco sobre você e sua casa.</p></div>
    <div class="step"><div class="num">3</div><h3>Aguarde</h3><p class="muted">Nossa equipe analisa e você recebe o resultado por e-mail.</p></div>
  </div>
</section>
