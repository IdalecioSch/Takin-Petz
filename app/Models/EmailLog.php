<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class EmailLog extends Model
{
    protected static string $tabela = 'emails_enviados';
    protected static array $colunas = ['destinatario', 'assunto', 'sucesso', 'erro'];
}
