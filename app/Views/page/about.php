<section class="section form-narrow">
  <h1>Sobre o <?= e(\App\Core\Messages::SITE_NAME) ?></h1>
  <p>O Takin Petz é um projeto de extensão que apoia a <strong><?= e(config('entidade.nome')) ?></strong> na divulgação e adoção responsável de animais resgatados.</p>
  <h2>Nossa história</h2>
  <p class="muted">Texto a definir após a entrevista com a entidade parceira.</p>
  <h2>Missão</h2>
  <p>Promover a posse responsável e combater o abandono, encontrando lares cuidadosos para cada animal.</p>
  <h2>Como adotar</h2>
  <ol>
    <li>Escolha um animal no <a href="<?= e(url('animais')) ?>">catálogo</a>.</li>
    <li>Preencha o pedido de adoção.</li>
    <li>Aguarde nosso contato e o resultado da análise por e-mail.</li>
  </ol>
</section>
