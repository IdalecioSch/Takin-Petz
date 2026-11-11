<div class="row between"><h1><?= e($title) ?></h1><a class="btn" href="<?= e(url($route . '/novo')) ?>">+ Novo(a) <?= e(strtolower($singular)) ?></a></div>
<?php if (!$rows): ?>
  <p class="muted">Nenhum registro cadastrado.</p>
<?php else: ?>
<div class="table-wrap"><table>
  <thead><tr><?php foreach ($columns as $column): ?><th><?= e($column['label']) ?></th><?php endforeach; ?><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $Row): ?>
    <tr>
      <?php foreach ($columns as $column): ?><td><?= e($Row->{$column['field']}) ?></td><?php endforeach; ?>
      <td><div class="row">
        <a href="<?= e(url($route . '/' . $Row->getId() . '/editar')) ?>">Editar</a>
        <form method="post" class="inline" action="<?= e(url($route . '/' . $Row->getId() . '/excluir')) ?>" data-confirm="Excluir este registro?">
          <?= csrf_field() ?><button class="link-btn danger" type="submit">Excluir</button></form>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
