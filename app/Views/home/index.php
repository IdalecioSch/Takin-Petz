<section class="hero">
  <h1>Adote, acolha, transforme uma vida 🐾</h1>
  <p>Conheça os animais que esperam por um lar e envie seu pedido de adoção de forma simples.</p>
  <a class="btn" href="<?= e(url('animais')) ?>">Ver animais disponíveis</a>
</section>

<h2>Chegaram recentemente</h2>
<?php if (!$recentes): ?>
  <p class="mut">Nenhum animal disponível no momento. Volte em breve!</p>
<?php else: ?>
  <div class="grid"><?php foreach ($recentes as $a) { parcial('card_animal', ['a' => $a]); } ?></div>
<?php endif; ?>

<section style="margin-top:32px">
  <h2>Como adotar</h2>
  <ol>
    <li>Escolha um animal no catálogo e preencha o pedido de adoção.</li>
    <li>Nossa equipe analisa o pedido e entra em contato.</li>
    <li>Você recebe o resultado por e-mail.</li>
  </ol>
  <p>Dúvidas? Fale conosco pela página de <a href="<?= e(url('contato')) ?>">contato</a>.</p>
</section>
