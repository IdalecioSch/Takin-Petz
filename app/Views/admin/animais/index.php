<div class="acoes" style="justify-content:space-between"><h1>Animais</h1>
  <a class="btn" href="<?= e(url('admin/animais/novo')) ?>">+ Cadastrar animal</a></div>

<form method="get" action="<?= e(url('admin/animais')) ?>" class="filtros">
  <div><label for="q">Buscar por nome</label><input type="search" id="q" name="q" value="<?= e($busca) ?>"></div>
  <div><label for="status">Status</label><select id="status" name="status"><option value="">Todos</option>
    <?php foreach (dominio('status_animal') as $cod => $nome): ?>
      <option value="<?= e($cod) ?>"<?= selecionado($status, $cod) ?>><?= e($nome) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div class="acoes"><button class="btn" type="submit">Filtrar</button></div>
</form>

<?php if (!$animais): ?>
  <p class="mut">Nenhum animal encontrado.</p>
<?php else: ?>
<div class="tabela-wrap"><table>
  <thead><tr><th>Foto</th><th>Nome</th><th>Espécie</th><th>Status</th><th>Pedidos</th><th>Ações</th></tr></thead>
  <tbody>
  <?php foreach ($animais as $a): ?>
    <tr>
      <td><img class="thumb" src="<?= e(foto_url($a->foto)) ?>" alt=""></td>
      <td><?= e($a->nome) ?></td>
      <td><?= e($a->especieNome()) ?></td>
      <td>
        <form method="post" action="<?= e(url('admin/animais/' . $a->id() . '/status')) ?>" class="acoes">
          <?= csrf_field() ?>
          <span class="badge <?= e($a->status) ?>"><?= e($a->statusNome()) ?></span>
          <label class="mut" style="margin:0;font-weight:400" for="st<?= $a->id() ?>">alterar para</label>
          <select id="st<?= $a->id() ?>" name="status" style="width:auto">
            <?php foreach (dominio('status_animal') as $cod => $nome): ?>
              <option value="<?= e($cod) ?>"<?= selecionado($a->status, $cod) ?>><?= e($nome) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn sec" type="submit">Alterar</button>
        </form>
      </td>
      <td><?= (int)$a->total_pedidos ?><?= $a->pedidos_pendentes ? ' (' . (int)$a->pedidos_pendentes . ' pend.)' : '' ?></td>
      <td class="acoes">
        <a href="<?= e(url('admin/animais/' . $a->id() . '/editar')) ?>">Editar</a>
        <form method="post" action="<?= e(url('admin/animais/' . $a->id() . '/excluir')) ?>" class="inline"
              onsubmit="return confirm('Excluir <?= e(addslashes($a->nome)) ?>? Esta ação não pode ser desfeita.')">
          <?= csrf_field() ?>
          <button class="link" type="submit">Excluir</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
