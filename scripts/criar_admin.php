<?php
// Uso: php scripts/criar_admin.php "Nome" email@exemplo.org   (a senha é pedida no terminal)
declare(strict_types=1);

use App\Core\Config;
use App\Models\Administrador;

if (PHP_SAPI !== 'cli') {
    exit("Execute apenas pela linha de comando.\n");
}
[, $nome, $email] = $argv + [null, null, null];
if (!$nome || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Uso: php scripts/criar_admin.php \"Nome\" email@exemplo.org\n");
}

define('ROOT', dirname(__DIR__));
require ROOT . '/app/autoload.php';
require ROOT . '/app/helpers.php';
Config::carregar(ROOT . '/config/config.php');

echo 'Senha (mín. 8 caracteres): ';
$windows = stripos(PHP_OS, 'WIN') === 0;
if (!$windows) {
    @system('stty -echo 2>/dev/null');
}
$senha = trim((string)fgets(STDIN));
if (!$windows) {
    @system('stty echo 2>/dev/null');
}
echo "\n";
if (strlen($senha) < 8) {
    exit("Senha muito curta.\n");
}

Administrador::criar($nome, $email, $senha);
echo "Administrador criado.\n";
