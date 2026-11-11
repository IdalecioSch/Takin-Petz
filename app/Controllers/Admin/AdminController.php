<?php

namespace App\Controllers\Admin;

use App\Core\{Auth, Controller};

/** Base do painel: RN14 — só administrador autenticado e ativo entra. */
abstract class AdminController extends Controller
{
    protected $Admin;

    /**
     * @return void
     */
    public function before()
    {
        $Admin = Auth::getAdmin();
        if (!$Admin) {
            $this->redirect('/admin/login');
        }
        $this->Admin = $Admin;
    }

    /**
     * @param string $template
     * @param array $data
     * @param int $status
     * @return void
     */
    protected function adminView($template, $data = [], $status = 200)
    {
        $this->view($template, $data + ['Admin' => $this->Admin], 'admin', $status);
    }
}
