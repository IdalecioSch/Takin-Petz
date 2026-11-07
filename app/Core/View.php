<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    /** Renderiza app/Views/$template.php dentro de app/Views/layouts/$layout.php (ou sem layout). */
    public static function renderizar(string $template, array $dados = [], ?string $layout = 'site'): string
    {
        $conteudo = self::capturar($template, $dados);
        if ($layout === null) {
            return $conteudo;
        }
        return self::capturar('layouts/' . $layout, $dados + ['conteudo' => $conteudo]);
    }

    private static function capturar(string $__template, array $__dados): string
    {
        extract($__dados, EXTR_SKIP);
        ob_start();
        try {
            require ROOT . '/app/Views/' . $__template . '.php';
            return (string)ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
