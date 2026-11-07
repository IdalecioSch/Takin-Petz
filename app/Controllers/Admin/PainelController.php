<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\{Animal, Solicitacao};

final class PainelController extends AdminController
{
    public function index(): void
    {
        $this->viewAdmin('admin/painel', [
            'titulo'     => 'Painel',
            'ativo'      => 'painel',
            'porStatus'  => Animal::contagemPorStatus(),
            'pendentes'  => Solicitacao::totalPendentes(),
            'ultimos'    => Solicitacao::pendentesRecentes(5),
        ]);
    }
}
