<h1>Painel</h1>
<div class="summary">
  <div class="hot"><strong><?= (int)$pendingCount ?></strong>Pedidos pendentes</div>
  <?php foreach (domain('status_animal') as $code => $text): ?>
    <div><strong><?= (int)$byStatus[$code] ?></strong><?= e($text) ?></div>
  <?php endforeach; ?>
</div>

<div class="panel">
  <h2>Pedidos pendentes recentes</h2>
  <?php if (!$latest): ?>
    <p class="muted">Nenhum pedido pendente.</p>
  <?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>#</th><th>Solicitante</th><th>Animal</th><th>Enviado em</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($latest as $Request): ?>
        <tr><td><?= $Request->getId() ?></td><td><?= e($Request->pessoa_nome) ?></td><td><?= e($Request->animal_nome) ?></td>
            <td><?= e(date_br($Request->criado_em)) ?></td>
            <td><a href="<?= e(url('admin/pedidos/' . $Request->getId())) ?>">Analisar</a></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</div>
<div class="row">
  <a class="btn" href="<?= e(url('admin/animais/novo')) ?>">+ Cadastrar animal</a>
  <a class="btn btn-ghost" href="<?= e(url('admin/pedidos')) ?>">Ver todos os pedidos</a>
</div>
