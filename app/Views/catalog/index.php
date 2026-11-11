<h1 style="margin-top:28px">Animais para adoção</h1>
<form method="get" action="<?= e(url('animais')) ?>" class="filters" aria-label="Filtros">
  <?php
  $fields = ['especie' => ['Espécie', 'especies'], 'porte' => ['Porte', 'portes'], 'sexo' => ['Sexo', 'sexos'], 'faixa_etaria' => ['Idade', 'faixas_etarias']];
  foreach ($fields as $name => $field): ?>
    <div>
      <label for="f-<?= e($name) ?>"><?= e($field[0]) ?></label>
      <select id="f-<?= e($name) ?>" name="<?= e($name) ?>">
        <option value="">Todos</option>
        <?php foreach (domain($field[1]) as $code => $text): ?>
          <option value="<?= e($code) ?>"<?= selected($filters[$name], $code) ?>><?= e($text) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endforeach; ?>
  <div class="row"><button class="btn" type="submit">Filtrar</button><a class="btn btn-ghost" href="<?= e(url('animais')) ?>">Limpar</a></div>
</form>

<?php if (!$animals): ?>
  <p class="muted">Nenhum animal encontrado com esses filtros.</p>
<?php else: ?>
  <div class="cards"><?php foreach ($animals as $Animal) { partial('animal_card', ['Animal' => $Animal]); } ?></div>
<?php endif; ?>
