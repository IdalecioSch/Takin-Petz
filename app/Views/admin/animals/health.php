<?php
$base = 'admin/animais/' . $Animal->getId() . '/saude';
$vetOptions = function () use ($veterinarians) {
    echo '<option value="">Não informado</option>';
    foreach ($veterinarians as $Vet) {
        echo '<option value="' . $Vet->getId() . '"' . selected(isset($_POST['veterinario_id']) ? $_POST['veterinario_id'] : '', $Vet->getId()) . '>' . e($Vet->nome) . '</option>';
    }
};
?>
<div class="row between">
  <h1>Saúde de <?= e($Animal->nome) ?></h1>
  <div class="row"><a class="btn btn-ghost btn-sm" href="<?= e(url('admin/animais/' . $Animal->getId() . '/editar')) ?>">Editar animal</a>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('admin/animais')) ?>">Voltar</a></div>
</div>
<?php partial('errors', ['errors' => $errors]); ?>

<!-- ============ Vacinas ============ -->
<section class="panel">
  <h2>💉 Vacinas</h2>
  <?php if (!$vaccinations): ?><p class="muted">Nenhuma vacina registrada.</p><?php else: ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Vacina</th><th>Aplicada em</th><th>Próxima dose</th><th>Veterinário</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($vaccinations as $Vaccination): ?>
      <tr><td><?= e($Vaccination->vacina_nome) ?><?= $Vaccination->observacoes ? '<br><span class="muted">' . e($Vaccination->observacoes) . '</span>' : '' ?></td>
          <td><?= e(date_br($Vaccination->aplicada_em, false)) ?></td>
          <td><?= e(date_br($Vaccination->proxima_dose_em, false)) ?></td>
          <td><?= e($Vaccination->veterinario_nome ?: '—') ?></td>
          <td><form method="post" class="inline" action="<?= e(url($base . '/vacinas/' . $Vaccination->getId() . '/excluir')) ?>" data-confirm="Remover este registro de vacina?">
            <?= csrf_field() ?><button class="link-btn danger" type="submit">Remover</button></form></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
  <details class="add"<?= $openForm === 'vaccination' ? ' open' : '' ?>>
    <summary>+ Registrar vacina</summary>
    <form method="post" action="<?= e(url($base . '/vacinas')) ?>">
      <?= csrf_field() ?>
      <div class="grid-2">
        <div><label for="vacina_id">Vacina</label>
          <select id="vacina_id" name="vacina_id" required><option value="">Selecione</option>
            <?php foreach ($vaccines as $Vaccine): ?>
              <option value="<?= $Vaccine->getId() ?>"<?= selected(isset($_POST['vacina_id']) ? $_POST['vacina_id'] : '', $Vaccine->getId()) ?>><?= e($Vaccine->nome) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div><label for="v_vet">Veterinário</label><select id="v_vet" name="veterinario_id"><?php $vetOptions(); ?></select></div>
        <div><label for="aplicada_em">Aplicada em</label><input type="date" id="aplicada_em" name="aplicada_em" value="<?= old('aplicada_em', \App\Utils\DateUtils::today()) ?>" required></div>
        <div><label for="proxima_dose_em">Próxima dose</label><input type="date" id="proxima_dose_em" name="proxima_dose_em" value="<?= old('proxima_dose_em') ?>"></div>
      </div>
      <label for="v_obs">Observações</label><input type="text" id="v_obs" name="observacoes" value="<?= old('observacoes') ?>">
      <p><button class="btn btn-sm" type="submit">Salvar vacina</button>
        <small class="muted">Falta a vacina na lista? <a href="<?= e(url('admin/vacinas/novo')) ?>">Cadastre aqui</a>.</small></p>
    </form>
  </details>
</section>

<!-- ============ Dieta ============ -->
<section class="panel">
  <h2>🍽️ Alimentação e dieta</h2>
  <?php if (!$diets): ?><p class="muted">Nenhuma dieta registrada.</p><?php else: ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Tipo</th><th>Detalhes</th><th>Refeições/dia</th><th>Período</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($diets as $Diet): ?>
      <tr><td><?= e($Diet->getFoodTypeLabel()) ?> <?= $Diet->isActive() ? '<span class="badge disponivel">atual</span>' : '' ?></td>
          <td><?= e($Diet->descricao) ?><?= $Diet->restricoes ? '<br><span class="muted">Restrições: ' . e($Diet->restricoes) . '</span>' : '' ?></td>
          <td><?= e($Diet->refeicoes_por_dia ?: '—') ?></td>
          <td><?= e(date_br($Diet->iniciada_em, false)) ?> → <?= $Diet->isActive() ? 'em andamento' : e(date_br($Diet->encerrada_em, false)) ?></td>
          <td><div class="row">
            <?php if ($Diet->isActive()): ?>
              <form method="post" class="inline" action="<?= e(url($base . '/dietas/' . $Diet->getId() . '/encerrar')) ?>" data-confirm="Encerrar esta dieta hoje?">
                <?= csrf_field() ?><button class="link-btn" type="submit">Encerrar</button></form>
            <?php endif; ?>
            <form method="post" class="inline" action="<?= e(url($base . '/dietas/' . $Diet->getId() . '/excluir')) ?>" data-confirm="Remover este registro de dieta?">
              <?= csrf_field() ?><button class="link-btn danger" type="submit">Remover</button></form>
          </div></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>
  <details class="add"<?= $openForm === 'diet' ? ' open' : '' ?>>
    <summary>+ Registrar dieta</summary>
    <form method="post" action="<?= e(url($base . '/dietas')) ?>">
      <?= csrf_field() ?>
      <div class="grid-2">
        <div><label for="tipo_alimentacao">Tipo de alimentação</label>
          <select id="tipo_alimentacao" name="tipo_alimentacao" required><option value="">Selecione</option>
            <?php foreach (domain('tipos_alimentacao') as $code => $text): ?>
              <option value="<?= e($code) ?>"<?= selected(isset($_POST['tipo_alimentacao']) ? $_POST['tipo_alimentacao'] : '', $code) ?>><?= e($text) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div><label for="refeicoes_por_dia">Refeições por dia</label><input type="number" min="1" max="12" id="refeicoes_por_dia" name="refeicoes_por_dia" value="<?= old('refeicoes_por_dia') ?>"></div>
        <div><label for="iniciada_em">Início</label><input type="date" id="iniciada_em" name="iniciada_em" value="<?= old('iniciada_em', \App\Utils\DateUtils::today()) ?>" required></div>
      </div>
      <label for="d_desc">Marca / quantidade</label><input type="text" id="d_desc" name="descricao" value="<?= old('descricao') ?>">
      <label for="d_restr">Restrições alimentares</label><input type="text" id="d_restr" name="restricoes" value="<?= old('restricoes') ?>">
      <p><button class="btn btn-sm" type="submit">Salvar dieta</button></p>
    </form>
  </details>
