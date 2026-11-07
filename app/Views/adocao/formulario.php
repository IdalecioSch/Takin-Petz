<h1>Pedido de adoção: <?= e($animal->nome) ?></h1>
<p><a href="<?= e(url('animais/' . $animal->id())) ?>">← Voltar ao animal</a></p>
<?php parcial('erros', ['erros' => $erros]); ?>

<form method="post" action="<?= e(url('animais/' . $animal->id() . '/adotar')) ?>" novalidate>
  <?= csrf_field() ?>
  <div class="hp" aria-hidden="true"><label>Não preencha <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

  <fieldset>
    <legend>Seus dados</legend>
    <label for="nome">Nome completo</label>
    <input type="text" id="nome" name="nome" value="<?= old('nome') ?>" required autocomplete="name">
    <div class="cols">
      <div><label for="email">E-mail</label><input type="email" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email"></div>
      <div><label for="telefone">Telefone (com DDD)</label><input type="tel" id="telefone" name="telefone" value="<?= old('telefone') ?>" required autocomplete="tel"></div>
      <div><label for="cidade">Cidade</label><input type="text" id="cidade" name="cidade" value="<?= old('cidade') ?>" required></div>
      <div><label for="uf">Estado</label>
        <select id="uf" name="uf" required>
          <?php foreach (dominio('estados') as $sigla => $nomeEstado): ?>
            <option value="<?= e($sigla) ?>"<?= selecionado($_POST['uf'] ?? 'SC', $sigla) ?>><?= e($sigla) ?> · <?= e($nomeEstado) ?></option>
          <?php endforeach; ?>
        </select></div>
    </div>
  </fieldset>

  <fieldset>
    <legend>Sobre sua casa</legend>
    <label for="moradia">Tipo de moradia</label>
    <select id="moradia" name="moradia" required>
      <option value="">Selecione</option>
      <?php foreach (dominio('tipos_moradia') as $cod => $nomeMoradia): ?>
        <option value="<?= e($cod) ?>"<?= selecionado($_POST['moradia'] ?? '', $cod) ?>><?= e($nomeMoradia) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="outros_animais">Você possui outros animais?</label>
    <select id="outros_animais" name="outros_animais" required>
      <option value="">Selecione</option>
      <option value="sim"<?= selecionado($_POST['outros_animais'] ?? '', 'sim') ?>>Sim</option>
      <option value="nao"<?= selecionado($_POST['outros_animais'] ?? '', 'nao') ?>>Não</option>
    </select>
    <label for="motivacao">Por que você quer adotar?</label>
    <textarea id="motivacao" name="motivacao" required><?= old('motivacao') ?></textarea>
  </fieldset>

  <fieldset>
    <legend>Declarações</legend>
    <label class="check"><input type="checkbox" name="maior_18" value="1"<?= marcado(!empty($_POST['maior_18'])) ?>> Declaro ter mais de 18 anos.</label>
    <label class="check"><input type="checkbox" name="termo" value="1"<?= marcado(!empty($_POST['termo'])) ?>>
      Aceito o termo de responsabilidade: me comprometo com a posse responsável, oferecendo alimentação, cuidados veterinários e carinho, e a nunca abandonar o animal.</label>
    <label class="check"><input type="checkbox" name="lgpd" value="1"<?= marcado(!empty($_POST['lgpd'])) ?>>
      Concordo que meus dados pessoais sejam usados apenas para analisar este pedido e contatar você, e posso pedir a exclusão deles à entidade a qualquer momento.</label>
  </fieldset>

  <button class="btn" type="submit">Enviar pedido</button>
</form>
