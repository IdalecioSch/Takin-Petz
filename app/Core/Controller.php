<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    public function __construct(protected Request $req)
    {
    }

    /** Executado pelo Router antes da ação (útil para exigir login). */
    public function antes(): void
    {
    }

    protected function view(string $template, array $dados = [], string $layout = 'site', int $status = 200): void
    {
        http_response_code($status);
        echo View::renderizar($template, $dados, $layout);
    }

    protected function redirecionar(string $caminho): never
    {
        header('Location: ' . url($caminho));
        exit;
    }

    protected function flash(string $tipo, string $msg): void
    {
        Session::flash($tipo, $msg);
    }

    protected function verificarCsrf(): void
    {
        Csrf::verificar();
    }
}
