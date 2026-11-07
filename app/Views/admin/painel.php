<h1>Painel</h1>
<div class="resumo">
  <div><strong><?= (int)$pendentes ?></strong>Pedidos pendentes</div>
  <?php foreach (dominio('status_animal') as $cod => $nome): ?>
    <div><strong><?= (int)$porStatus[$cod] ?></strong><?= e($nome) ?></div>
  <?php endforeach; ?>
</div>

<h2>Pedidos pendentes recentes</h2>
<?php if (!$ultimos): ?>
  <p class="mut">Nenhum pedido pendente.</p>
<?php else: ?>
  <div class="tabela-wrap"><table>
    <thead><tr><th>#</th><th>Solicitante</th><th>Animal</th><th>Enviado em</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($ultimos as $p): ?>
      <tr><td><?= $p->id() ?></td><td><?= e($p->pessoa_nome) ?></td><td><?= e($p->animal_nome) ?></td>
          <td><?= e(data_br($p->criado_em)) ?></td>
          <td><a href="<?= e(url('admin/pedidos/' . $p->id())) ?>">Analisar</a></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
<?php endif; ?>
<p style="margin-top:20px" class="acoes">
  <a class="btn" href="<?= e(url('admin/animais/novo')) ?>">+ Cadastrar animal</a>
  <a class="btn sec" href="<?= e(url('admin/pedidos')) ?>">Ver todos os pedidos</a>
</p>
