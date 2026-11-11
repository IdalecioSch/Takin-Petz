<?php $id = $Solicitacao->getId(); ?>
<p><a href="<?= e(url('admin/pedidos')) ?>">← Voltar aos pedidos</a></p>
<h1>Pedido #<?= $id ?> <span class="badge <?= e($Solicitacao->status) ?>"><?= e($Solicitacao->getStatusLabel()) ?></span></h1>

<div class="table-wrap"><table class="kv">
  <tr><th>Animal</th><td><?= e($Solicitacao->animal_nome) ?> (<?= e(label('status_animal', $Solicitacao->animal_status)) ?>)</td></tr>
  <tr><th>Solicitante</th><td><?= e($Solicitacao->pessoa_nome) ?></td></tr>
  <tr><th>E-mail</th><td><?= e($Solicitacao->pessoa_email) ?></td></tr>
  <tr><th>Telefone</th><td><?= e($Solicitacao->pessoa_telefone) ?></td></tr>
  <tr><th>Cidade</th><td><?= e($Solicitacao->getCityLabel()) ?></td></tr>
  <tr><th>Moradia</th><td><?= e($Solicitacao->getHousingLabel()) ?></td></tr>
  <tr><th>Outros animais</th><td><?= yes_no($Solicitacao->tem_outros_animais) ?></td></tr>
  <tr><th>Motivação</th><td><?= nl2br(e($Solicitacao->motivacao)) ?></td></tr>
  <tr><th>Termo / LGPD</th><td>Termo aceito em <?= e(date_br($Solicitacao->termo_aceito_em)) ?> · LGPD aceita em <?= e(date_br($Solicitacao->lgpd_aceito_em)) ?></td></tr>
  <tr><th>Enviado em</th><td><?= e(date_br($Solicitacao->criado_em)) ?></td></tr>
  <?php if (!$Solicitacao->isPending()): ?>
    <tr><th>Decisão</th><td><?= e(date_br($Solicitacao->decidido_em)) ?> por <?= e($Solicitacao->decisor_nome ?: '—') ?></td></tr>
    <?php if ($Solicitacao->motivo_recusa): ?><tr><th>Motivo da recusa</th><td><?= nl2br(e($Solicitacao->motivo_recusa)) ?></td></tr><?php endif; ?>
  <?php endif; ?>
</table></div>

<?php if ($Solicitacao->isPending()): ?>
<div class="panel" style="margin-top:22px">
  <h2>Decisão</h2>
  <div class="row" style="align-items:flex-start;gap:28px">
    <form method="post" action="<?= e(url('admin/pedidos/' . $id . '/aprovar')) ?>"
          data-confirm="Aprovar este pedido? O animal passará a Adotado e os demais pedidos pendentes serão recusados.">
      <?= csrf_field() ?><button class="btn btn-teal" type="submit">Aprovar pedido</button>
    </form>
    <form method="post" action="<?= e(url('admin/pedidos/' . $id . '/recusar')) ?>" style="flex:1 1 300px">
      <?= csrf_field() ?>
      <label for="motivo" style="margin-top:0">Motivo da recusa (opcional, mas recomendado)</label>
      <textarea id="motivo" name="motivo" maxlength="1000"></textarea>
      <p><button class="btn btn-danger" type="submit">Recusar pedido</button></p>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="panel">
  <h2>Privacidade</h2>
  <form method="post" action="<?= e(url('admin/pedidos/' . $id . '/excluir-dados')) ?>"
        data-confirm="Excluir definitivamente os dados deste solicitante, incluindo todos os pedidos e mensagens dele?">
    <?= csrf_field() ?><button class="btn btn-ghost" type="submit">Excluir dados do solicitante (LGPD)</button>
  </form>
</div>
