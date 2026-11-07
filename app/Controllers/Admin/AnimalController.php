<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Database, Dominio, Validator};
use App\Models\{Animal, Foto};
use App\Services\UploadFotos;

final class AnimalController extends AdminController
{
    public function index(): void
    {
        $busca = $this->req->consulta('q');
        $status = $this->req->consulta('status');
        $this->viewAdmin('admin/animais/index', [
            'titulo' => 'Animais', 'ativo' => 'animais',
            'busca' => $busca, 'status' => $status,
            'animais' => Animal::paraAdmin($busca, $status),
        ]);
    }

    public function criar(): void
    {
        $this->formulario(new Animal(['status' => 'disponivel']), []);
    }

    public function salvar(): void
    {
        $this->verificarCsrf();
        $animal = new Animal();
        $erros = $this->gravar($animal);
        if ($erros) {
            $this->formulario($animal, $erros);
            return;
        }
        $this->flash('ok', 'Animal salvo com sucesso.');
        $this->redirecionar('/admin/animais');
    }

    public function editar(int $id): void
    {
        $this->formulario(Animal::buscarOuFalhar($id), []);
    }

    public function atualizar(int $id): void
    {
        $this->verificarCsrf();
        $animal = Animal::buscarOuFalhar($id);
        $erros = $this->gravar($animal);
        if ($erros) {
            $this->formulario($animal, $erros);
            return;
        }
        $this->flash('ok', 'Animal salvo com sucesso.');
        $this->redirecionar('/admin/animais');
    }

    /** RF15 / RN06 */
    public function alterarStatus(int $id): void
    {
        $this->verificarCsrf();
        $animal = Animal::buscarOuFalhar($id);
        $novo = $this->req->texto('status');
        if (Dominio::valido('status_animal', $novo)) {
            $animal->status = $novo;
            $animal->salvar();
            $this->flash('ok', 'Status de ' . $animal->nome . ' alterado para ' . $animal->statusNome() . '.');
        }
        $this->redirecionar('/admin/animais');
    }

    /** RF16 / RN05 */
    public function excluir(int $id): void
    {
        $this->verificarCsrf();
        $animal = Animal::buscarOuFalhar($id);
        try {
            (new UploadFotos())->apagar($animal->remover());
            $this->flash('ok', $animal->nome . ' foi excluído.');
        } catch (\DomainException $e) {
            $this->flash('erro', $e->getMessage());
        }
        $this->redirecionar('/admin/animais');
    }

    /** Valida o formulário e grava animal + raça/cor + fotos. Retorna a lista de erros (vazia = sucesso). */
    private function gravar(Animal $animal): array
    {
        $r = $this->req;
        $d = $this->dadosDoFormulario();

        // RN01
        $v = (new Validator($d))
            ->regra($d['nome'] !== '' && mb_strlen($d['nome']) <= 80, 'Informe o nome (até 80 caracteres).')
            ->emDominio('especies', $d['especie'], 'Selecione a espécie.')
            ->emDominio('sexos', $d['sexo'], 'Selecione o sexo.')
            ->emDominio('portes', $d['porte'], 'Selecione o porte.')
            ->emDominio('faixas_etarias', $d['faixa_etaria'], 'Selecione a faixa de idade.')
            ->emDominio('status_animal', $d['status'], 'Status inválido.')
            ->regra(mb_strlen($d['raca']) <= 80 && mb_strlen($d['cor']) <= 60, 'Raça (até 80) ou cor (até 60) muito longa.');

        // Fotos: RN01 (mín. 1) e RN17 (máx. 5, 3 MB, JPG/PNG/WEBP)
        $upload = new UploadFotos();
        $novas = $r->arquivos('fotos');
        $idsAtuais = array_map(fn(Foto $f) => $f->id(), $animal->existe() ? $animal->fotos() : []);
        $remover = array_values(array_intersect($r->inteiros('remover'), $idsAtuais));
        $total = count($idsAtuais) - count($remover) + count($novas);
        $max = (int)\App\Core\Config::get('upload.max_fotos');
        $v->regra($total >= 1, 'O animal precisa ter pelo menos uma foto.')
          ->regra($total <= $max, "Máximo de $max fotos por animal.")
          ->adicionar($upload->validar($novas));
        if ($v->falhou()) {
            $animal->preencher($d + ['castrado' => $r->marcado('castrado'), 'vacinado' => $r->marcado('vacinado'), 'vermifugado' => $r->marcado('vermifugado')]);
            return $v->erros();
        }

        $salvos = [];
        try {
            $salvos = $upload->salvar($novas);
            $removidos = Database::transacao(function () use ($animal, $d, $r, $salvos, $remover): array {
                $animal->preencher([
                    'nome' => $d['nome'], 'especie' => $d['especie'], 'sexo' => $d['sexo'], 'porte' => $d['porte'],
                    'faixa_etaria' => $d['faixa_etaria'], 'status' => $d['status'],
                    'descricao' => $d['descricao'] ?: null, 'temperamento' => $d['temperamento'] ?: null,
                    'castrado' => $r->marcado('castrado'), 'vacinado' => $r->marcado('vacinado'), 'vermifugado' => $r->marcado('vermifugado'),
                ]);
                $animal->definirRacaECor($d['raca'], $d['cor'])->salvar();
                return $animal->sincronizarFotos($remover, $salvos, (int)($_POST['principal'] ?? 0));
            });
        } catch (\Throwable $e) {
            $upload->apagar($salvos);
            error_log('[animal] ' . $e->getMessage());
            return ['Não foi possível salvar. ' . (\App\Core\Config::get('debug') ? $e->getMessage() : 'Tente novamente.')];
        }
        $upload->apagar($removidos); // só depois do commit
        return [];
    }

    private function dadosDoFormulario(): array
    {
        $r = $this->req;
        return [
            'nome' => $r->texto('nome'), 'especie' => $r->texto('especie'), 'sexo' => $r->texto('sexo'),
            'porte' => $r->texto('porte'), 'faixa_etaria' => $r->texto('faixa_etaria'),
            'raca' => $r->texto('raca'), 'cor' => $r->texto('cor'),
            'descricao' => $r->texto('descricao'), 'temperamento' => $r->texto('temperamento'),
            'status' => $r->texto('status') ?: 'disponivel',
        ];
    }

    private function formulario(Animal $animal, array $erros): void
    {
        $post = $this->req->ehPost();
        $valores = [
            'nome' => $animal->nome, 'especie' => $animal->especie, 'sexo' => $animal->sexo, 'porte' => $animal->porte,
            'faixa_etaria' => $animal->faixa_etaria, 'status' => $animal->status ?? 'disponivel',
            'raca' => $post ? $this->req->texto('raca') : ($animal->existe() ? $animal->racaNome() : ''),
            'cor' => $post ? $this->req->texto('cor') : ($animal->existe() ? $animal->corNome() : ''),
            'descricao' => $animal->descricao, 'temperamento' => $animal->temperamento,
            'castrado' => $animal->castrado, 'vacinado' => $animal->vacinado, 'vermifugado' => $animal->vermifugado,
        ];
        $titulo = $animal->existe() ? 'Editar ' . $animal->nome : 'Cadastrar animal';
        $this->viewAdmin('admin/animais/formulario', [
            'titulo' => $titulo, 'ativo' => 'animais', 'animal' => $animal, 'v' => $valores, 'erros' => $erros,
            'fotos' => $animal->existe() ? $animal->fotos() : [],
        ]);
    }
}
