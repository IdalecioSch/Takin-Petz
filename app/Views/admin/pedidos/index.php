<h1>Pedidos de adoção</h1>
<form method="get" action="<?= e(url('admin/pedidos')) ?>" class="filtros">
  <div><label for="status">Status</label><select id="status" name="status"><option value="">Todos</option>
    <?php foreach (dominio('status_solicitacao') as $cod => $nome): ?>
      <option value="<?= e($cod) ?>"<?= selecionado($status, $cod) ?>><?= e($nome) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div><label for="animal">Animal</label><select id="animal" name="animal"><option value="">Todos</option>
    <?php foreach ($animais as $a): ?>
      <option value="<?= $a->id() ?>"<?= selecionado($animalId, $a->id()) ?>><?= e($a->nome) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div class="acoes"><button class="btn" type="submit">Filtrar</button></div>
</form>

<?php if (!$pedidos): ?>
  <p class="mut">Nenhum pedido encontrado.</p>
<?php else: ?>
<div class="tabela-wrap"><table>
  <thead><tr><th>#</th><th>Solicitante</th><th>Animal</th><th>Status</th><th>Enviado em</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($pedidos as $p): ?>
    <tr><td><?= $p->id() ?></td><td><?= e($p->pessoa_nome) ?></td><td><?= e($p->animal_nome) ?></td>
        <td><span class="badge <?= e($p->status) ?>"><?= e($p->statusNome()) ?></span></td>
        <td><?= e(data_br($p->criado_em)) ?></td>
        <td><a href="<?= e(url('admin/pedidos/' . $p->id())) ?>">Detalhes</a></td></tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
