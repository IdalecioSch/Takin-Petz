<?php
$action = $Animal->isNew() ? 'admin/animais' : 'admin/animais/' . $Animal->getId();
$options = function ($table, $current) {
    echo '<option value="">Selecione</option>';
    foreach (domain($table) as $code => $text) {
        echo '<option value="' . e($code) . '"' . selected($current, $code) . '>' . e($text) . '</option>';
    }
};
$photos = $Animal->getPhotos();
?>
<h1><?= e($title) ?></h1>
<?php partial('errors', ['errors' => $errors]); ?>

<form method="post" action="<?= e(url($action)) ?>" enctype="multipart/form-data" class="form-narrow" novalidate>
  <?= csrf_field() ?>

  <fieldset>
    <legend>Dados do animal</legend>
    <label for="nome">Nome</label>
    <input type="text" id="nome" name="nome" value="<?= e($Animal->nome) ?>" required maxlength="80">
    <div class="grid-2">
      <div><label for="especie">Espécie</label><select id="especie" name="especie" required><?php $options('especies', $Animal->especie); ?></select></div>
      <div><label for="sexo">Sexo</label><select id="sexo" name="sexo" required><?php $options('sexos', $Animal->sexo); ?></select></div>
      <div><label for="porte">Porte</label><select id="porte" name="porte" required><?php $options('portes', $Animal->porte); ?></select></div>
      <div><label for="faixa_etaria">Faixa de idade</label><select id="faixa_etaria" name="faixa_etaria" required><?php $options('faixas_etarias', $Animal->faixa_etaria); ?></select></div>
      <div><label for="raca_id">Raça</label>
        <select id="raca_id" name="raca_id">
          <option value="">Não informada</option>
          <?php foreach ($breeds as $Breed): ?>
            <option value="<?= $Breed->getId() ?>" data-species="<?= e($Breed->especie) ?>"<?= selected($Animal->raca_id, $Breed->getId()) ?>><?= e($Breed->nome) ?></option>
          <?php endforeach; ?>
        </select>
        <small class="muted">Falta a raça? <a href="<?= e(url('admin/racas/novo')) ?>">Cadastre aqui</a>.</small></div>
      <div><label for="cor_id">Cor</label>
        <select id="cor_id" name="cor_id">
          <option value="">Não informada</option>
          <?php foreach ($colors as $Color): ?>
            <option value="<?= $Color->getId() ?>"<?= selected($Animal->cor_id, $Color->getId()) ?>><?= e($Color->nome) ?></option>
          <?php endforeach; ?>
        </select>
        <small class="muted"><a href="<?= e(url('admin/cores/novo')) ?>">Cadastrar nova cor</a></small></div>
    </div>
    <label for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao"><?= e($Animal->descricao) ?></textarea>
    <label for="temperamento">Temperamento</label>
    <textarea id="temperamento" name="temperamento"><?= e($Animal->temperamento) ?></textarea>
  </fieldset>

  <fieldset>
    <legend>Saúde e status</legend>
    <label class="check"><input type="checkbox" name="castrado" value="1"<?= checked($Animal->castrado) ?>> Castrado</label>
    <label class="check"><input type="checkbox" name="vacinado" value="1"<?= checked($Animal->vacinado) ?>> Vacinado</label>
    <label class="check"><input type="checkbox" name="vermifugado" value="1"<?= checked($Animal->vermifugado) ?>> Vermifugado</label>
    <label for="status">Status</label>
    <select id="status" name="status">
      <?php foreach (domain('status_animal') as $code => $text): ?>
        <option value="<?= e($code) ?>"<?= selected($Animal->status ?: 'disponivel', $code) ?>><?= e($text) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if (!$Animal->isNew()): ?><p style="margin-top:12px"><a href="<?= e(url('admin/animais/' . $Animal->getId() . '/saude')) ?>">Vacinas, dieta e consultas →</a></p><?php endif; ?>
  </fieldset>

  <fieldset>
    <legend>Fotos (JPG, PNG ou WEBP · até <?= round(config('upload.max_bytes') / 1048576) ?> MB · máx. <?= (int)config('upload.max_fotos') ?>)</legend>
    <?php if ($photos): ?>
      <div class="photo-edit">
        <?php foreach ($photos as $Photo): ?>
          <figure>
            <img src="<?= e($Photo->getUrl()) ?>" alt="Foto atual">
            <label class="check"><input type="radio" name="principal" value="<?= $Photo->getId() ?>"<?= checked($Photo->principal) ?>> Principal</label>
            <label class="check"><input type="checkbox" name="remove[]" value="<?= $Photo->getId() ?>"> Remover</label>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <label for="photos"><?= $photos ? 'Adicionar fotos' : 'Enviar fotos (pelo menos uma)' ?></label>
    <input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
  </fieldset>

  <div class="row"><button class="btn" type="submit">Salvar</button><a class="btn btn-ghost" href="<?= e(url('admin/animais')) ?>">Cancelar</a></div>
</form>
