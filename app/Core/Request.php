<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function capturar(): self
    {
        return new self();
    }

    public function metodo(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function ehPost(): bool
    {
        return $this->metodo() === 'POST';
    }

    public function caminho(): string
    {
        $p = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $base = rtrim((string)Config::get('base_path', ''), '/');
        if ($base !== '' && str_starts_with($p, $base)) {
            $p = substr($p, strlen($base));
        }
        return '/' . trim($p, '/');
    }

    /** Campo do formulário (POST) como texto sem espaços nas pontas. */
    public function texto(string $campo): string
    {
        $v = $_POST[$campo] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    /** Parâmetro da URL (GET) como texto. */
    public function consulta(string $campo): string
    {
        $v = $_GET[$campo] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    public function marcado(string $campo): bool
    {
        return !empty($_POST[$campo]);
    }

    /** @return int[] */
    public function inteiros(string $campo): array
    {
        return array_map('intval', array_filter((array)($_POST[$campo] ?? []), 'is_scalar'));
    }

    /** Normaliza $_FILES['campo'] (múltiplo) numa lista, ignorando campos vazios. */
    public function arquivos(string $campo): array
    {
        $f = $_FILES[$campo] ?? null;
        if (!$f || !is_array($f['name'])) {
            return [];
        }
        $lista = [];
        foreach ($f['name'] as $i => $nome) {
            if ($f['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $lista[] = ['name' => $nome, 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
        }
        return $lista;
    }
}
