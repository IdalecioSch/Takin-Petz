<?php

namespace App\Controllers\Admin;

use App\Core\{BusinessException, Messages};
use App\Models\Solicitacao;
use App\Persistence\{AnimalPersistence, PessoaPersistence, SolicitacaoPersistence};
use App\Services\Notifier;
use App\Utils\{ControllerUtils, StringUtils};

class SolicitacaoController extends AdminController
{
    /** @return SolicitacaoPersistence */
    protected function createPersistence() { return new SolicitacaoPersistence(); }

    /** @return Solicitacao */
    protected function createModel() { return new Solicitacao(); }

    /**
     * @return void
     */
    public function index()
    {
        $status = ControllerUtils::getQuery('status');
        $animalId = (int)ControllerUtils::getQuery('animal');
        $this->adminView('admin/requests/index', [
            'title' => 'Pedidos',
            'active' => 'pedidos',
            'status' => $status,
            'animalId' => $animalId,
            'requests' => $this->Persistence->getList($status, $animalId),
            'animals' => (new AnimalPersistence())->getAllModel([], [], 't.nome'),
        ]);
    }

    /**
     * @param int $id
     * @return void
     */
    public function show($id)
    {
        $Solicitacao = $this->Persistence->getModelByIdOrFail($id);
        $this->adminView('admin/requests/show', ['title' => 'Pedido #' . $id, 'active' => 'pedidos', 'Solicitacao' => $Solicitacao]);
    }

    /**
     * @param int $id
     * @return void
     */
    public function approve($id)
    {
        $this->verifyCsrf();
        $Solicitacao = $this->Persistence->getModelByIdOrFail($id);
        try {
            $refused = $this->Persistence->approve($Solicitacao, $this->Admin);
        } catch (BusinessException $Exception) {
            $this->flash('erro', $Exception->getMessage());
            $this->redirect('/admin/pedidos/' . $id);
        }
        $Notifier = new Notifier();
        $Notifier->statusChanged($Solicitacao);
        foreach ($refused as $Other) {
            $Notifier->statusChanged($Other);
        }
        $this->flash('ok', Messages::REQUEST_APPROVED);
        $this->redirect('/admin/pedidos/' . $id);
    }

    /**
     * @param int $id
     * @return void
     */
    public function refuse($id)
    {
        $this->verifyCsrf();
        $Solicitacao = $this->Persistence->getModelByIdOrFail($id);
        $reason = ControllerUtils::getPost('motivo');
        if (StringUtils::length($reason) > 1000) {
            $this->flash('erro', Messages::get(Messages::REFUSAL_REASON_TOO_LONG, 1000));
            $this->redirect('/admin/pedidos/' . $id);
        }
        try {
            $this->Persistence->refuse($Solicitacao, $this->Admin, $reason);
        } catch (BusinessException $Exception) {
            $this->flash('erro', $Exception->getMessage());
            $this->redirect('/admin/pedidos/' . $id);
        }
        (new Notifier())->statusChanged($Solicitacao);
        $this->flash('ok', Messages::REQUEST_REFUSED);
        $this->redirect('/admin/pedidos/' . $id);
    }

    /**
     * @param int $id
     * @return void
     */
    public function deletePersonalData($id)
    {
        $this->verifyCsrf();
        $Solicitacao = $this->Persistence->getModelByIdOrFail($id);
        $PessoaPersistence = new PessoaPersistence();
        $PessoaPersistence->deletePersonalData($PessoaPersistence->getModelByIdOrFail($Solicitacao->pessoa_id));
        $this->flash('ok', Messages::PERSONAL_DATA_DELETED);
        $this->redirect('/admin/pedidos');
    }
}
