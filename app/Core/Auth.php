<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Administrador;

final class Auth
{
    private static ?Administrador $admin = null;
    private static bool $carregado = false;

    /** Administrador logado (e ainda ativo) ou null. */
    public static function admin(): ?Administrador
    {
        if (!self::$carregado) {
            $id = Session::get('admin_id');
            $a = $id ? Administrador::buscar((int)$id) : null;
            self::$admin = ($a && $a->ativo) ? $a : null;
            self::$carregado = true;
        }
        return self::$admin;
    }

    public static function entrar(Administrador $a): void
    {
        Session::regenerar();
        Session::set('admin_id', $a->id());
        self::$admin = $a;
        self::$carregado = true;
    }

    public static function sair(): void
    {
        Session::destruir();
        self::$admin = null;
        self::$carregado = true;
    }
}
