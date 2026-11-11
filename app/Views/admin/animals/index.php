<div class="row between"><h1>Animais</h1><a class="btn" href="<?= e(url('admin/animais/novo')) ?>">+ Cadastrar animal</a></div>

<form method="get" action="<?= e(url('admin/animais')) ?>" class="filters">
  <div><label for="q">Buscar por nome</label><input type="search" id="q" name="q" value="<?= e($search) ?>"></div>
  <div><label for="status">Status</label><select id="status" name="status"><option value="">Todos</option>
    <?php foreach (domain('status_animal') as $code => $text): ?>
      <option value="<?= e($code) ?>"<?= selected($status, $code) ?>><?= e($text) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div class="row"><button class="btn" type="submit">Filtrar</button></div>
</form>

<?php if (!$animals): ?>
  <p class="muted">Nenhum animal encontrado.</p>
<?php else: ?>
<div class="table-wrap"><table>
  <thead><tr><th>Foto</th><th>Nome</th><th>Espécie / raça</th><th>Status</th><th>Pedidos</th><th>Ações</th></tr></thead>
  <tbody>
  <?php foreach ($animals as $Animal): ?>
    <tr>
      <td><img class="thumb" src="<?= e(photo_url($Animal->foto)) ?>" alt=""></td>
      <td><strong><?= e($Animal->nome) ?></strong></td>
      <td><?= e($Animal->getSpeciesLabel()) ?><br><span class="muted"><?= e($Animal->getBreedName()) ?></span></td>
      <td>
        <form method="post" action="<?= e(url('admin/animais/' . $Animal->getId() . '/status')) ?>" class="row">
          <?= csrf_field() ?>
          <span class="badge <?= e($Animal->status) ?>"><?= e($Animal->getStatusLabel()) ?></span>
          <select name="status" style="width:auto" aria-label="Novo status de <?= e($Animal->nome) ?>">
            <?php foreach (domain('status_animal') as $code => $text): ?>
              <option value="<?= e($code) ?>"<?= selected($Animal->status, $code) ?>><?= e($text) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-ghost btn-sm" type="submit">Alterar</button>
        </form>
      </td>
      <td><?= (int)$Animal->total_pedidos ?><?= $Animal->pedidos_pendentes ? ' (' . (int)$Animal->pedidos_pendentes . ' pend.)' : '' ?></td>
      <td><div class="row">
        <a href="<?= e(url('admin/animais/' . $Animal->getId() . '/editar')) ?>">Editar</a>
        <a href="<?= e(url('admin/animais/' . $Animal->getId() . '/saude')) ?>">Saúde</a>
        <form method="post" class="inline" action="<?= e(url('admin/animais/' . $Animal->getId() . '/excluir')) ?>"
              data-confirm="Excluir <?= e($Animal->nome) ?>? Esta ação não pode ser desfeita.">
          <?= csrf_field() ?><button class="link-btn danger" type="submit">Excluir</button>
        </form>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
