<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Auth, Controller};
use App\Models\Administrador;

/** Base do painel: RN14 — só administrador autenticado e ativo entra. */
abstract class AdminController extends Controller
{
    protected Administrador $admin;

    public function antes(): void
    {
        $admin = Auth::admin();
        if (!$admin) {
            $this->redirecionar('/admin/login');
        }
        $this->admin = $admin;
    }

    protected function viewAdmin(string $template, array $dados = []): void
    {
        $this->view($template, $dados + ['admin' => $this->admin], 'admin');
    }
}
