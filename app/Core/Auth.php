<?php

namespace App\Core;

use App\Models\Administrador;
use App\Persistence\AdministradorPersistence;

class Auth
{
    private static $Admin;
    private static $loaded = false;

    /**
     * @return Administrador|null
     */
    public static function getAdmin()
    {
        if (!self::$loaded) {
            $id = Session::get('admin_id');
            $Admin = $id ? (new AdministradorPersistence())->getModelById((int)$id) : null;
            self::$Admin = ($Admin && $Admin->ativo) ? $Admin : null;
            self::$loaded = true;
        }
        return self::$Admin;
    }

    /**
     * @param Administrador $Admin
     * @return void
     */
    public static function login(Administrador $Admin)
    {
        Session::regenerate();
        Session::set('admin_id', $Admin->getId());
        self::$Admin = $Admin;
        self::$loaded = true;
    }

    /**
     * @return void
     */
    public static function logout()
    {
        Session::destroy();
        self::$Admin = null;
        self::$loaded = true;
    }
}
