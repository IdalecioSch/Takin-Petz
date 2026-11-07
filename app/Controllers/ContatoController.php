<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Controller, Database, Validator};
use App\Models\{MensagemContato, Pessoa};
use App\Services\Notificador;

final class ContatoController extends Controller
{
    public function formulario(): void
    {
        $this->exibir([]);
    }

    public function enviar(): void
    {
        $this->verificarCsrf();
        if ($this->req->texto('website') !== '') {
            $this->redirecionar('/');
        }
        $r = $this->req;
        $nome = $r->texto('nome');
        $email = mb_strtolower($r->texto('email'));
        $msg = $r->texto('mensagem');

        $v = (new Validator([]))
            ->regra($nome !== '' && mb_strlen($nome) <= 160, 'Informe seu nome.')
            ->email($email, 'Informe um e-mail válido.')
            ->tamanho($msg, 10, 3000, 'Escreva uma mensagem (entre 10 e 3000 caracteres).')
            ->regra($r->marcado('lgpd'), 'É necessário concordar com o uso dos seus dados para responder sua mensagem.');
        if ($v->falhou()) {
            $this->exibir($v->erros());
            return;
        }

        $mensagem = Database::transacao(fn() => MensagemContato::registrar(Pessoa::registrar($nome, $email), $msg));
        (new Notificador())->contatoRecebido($mensagem);

        $this->flash('ok', 'Mensagem enviada! Responderemos em breve.');
        $this->redirecionar('/contato');
    }

    private function exibir(array $erros): void
    {
        $this->view('contato/formulario', ['titulo' => 'Contato', 'ativo' => 'contato', 'erros' => $erros]);
    }
}
