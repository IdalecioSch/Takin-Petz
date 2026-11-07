<?php
declare(strict_types=1);

namespace App\Core;

final class Application
{
    public function executar(): void
    {
        $arquivo = ROOT . '/config/config.php';
        if (!is_file($arquivo)) {
            http_response_code(500);
            exit('Configuração ausente: copie config/config.example.php para config/config.php.');
        }
        Config::carregar($arquivo);
        date_default_timezone_set('America/Sao_Paulo');

        if (Config::get('debug')) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        }

        Session::iniciar();
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        try {
            $router = new Router();
            (require ROOT . '/app/rotas.php')($router);
            $router->despachar(Request::capturar());
        } catch (HttpException $e) {
            $this->erro($e->status(), $e->getMessage());
        } catch (\Throwable $e) {
            error_log('[erro] ' . $e);
            $this->erro(500, Config::get('debug') ? $e->getMessage() : 'Ocorreu um erro interno. Tente novamente em instantes.');
        }
    }

    private function erro(int $status, string $mensagem): void
    {
        http_response_code($status);
        echo View::renderizar('erro', ['titulo' => 'Erro ' . $status, 'status' => $status, 'mensagem' => $mensagem]);
    }
}
