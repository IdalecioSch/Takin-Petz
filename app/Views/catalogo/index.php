<h1>Animais para adoção</h1>
<form method="get" action="<?= e(url('animais')) ?>" class="filtros" aria-label="Filtros">
  <?php
  $campos = ['especie' => ['Espécie', 'especies'], 'porte' => ['Porte', 'portes'], 'sexo' => ['Sexo', 'sexos'], 'faixa_etaria' => ['Idade', 'faixas_etarias']];
  foreach ($campos as $nome => [$rot, $tabela]): ?>
    <div>
      <label for="f-<?= e($nome) ?>"><?= e($rot) ?></label>
      <select id="f-<?= e($nome) ?>" name="<?= e($nome) ?>">
        <option value="">Todos</option>
        <?php foreach (dominio($tabela) as $cod => $nomeOpcao): ?>
          <option value="<?= e($cod) ?>"<?= selecionado($filtros[$nome], $cod) ?>><?= e($nomeOpcao) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endforeach; ?>
  <div class="acoes"><button class="btn" type="submit">Filtrar</button>
    <a class="btn sec" href="<?= e(url('animais')) ?>">Limpar</a></div>
</form>

<?php if (!$animais): ?>
  <p class="mut">Nenhum animal encontrado com esses filtros.</p>
<?php else: ?>
  <div class="grid"><?php foreach ($animais as $a) { parcial('card_animal', ['a' => $a]); } ?></div>
<?php endif; ?>
