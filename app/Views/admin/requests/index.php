<h1>Pedidos de adoção</h1>
<form method="get" action="<?= e(url('admin/pedidos')) ?>" class="filters">
  <div><label for="status">Status</label><select id="status" name="status"><option value="">Todos</option>
    <?php foreach (domain('status_solicitacao') as $code => $text): ?>
      <option value="<?= e($code) ?>"<?= selected($status, $code) ?>><?= e($text) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div><label for="animal">Animal</label><select id="animal" name="animal"><option value="">Todos</option>
    <?php foreach ($animals as $Animal): ?>
      <option value="<?= $Animal->getId() ?>"<?= selected($animalId, $Animal->getId()) ?>><?= e($Animal->nome) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div class="row"><button class="btn" type="submit">Filtrar</button></div>
</form>

<?php if (!$requests): ?>
  <p class="muted">Nenhum pedido encontrado.</p>
<?php else: ?>
<div class="table-wrap"><table>
  <thead><tr><th>#</th><th>Solicitante</th><th>Animal</th><th>Status</th><th>Enviado em</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($requests as $Request): ?>
    <tr><td><?= $Request->getId() ?></td><td><?= e($Request->pessoa_nome) ?></td><td><?= e($Request->animal_nome) ?></td>
        <td><span class="badge <?= e($Request->status) ?>"><?= e($Request->getStatusLabel()) ?></span></td>
        <td><?= e(date_br($Request->criado_em)) ?></td>
        <td><a href="<?= e(url('admin/pedidos/' . $Request->getId())) ?>">Detalhes</a></td></tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
