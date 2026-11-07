<?php
declare(strict_types=1);

use App\Core\{Config, Csrf, Dominio, Session, View};

// Funções curtas para uso nas views.
function config(string $caminho, mixed $padrao = null): mixed
{
    return Config::get($caminho, $padrao);
}

function e(mixed $s): string
{
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $caminho = ''): string
{
    return rtrim((string)Config::get('base_path', ''), '/') . '/' . ltrim($caminho, '/');
}

function old(string $campo, mixed $padrao = ''): string
{
    $v = $_POST[$campo] ?? $padrao;
    return e(is_scalar($v) ? $v : '');
}

function csrf_field(): string
{
    return Csrf::campo();
}

function foto_url(?string $arquivo): string
{
    return $arquivo ? url('uploads/' . rawurlencode($arquivo)) : url('img/sem-foto.svg');
}

function data_br(?string $ts): string
{
    return $ts ? date('d/m/Y H:i', strtotime($ts)) : '—';
}

function sim_nao(mixed $v): string
{
    return $v ? 'Sim' : 'Não';
}

function selecionado(mixed $atual, mixed $valor): string
{
    return (string)$atual === (string)$valor ? ' selected' : '';
}

function marcado(mixed $v): string
{
    return $v ? ' checked' : '';
}

function rotulo(string $tabela, ?string $codigo): string
{
    return Dominio::nome($tabela, $codigo);
}

function dominio(string $tabela): array
{
    return Dominio::opcoes($tabela);
}

function parcial(string $nome, array $dados = []): void
{
    echo View::renderizar('partials/' . $nome, $dados, null);
}

function flash_render(): string
{
    $html = '';
    foreach (Session::flashes() as $f) {
        $html .= '<div class="alert alert-' . e($f['tipo']) . '" role="alert">' . e($f['msg']) . '</div>';
    }
    return $html;
}
