<?php

// Servidor embutido do PHP: deixa arquivos estáticos (css, js, img, uploads) passarem direto.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . rawurldecode((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (is_file($file)) {
        return false;
    }
}

define('ROOT', dirname(__DIR__));
require ROOT . '/app/autoload.php';
require ROOT . '/app/helpers.php';

(new App\Core\Application())->run();
