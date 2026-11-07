<?php $animal = $s->animal(); $pessoa = $s->pessoa(); $id = $s->id(); ?>
<p><a href="<?= e(url('admin/pedidos')) ?>">← Voltar aos pedidos</a></p>
<h1>Pedido #<?= $id ?> <span class="badge <?= e($s->status) ?>"><?= e($s->statusNome()) ?></span></h1>

<div class="tabela-wrap"><table>
  <tr><th>Animal</th><td><?= e($animal->nome) ?> (<?= e($animal->statusNome()) ?>)</td></tr>
  <tr><th>Solicitante</th><td><?= e($pessoa->nome) ?></td></tr>
  <tr><th>E-mail</th><td><?= e($pessoa->email) ?></td></tr>
  <tr><th>Telefone</th><td><?= e($pessoa->telefone) ?></td></tr>
  <tr><th>Cidade</th><td><?= e($pessoa->cidade()?->rotulo()) ?></td></tr>
  <tr><th>Moradia</th><td><?= e($s->moradiaNome()) ?></td></tr>
  <tr><th>Outros animais</th><td><?= sim_nao($s->tem_outros_animais) ?></td></tr>
  <tr><th>Motivação</th><td><?= nl2br(e($s->motivacao)) ?></td></tr>
  <tr><th>Termo / LGPD</th><td>Termo aceito em <?= e(data_br($s->termo_aceito_em)) ?> · LGPD aceita em <?= e(data_br($s->lgpd_aceito_em)) ?></td></tr>
  <tr><th>Enviado em</th><td><?= e(data_br($s->criado_em)) ?></td></tr>
  <?php if (!$s->pendente()): ?>
    <tr><th>Decisão</th><td><?= e(data_br($s->decidido_em)) ?> por <?= e($s->decisor()?->nome ?? '—') ?></td></tr>
    <?php if ($s->motivo_recusa): ?><tr><th>Motivo da recusa</th><td><?= nl2br(e($s->motivo_recusa)) ?></td></tr><?php endif; ?>
  <?php endif; ?>
</table></div>

<?php if ($s->pendente()): ?>
<h2>Decisão</h2>
<div class="acoes" style="align-items:flex-start;gap:24px">
  <form method="post" action="<?= e(url('admin/pedidos/' . $id . '/aprovar')) ?>"
        onsubmit="return confirm('Aprovar este pedido? O animal passará a Adotado e os demais pedidos pendentes serão recusados.')">
    <?= csrf_field() ?>
    <button class="btn" type="submit">Aprovar pedido</button>
  </form>
  <form method="post" action="<?= e(url('admin/pedidos/' . $id . '/recusar')) ?>" style="flex:1 1 300px">
    <?= csrf_field() ?>
    <label for="motivo">Motivo da recusa (opcional, mas recomendado)</label>
    <textarea id="motivo" name="motivo" maxlength="1000"></textarea>
    <p><button class="btn perigo" type="submit">Recusar pedido</button></p>
  </form>
</div>
<?php endif; ?>

<h2>Privacidade</h2>
<form method="post" action="<?= e(url('admin/pedidos/' . $id . '/excluir-dados')) ?>"
      onsubmit="return confirm('Excluir definitivamente os dados deste solicitante, incluindo todos os pedidos e mensagens dele?')">
  <?= csrf_field() ?>
  <button class="btn sec" type="submit">Excluir dados do solicitante (LGPD)</button>
</form>
