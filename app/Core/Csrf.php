<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (!Session::get('csrf')) {
            Session::set('csrf', bin2hex(random_bytes(32)));
        }
        return (string)Session::get('csrf');
    }

    public static function campo(): string
    {
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    public static function verificar(): void
    {
        $enviado = $_POST['csrf'] ?? '';
        if (!is_string($enviado) || !hash_equals((string)Session::get('csrf', ''), $enviado)) {
            throw new HttpException(419, 'Sessão expirada ou requisição inválida. Volte e tente novamente.');
        }
    }
}
