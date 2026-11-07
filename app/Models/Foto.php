<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Foto extends Model
{
    protected static string $tabela = 'fotos_animais';
    protected static array $colunas = ['animal_id', 'arquivo', 'principal', 'ordem'];
}
