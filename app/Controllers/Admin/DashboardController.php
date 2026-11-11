<?php

namespace App\Controllers\Admin;

use App\Persistence\{AnimalPersistence, SolicitacaoPersistence};

class DashboardController extends AdminController
{
    /** @return SolicitacaoPersistence */
    protected function createPersistence() { return new SolicitacaoPersistence(); }

    /**
     * @return void
     */
    public function index()
    {
        $this->adminView('admin/dashboard', [
            'title' => 'Painel',
            'active' => 'painel',
            'byStatus' => (new AnimalPersistence())->countByStatus(),
            'pendingCount' => $this->Persistence->countPending(),
            'latest' => $this->Persistence->getRecentPending(5),
        ]);
    }
}
