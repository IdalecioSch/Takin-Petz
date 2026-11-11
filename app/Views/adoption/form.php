<section class="section form-narrow">
  <h1>Pedido de adoção: <?= e($Animal->nome) ?></h1>
  <p><a href="<?= e(url('animais/' . $Animal->getId())) ?>">← Voltar ao animal</a></p>
  <?php partial('errors', ['errors' => $errors]); ?>

  <form method="post" action="<?= e(url('animais/' . $Animal->getId() . '/adotar')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="hp" aria-hidden="true"><label>Não preencha <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

    <fieldset>
      <legend>Seus dados</legend>
      <label for="nome">Nome completo</label>
      <input type="text" id="nome" name="nome" value="<?= old('nome') ?>" required autocomplete="name">
      <div class="grid-2">
        <div><label for="email">E-mail</label><input type="email" id="email" name="email" value="<?= old('email') ?>" required autocomplete="email"></div>
        <div><label for="telefone">Telefone (com DDD)</label><input type="tel" id="telefone" name="telefone" value="<?= old('telefone') ?>" required autocomplete="tel"></div>
        <div><label for="uf">Estado</label>
          <select id="uf" name="uf" required>
            <?php foreach (domain('estados') as $abbr => $stateName): ?>
              <option value="<?= e($abbr) ?>"<?= selected(isset($_POST['uf']) ? $_POST['uf'] : 'SC', $abbr) ?>><?= e($abbr) ?> · <?= e($stateName) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div><label for="city_id">Cidade</label>
          <select id="city_id" name="city_id" data-selected="<?= old('city_id') ?>"><option value="">Selecione o estado primeiro</option></select>
          <input type="text" id="city-fallback" name="city_name" value="<?= old('city_name') ?>" placeholder="Digite sua cidade" hidden></div>
      </div>
    </fieldset>

    <fieldset>
      <legend>Sobre sua casa</legend>
      <div class="grid-2">
        <div><label for="moradia">Tipo de moradia</label>
          <select id="moradia" name="moradia" required>
            <option value="">Selecione</option>
            <?php foreach (domain('tipos_moradia') as $code => $text): ?>
              <option value="<?= e($code) ?>"<?= selected(isset($_POST['moradia']) ? $_POST['moradia'] : '', $code) ?>><?= e($text) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div><label for="outros_animais">Você possui outros animais?</label>
          <select id="outros_animais" name="outros_animais" required>
            <option value="">Selecione</option>
            <option value="sim"<?= selected(isset($_POST['outros_animais']) ? $_POST['outros_animais'] : '', 'sim') ?>>Sim</option>
            <option value="nao"<?= selected(isset($_POST['outros_animais']) ? $_POST['outros_animais'] : '', 'nao') ?>>Não</option>
          </select></div>
      </div>
      <label for="motivacao">Por que você quer adotar?</label>
      <textarea id="motivacao" name="motivacao" required><?= old('motivacao') ?></textarea>
    </fieldset>

    <fieldset>
      <legend>Declarações</legend>
      <label class="check"><input type="checkbox" name="maior_18" value="1"<?= checked(!empty($_POST['maior_18'])) ?>> Declaro ter mais de 18 anos.</label>
      <label class="check"><input type="checkbox" name="termo" value="1"<?= checked(!empty($_POST['termo'])) ?>>
        Aceito o termo de responsabilidade: me comprometo com a posse responsável, oferecendo alimentação, cuidados veterinários e carinho, e a nunca abandonar o animal.</label>
      <label class="check"><input type="checkbox" name="lgpd" value="1"<?= checked(!empty($_POST['lgpd'])) ?>>
        Concordo que meus dados pessoais sejam usados apenas para analisar este pedido e contatar você, e posso pedir a exclusão deles à entidade a qualquer momento.</label>
    </fieldset>

    <button class="btn" type="submit">Enviar pedido</button>
  </form>
</section>
