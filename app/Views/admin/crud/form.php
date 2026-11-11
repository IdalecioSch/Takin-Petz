<h1><?= $Model->isNew() ? 'Novo(a) ' : 'Editar ' ?><?= e(strtolower($singular)) ?></h1>
<?php partial('errors', ['errors' => $errors]); ?>
<form method="post" class="form-narrow" action="<?= e(url($Model->isNew() ? $route : $route . '/' . $Model->getId())) ?>" novalidate>
  <?= csrf_field() ?>
  <fieldset>
    <?php foreach ($fields as $field): $value = $Model->{$field['name']}; ?>
      <label for="f_<?= e($field['name']) ?>"><?= e($field['label']) ?><?= !empty($field['required']) ? ' *' : '' ?></label>
      <?php if ($field['type'] === 'select'): ?>
        <select id="f_<?= e($field['name']) ?>" name="<?= e($field['name']) ?>">
          <option value=""><?= e(isset($field['empty']) ? $field['empty'] : 'Selecione') ?></option>
          <?php foreach ($field['options'] as $code => $text): ?>
            <option value="<?= e($code) ?>"<?= selected($value, $code) ?>><?= e($text) ?></option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        <input type="text" id="f_<?= e($field['name']) ?>" name="<?= e($field['name']) ?>" value="<?= e($value) ?>"<?= !empty($field['max']) ? ' maxlength="' . (int)$field['max'] . '"' : '' ?>>
      <?php endif; ?>
    <?php endforeach; ?>
  </fieldset>
  <div class="row"><button class="btn" type="submit">Salvar</button><a class="btn btn-ghost" href="<?= e(url($route)) ?>">Cancelar</a></div>
</form>
