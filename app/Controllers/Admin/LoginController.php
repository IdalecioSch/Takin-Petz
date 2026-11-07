<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Auth, Controller};
use App\Models\Administrador;

final class LoginController extends Controller
{
    public function formulario(): void
    {
        if (Auth::admin()) {
            $this->redirecionar('/admin');
        }
        $this->view('admin/login', ['titulo' => 'Entrar', 'ativo' => '', 'erro' => '']);
    }

    public function entrar(): void
    {
        $this->verificarCsrf();
        $admin = Administrador::autenticar($this->req->texto('email'), (string)($_POST['senha'] ?? ''));
        if ($admin) {
            Auth::entrar($admin); // regenera o ID da sessão (RNF02)
            $this->redirecionar('/admin');
        }
        $this->view('admin/login', ['titulo' => 'Entrar', 'ativo' => '', 'erro' => 'E-mail ou senha incorretos.']);
    }

    public function sair(): void
    {
        $this->verificarCsrf();
        Auth::sair();
        $this->redirecionar('/admin/login');
    }
}
