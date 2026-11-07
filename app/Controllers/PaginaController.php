<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class PaginaController extends Controller
{
    public function sobre(): void
    {
        $this->view('pagina/sobre', ['titulo' => 'Sobre', 'ativo' => 'sobre']);
    }
}
