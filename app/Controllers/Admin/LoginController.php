<?php

namespace App\Controllers\Admin;

use App\Core\{Auth, Controller, Csrf, Messages};
use App\Models\Administrador;
use App\Persistence\AdministradorPersistence;
use App\Utils\ControllerUtils;

class LoginController extends Controller
{
    /** @return AdministradorPersistence */
    protected function createPersistence() { return new AdministradorPersistence(); }

    /** @return Administrador */
    protected function createModel() { return new Administrador(); }

    /**
     * @return void
     */
    public function form()
    {
        if (Auth::getAdmin()) {
            $this->redirect('/admin');
        }
        $this->view('admin/login', ['title' => 'Entrar', 'active' => '', 'error' => '']);
    }

    /**
     * @return void
     */
    public function login()
    {
        $this->verifyCsrf();
        $Admin = $this->Persistence->authenticate(ControllerUtils::getPost('email'), isset($_POST['senha']) ? (string)$_POST['senha'] : '');
        if ($Admin) {
            Auth::login($Admin); // regenera o ID da sessão (RNF02)
            $this->redirect('/admin');
        }
        $this->view('admin/login', ['title' => 'Entrar', 'active' => '', 'error' => Messages::LOGIN_FAILED]);
    }

    /**
     * @return void
     */
    public function logout()
    {
        $this->verifyCsrf();
        Auth::logout();
        $this->redirect('/admin/login');
    }
}
