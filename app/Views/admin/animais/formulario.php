<?php
$acao = $animal->existe() ? 'admin/animais/' . $animal->id() : 'admin/animais';
$opcoes = function (string $tabela, ?string $atual, bool $vazio = true): void {
    if ($vazio) echo '<option value="">Selecione</option>';
    foreach (dominio($tabela) as $cod => $nome) {
        echo '<option value="' . e($cod) . '"' . selecionado($atual, $cod) . '>' . e($nome) . '</option>';
    }
};
?>
<h1><?= e($titulo) ?></h1>
<?php parcial('erros', ['erros' => $erros]); ?>

<form method="post" action="<?= e(url($acao)) ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>

  <fieldset>
    <legend>Dados do animal</legend>
    <label for="nome">Nome</label>
    <input type="text" id="nome" name="nome" value="<?= e($v['nome']) ?>" required maxlength="80">
    <div class="cols">
      <div><label for="especie">Espécie</label><select id="especie" name="especie" required><?php $opcoes('especies', $v['especie']); ?></select></div>
      <div><label for="sexo">Sexo</label><select id="sexo" name="sexo" required><?php $opcoes('sexos', $v['sexo']); ?></select></div>
      <div><label for="porte">Porte</label><select id="porte" name="porte" required><?php $opcoes('portes', $v['porte']); ?></select></div>
      <div><label for="faixa_etaria">Faixa de idade</label><select id="faixa_etaria" name="faixa_etaria" required><?php $opcoes('faixas_etarias', $v['faixa_etaria']); ?></select></div>
      <div><label for="raca">Raça (opcional)</label><input type="text" id="raca" name="raca" value="<?= e($v['raca']) ?>" maxlength="80"></div>
      <div><label for="cor">Cor (opcional)</label><input type="text" id="cor" name="cor" value="<?= e($v['cor']) ?>" maxlength="60"></div>
    </div>
    <label for="descricao">Descrição</label>
    <textarea id="descricao" name="descricao"><?= e($v['descricao']) ?></textarea>
    <label for="temperamento">Temperamento</label>
    <textarea id="temperamento" name="temperamento"><?= e($v['temperamento']) ?></textarea>
  </fieldset>

  <fieldset>
    <legend>Saúde e status</legend>
    <label class="check"><input type="checkbox" name="castrado" value="1"<?= marcado($v['castrado']) ?>> Castrado</label>
    <label class="check"><input type="checkbox" name="vacinado" value="1"<?= marcado($v['vacinado']) ?>> Vacinado</label>
    <label class="check"><input type="checkbox" name="vermifugado" value="1"<?= marcado($v['vermifugado']) ?>> Vermifugado</label>
    <label for="status">Status</label>
    <select id="status" name="status"><?php $opcoes('status_animal', $v['status'], false); ?></select>
  </fieldset>

  <fieldset>
    <legend>Fotos (JPG, PNG ou WEBP · até <?= round(config('upload.max_bytes') / 1048576) ?> MB · máx. <?= (int)config('upload.max_fotos') ?>)</legend>
    <?php if ($fotos): ?>
      <div class="fotos-edit">
        <?php foreach ($fotos as $f): ?>
          <figure>
            <img src="<?= e(foto_url($f->arquivo)) ?>" alt="Foto atual">
            <label class="radio"><input type="radio" name="principal" value="<?= $f->id() ?>"<?= marcado($f->principal) ?>> Principal</label>
            <label class="check"><input type="checkbox" name="remover[]" value="<?= $f->id() ?>"> Remover</label>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <label for="fotos"><?= $fotos ? 'Adicionar fotos' : 'Enviar fotos (pelo menos uma)' ?></label>
    <input type="file" id="fotos" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple>
  </fieldset>

  <div class="acoes"><button class="btn" type="submit">Salvar</button>
    <a class="btn sec" href="<?= e(url('admin/animais')) ?>">Cancelar</a></div>
</form>
