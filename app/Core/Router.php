<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int,array{metodo:string,regex:string,controller:string,acao:string}> */
    private array $rotas = [];

    public function get(string $padrao, string $controller, string $acao): void
    {
        $this->adicionar('GET', $padrao, $controller, $acao);
    }

    public function post(string $padrao, string $controller, string $acao): void
    {
        $this->adicionar('POST', $padrao, $controller, $acao);
    }

    private function adicionar(string $metodo, string $padrao, string $controller, string $acao): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $padrao) . '$#';
        $this->rotas[] = compact('metodo', 'regex', 'controller', 'acao');
    }

    public function despachar(Request $req): void
    {
        $caminho = $req->caminho();
        foreach ($this->rotas as $r) {
            if ($r['metodo'] !== $req->metodo() || !preg_match($r['regex'], $caminho, $m)) {
                continue;
            }
            $params = [];
            foreach ($m as $nome => $valor) {
                if (is_string($nome)) {
                    $params[] = ctype_digit($valor) ? (int)$valor : $valor;
                }
            }
            $controller = new $r['controller']($req);
            $controller->antes();
            $controller->{$r['acao']}(...$params);
            return;
        }
        throw new HttpException(404, 'Página não encontrada.');
    }
}
