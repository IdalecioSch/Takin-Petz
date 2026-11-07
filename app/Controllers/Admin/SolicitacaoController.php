<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\{Animal, Solicitacao};
use App\Services\Notificador;

final class SolicitacaoController extends AdminController
{
    public function index(): void
    {
        $status = $this->req->consulta('status');
        $animalId = (int)$this->req->consulta('animal');
        $this->viewAdmin('admin/pedidos/index', [
            'titulo' => 'Pedidos', 'ativo' => 'pedidos',
            'status' => $status, 'animalId' => $animalId,
            'pedidos' => Solicitacao::listar($status, $animalId),
            'animais' => Animal::onde('TRUE', [], 'nome'),
        ]);
    }

    public function mostrar(int $id): void
    {
        $s = Solicitacao::buscarOuFalhar($id);
        $this->viewAdmin('admin/pedidos/mostrar', ['titulo' => 'Pedido #' . $id, 'ativo' => 'pedidos', 's' => $s]);
    }

    /** RN04 / RN11 / RN12 / RN19 */
    public function aprovar(int $id): void
    {
        $this->verificarCsrf();
        $s = Solicitacao::buscarOuFalhar($id);
        try {
            $recusadas = $s->aprovar($this->admin);
        } catch (\DomainException $e) {
            $this->flash('erro', $e->getMessage());
            $this->redirecionar('/admin/pedidos/' . $id);
        }
        $n = new Notificador();
        $n->statusAlterado($s);
        foreach ($recusadas as $outra) {
            $n->statusAlterado($outra);
        }
        $this->flash('ok', 'Pedido aprovado. O animal foi marcado como Adotado e os demais pedidos pendentes foram recusados.');
        $this->redirecionar('/admin/pedidos/' . $id);
    }

    /** RN13 / RN19 */
    public function recusar(int $id): void
    {
        $this->verificarCsrf();
        $s = Solicitacao::buscarOuFalhar($id);
        $motivo = $this->req->texto('motivo');
        if (mb_strlen($motivo) > 1000) {
            $this->flash('erro', 'O motivo é muito longo (máx. 1000 caracteres).');
            $this->redirecionar('/admin/pedidos/' . $id);
        }
        try {
            $s->recusar($this->admin, $motivo);
        } catch (\DomainException $e) {
            $this->flash('erro', $e->getMessage());
            $this->redirecionar('/admin/pedidos/' . $id);
        }
        (new Notificador())->statusAlterado($s);
        $this->flash('ok', 'Pedido recusado.');
        $this->redirecionar('/admin/pedidos/' . $id);
    }

    /** RN22 (LGPD): apaga a pessoa, com todos os pedidos e mensagens dela. */
    public function excluirDados(int $id): void
    {
        $this->verificarCsrf();
        Solicitacao::buscarOuFalhar($id)->pessoa()->excluirDados();
        $this->flash('ok', 'Os dados do solicitante foram excluídos.');
        $this->redirecionar('/admin/pedidos');
    }
}
