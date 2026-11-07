<?php
declare(strict_types=1);

namespace App\Core;

final class HttpException extends \RuntimeException
{
    public function __construct(private int $status, string $mensagem = '')
    {
        parent::__construct($mensagem, $status);
    }

    public function status(): int
    {
        return $this->status;
    }
}
