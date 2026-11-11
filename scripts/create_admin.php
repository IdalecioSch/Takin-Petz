<?php
// Uso: php scripts/create_admin.php "Nome" email@exemplo.org   (a senha é pedida no terminal)

use App\Core\Config;
use App\Persistence\AdministradorPersistence;

if (PHP_SAPI !== 'cli') {
    exit("Execute apenas pela linha de comando.\n");
}
$name = isset($argv[1]) ? $argv[1] : null;
$email = isset($argv[2]) ? $argv[2] : null;
if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Uso: php scripts/create_admin.php \"Nome\" email@exemplo.org\n");
}

define('ROOT', dirname(__DIR__));
require ROOT . '/app/autoload.php';
require ROOT . '/app/helpers.php';
Config::load(ROOT . '/config/config.php');

echo 'Senha (mín. 8 caracteres): ';
$windows = stripos(PHP_OS, 'WIN') === 0;
if (!$windows) {
    @system('stty -echo 2>/dev/null');
}
$password = trim((string)fgets(STDIN));
if (!$windows) {
    @system('stty echo 2>/dev/null');
}
echo "\n";
if (strlen($password) < 8) {
    exit("Senha muito curta.\n");
}

(new AdministradorPersistence())->create($name, $email, $password);
echo "Administrador criado.\n";
