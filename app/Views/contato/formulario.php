<h1>Contato</h1>
<p>
  <strong><?= e(config('entidade.nome')) ?></strong><br>
  E-mail: <?= e(config('entidade.email')) ?><br>
  Telefone: <?= e(config('entidade.telefone')) ?><br>
  Endereço: <?= e(config('entidade.endereco')) ?>
</p>
<?php parcial('erros', ['erros' => $erros]); ?>

<form method="post" action="<?= e(url('contato')) ?>" novalidate>
  <?= csrf_field() ?>
  <div class="hp" aria-hidden="true"><label>Não preencha <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
  <label for="nome">Nome</label>
  <input type="text" id="nome" name="nome" value="<?= old('nome') ?>" required>
  <label for="email">E-mail</label>
  <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
  <label for="mensagem">Mensagem</label>
  <textarea id="mensagem" name="mensagem" required><?= old('mensagem') ?></textarea>
  <label class="check"><input type="checkbox" name="lgpd" value="1"<?= marcado(!empty($_POST['lgpd'])) ?>>
    Concordo que meus dados sejam usados apenas para responder esta mensagem.</label>
  <button class="btn" type="submit">Enviar</button>
</form>
