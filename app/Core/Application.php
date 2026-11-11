<?php

namespace App\Core;

class Application
{
    /**
     * @return void
     */
    public function run()
    {
        $file = ROOT . '/config/config.php';
        if (!is_file($file)) {
            http_response_code(500);
            exit(Messages::ERROR_CONFIG_MISSING);
        }
        Config::load($file);
        date_default_timezone_set('America/Sao_Paulo');

        if (Config::get('debug')) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        }

        Session::start();
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        try {
            $Router = new Router();
            $registerRoutes = require ROOT . '/app/routes.php';
            $registerRoutes($Router);
            $Router->dispatch();
        } catch (HttpException $Exception) {
            $this->renderError($Exception->getStatus(), $Exception->getMessage());
        } catch (\Throwable $Exception) {
            error_log('[erro] ' . $Exception);
            $this->renderError(500, Config::get('debug') ? $Exception->getMessage() : Messages::ERROR_INTERNAL);
        }
    }

    /**
     * @param int $status
     * @param string $message
     * @return void
     */
    private function renderError($status, $message)
    {
        http_response_code($status);
        echo View::render('error', ['title' => 'Erro ' . $status, 'status' => $status, 'message' => $message, 'active' => '']);
    }
}