</section>

<!-- ============ Consultas ============ -->
<section class="panel">
  <h2>🩺 Histórico de consultas</h2>
  <?php if (!$consultations): ?><p class="muted">Nenhuma consulta registrada.</p><?php else: ?>
  <ul class="timeline">
    <?php foreach ($consultations as $Consultation): ?>
      <li>
        <strong><?= e(date_br($Consultation->consultada_em, false)) ?> · <?= e($Consultation->motivo) ?></strong>
        <?= $Consultation->veterinario_nome ? '<span class="muted"> com ' . e($Consultation->veterinario_nome) . '</span>' : '' ?>
        <?php if ($Consultation->sintomas_lista): ?><div class="chips" style="margin:6px 0"><?php foreach (explode(', ', $Consultation->sintomas_lista) as $symptom): ?><span class="chip"><?= e($symptom) ?></span><?php endforeach; ?></div><?php endif; ?>
        <?php if ($Consultation->diagnostico): ?><div><b>Diagnóstico:</b> <?= nl2br(e($Consultation->diagnostico)) ?></div><?php endif; ?>
        <?php if ($Consultation->tratamento): ?><div><b>Tratamento:</b> <?= nl2br(e($Consultation->tratamento)) ?></div><?php endif; ?>
        <?php if ($Consultation->observacoes): ?><div class="muted"><?= nl2br(e($Consultation->observacoes)) ?></div><?php endif; ?>
        <?php if ($Consultation->retorno_em): ?><div><b>Retorno:</b> <?= e(date_br($Consultation->retorno_em, false)) ?></div><?php endif; ?>
        <form method="post" class="inline" action="<?= e(url($base . '/consultas/' . $Consultation->getId() . '/excluir')) ?>" data-confirm="Remover esta consulta?">
          <?= csrf_field() ?><button class="link-btn danger" type="submit">Remover</button></form>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <details class="add"<?= $openForm === 'consultation' ? ' open' : '' ?>>
    <summary>+ Registrar consulta</summary>
    <form method="post" action="<?= e(url($base . '/consultas')) ?>">
      <?= csrf_field() ?>
      <div class="grid-2">
        <div><label for="consultada_em">Data</label><input type="date" id="consultada_em" name="consultada_em" value="<?= old('consultada_em', \App\Utils\DateUtils::today()) ?>" required></div>
        <div><label for="c_vet">Veterinário</label><select id="c_vet" name="veterinario_id"><?php $vetOptions(); ?></select></div>
        <div><label for="retorno_em">Retorno</label><input type="date" id="retorno_em" name="retorno_em" value="<?= old('retorno_em') ?>"></div>
      </div>
      <label for="motivo">Motivo da consulta</label><input type="text" id="motivo" name="motivo" value="<?= old('motivo') ?>" required maxlength="200">
      <label>O que o animal apresentava</label>
      <div class="symptoms">
        <?php $selectedSymptoms = isset($_POST['sintomas']) ? (array)$_POST['sintomas'] : []; ?>
        <?php foreach ($symptoms as $Symptom): ?>
          <label class="check"><input type="checkbox" name="sintomas[]" value="<?= $Symptom->getId() ?>"<?= checked(in_array((string)$Symptom->getId(), array_map('strval', $selectedSymptoms), true)) ?>> <?= e($Symptom->nome) ?></label>
        <?php endforeach; ?>
      </div>
      <small class="muted">Outro sintoma? <a href="<?= e(url('admin/sintomas/novo')) ?>">Cadastre aqui</a>.</small>
      <label for="diagnostico">Diagnóstico</label><textarea id="diagnostico" name="diagnostico"><?= old('diagnostico') ?></textarea>
      <label for="tratamento">Tratamento</label><textarea id="tratamento" name="tratamento"><?= old('tratamento') ?></textarea>
      <label for="c_obs">Observações</label><textarea id="c_obs" name="observacoes"><?= old('observacoes') ?></textarea>
      <p><button class="btn btn-sm" type="submit">Salvar consulta</button></p>
    </form>
  </details>
</section>
