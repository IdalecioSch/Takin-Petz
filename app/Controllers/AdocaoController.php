<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{Controller, Database, Dominio, HttpException, Validator};
use App\Models\{Animal, Cidade, Pessoa, Solicitacao};
use App\Services\Notificador;

final class AdocaoController extends Controller
{
    public function formulario(int $id): void
    {
        $animal = $this->animalParaAdocao($id);
        if ($animal) {
            $this->exibir($animal, []);
        }
    }

    public function enviar(int $id): void
    {
        $this->verificarCsrf();
        if ($this->req->texto('website') !== '') { // honeypot anti-bot
            $this->redirecionar('/');
        }
        $animal = $this->animalParaAdocao($id);
        if (!$animal) {
            return;
        }

        $r = $this->req;
        $d = [
            'nome'     => $r->texto('nome'),
            'email'    => strtolower($r->texto('email')),
            'telefone' => $r->texto('telefone'),
            'cidade'   => $r->texto('cidade'),
            'uf'       => $r->texto('uf'),
            'moradia'  => $r->texto('moradia'),
            'outros'   => $r->texto('outros_animais'),
            'motivacao' => $r->texto('motivacao'),
        ];
        $digitos = strlen(preg_replace('/\D/', '', $d['telefone']));

        // RF06 / RN07 / RN08 / RN21
        $v = (new Validator($d))
            ->regra(strlen($d['nome']) >= 5 && strlen($d['nome']) <= 160 && str_contains($d['nome'], ' '), 'Informe seu nome completo.')
            ->email($d['email'], 'Informe um e-mail válido.')
            ->regra($digitos >= 10 && $digitos <= 11, 'Informe um telefone válido com DDD.')
            ->regra($d['cidade'] !== '' && strlen($d['cidade']) <= 100, 'Informe sua cidade.')
            ->emDominio('estados', $d['uf'], 'Selecione o estado.')
            ->emDominio('tipos_moradia', $d['moradia'], 'Selecione o tipo de moradia.')
            ->regra(in_array($d['outros'], ['sim', 'nao'], true), 'Informe se você possui outros animais.')
            ->regra(strlen($d['motivacao']) >= 20, 'Conte um pouco mais sobre sua motivação (mínimo de 20 caracteres).')
            ->regra($r->marcado('maior_18'), 'É necessário declarar que você tem mais de 18 anos.')
            ->regra($r->marcado('termo'), 'É necessário aceitar o termo de responsabilidade.')
            ->regra($r->marcado('lgpd'), 'É necessário concordar com o uso dos seus dados para esta finalidade.');

        if ($v->falhou()) {
            $this->exibir($animal, $v->erros());
            return;
        }

        try {
            $solicitacao = Database::transacao(function () use ($animal, $d): Solicitacao {
                $pessoa = Pessoa::registrar($d['nome'], $d['email'], $d['telefone'], Cidade::obter($d['cidade'], $d['uf']));
                // RN10: aviso amigável (o índice único parcial garante mesmo em corrida)
                if (Solicitacao::jaPendente($animal, $pessoa)) {
                    throw new \DomainException('Você já enviou um pedido para este animal e ele ainda está em análise.');
                }
                return Solicitacao::abrir($animal, $pessoa, [
                    'tipo_moradia'       => $d['moradia'],
                    'tem_outros_animais' => $d['outros'] === 'sim',
                    'motivacao'          => $d['motivacao'],
                ]);
            });
        } catch (\DomainException $e) {
            $this->exibir($animal, [$e->getMessage()]);
            return;
        } catch (\PDOException $e) {
            if ($e->getCode() !== '23505') {
                throw $e;
            }
            $this->exibir($animal, ['Você já enviou um pedido para este animal e ele ainda está em análise.']);
            return;
        }

        // RN18/RN20: e-mails depois de salvar; falha de envio não desfaz o pedido
        (new Notificador())->pedidoRecebido($solicitacao);
        $this->redirecionar('/animais/' . $id . '/adotar/sucesso');
    }

    public function sucesso(int $id): void
    {
        $animal = Animal::buscarOuFalhar($id);
        $this->view('adocao/sucesso', ['titulo' => 'Pedido enviado', 'ativo' => 'animais', 'animal' => $animal]);
    }

    private function exibir(Animal $animal, array $erros): void
    {
        $this->view('adocao/formulario', ['titulo' => 'Pedido de adoção: ' . $animal->nome, 'ativo' => 'animais', 'animal' => $animal, 'erros' => $erros]);
    }

    /** RN03: só recebe pedido quem está Disponível. Devolve null após exibir a página de aviso. */
    private function animalParaAdocao(int $id): ?Animal
    {
        $animal = Animal::buscar($id) ?? throw new HttpException(404, 'Animal não encontrado.');
        if (!$animal->estaDisponivel()) {
            $this->view('catalogo/indisponivel', ['titulo' => 'Indisponível', 'ativo' => 'animais'], 'site', 404);
            return null;
        }
        return $animal;
    }
}
