<?php
declare(strict_types=1);

namespace App\Core;

/** Acumula mensagens de erro de validação. */
final class Validator
{
    private array $erros = [];

    public function regra(bool $ok, string $mensagem): static
    {
        if (!$ok) {
            $this->erros[] = $mensagem;
        }
        return $this;
    }

    public function email(string $valor, string $mensagem): static
    {
        return $this->regra(filter_var($valor, FILTER_VALIDATE_EMAIL) !== false && mb_strlen($valor) <= 160, $mensagem);
    }

    public function emDominio(string $tabela, string $valor, string $mensagem): static
    {
        return $this->regra(Dominio::valido($tabela, $valor), $mensagem);
    }

    public function tamanho(string $valor, int $min, int $max, string $mensagem): static
    {
        $n = mb_strlen($valor);
        return $this->regra($n >= $min && $n <= $max, $mensagem);
    }

    public function adicionar(array $erros): static
    {
        $this->erros = array_merge($this->erros, $erros);
        return $this;
    }

    public function falhou(): bool
    {
        return $this->erros !== [];
    }

    public function erros(): array
    {
        return $this->erros;
    }
}
