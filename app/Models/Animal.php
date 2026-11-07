<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\{Database, Dominio, Model};

class Animal extends Model
{
    protected static string $tabela = 'animais';
    protected static array $colunas = [
        'nome', 'especie', 'sexo', 'porte', 'faixa_etaria', 'raca_id', 'cor_id', 'descricao', 'temperamento',
        'castrado', 'vacinado', 'vermifugado', 'status', 'atualizado_em',
    ];

    private const SQL_FOTO = '(SELECT f.arquivo FROM fotos_animais f WHERE f.animal_id = a.id ORDER BY f.principal DESC, f.ordem, f.id LIMIT 1)';

    protected function antesDeSalvar(): void
    {
        if ($this->existe) {
            $this->atualizado_em = date('Y-m-d H:i:sP');
        }
    }

    /* ---------- rótulos (vêm das tabelas de domínio) ---------- */
    public function especieNome(): string { return Dominio::nome('especies', $this->especie); }
    public function sexoNome(): string    { return Dominio::nome('sexos', $this->sexo); }
    public function porteNome(): string   { return Dominio::nome('portes', $this->porte); }
    public function faixaNome(): string   { return Dominio::nome('faixas_etarias', $this->faixa_etaria); }
    public function statusNome(): string  { return Dominio::nome('status_animal', $this->status); }

    public function estaDisponivel(): bool
    {
        return $this->status === 'disponivel';
    }

    /* ---------- relacionamentos ---------- */
    public function raca(): ?Raca { return $this->pertenceA(Raca::class, 'raca_id'); }
    public function cor(): ?Cor   { return $this->pertenceA(Cor::class, 'cor_id'); }

    public function racaNome(): string { return (string)$this->raca()?->nome; }
    public function corNome(): string  { return (string)$this->cor()?->nome; }

    /** @return Foto[] principal primeiro */
    public function fotos(): array
    {
        return $this->temMuitos(Foto::class, 'animal_id', 'principal DESC, ordem, id');
    }

    public function fotoPrincipal(): ?string
    {
        return $this->foto ?? ($this->fotos()[0]->arquivo ?? null);
    }

    /** @return Solicitacao[] */
    public function solicitacoes(): array
    {
        return $this->temMuitos(Solicitacao::class, 'animal_id', 'criado_em DESC');
    }

    /* ---------- regras ---------- */

    /** Raça e cor chegam como texto no formulário; viram registros (ou reaproveitam os existentes). */
    public function definirRacaECor(?string $raca, ?string $cor): static
    {
        $this->raca_id = Raca::obter((string)$this->especie, $raca)?->id();
        $this->cor_id = Cor::obter($cor)?->id();
        return $this;
    }

    public function podeExcluir(): bool
    {
        return Solicitacao::contar('animal_id = :a', ['a' => $this->id()]) === 0;
    }

    /** RN05/RF16: só exclui animal sem pedidos. Retorna os arquivos de foto para o chamador apagar. */
    public function remover(): array
    {
        if (!$this->podeExcluir()) {
            throw new \DomainException($this->nome . ' tem pedidos de adoção e não pode ser excluído. Marque como Indisponível para ocultar.');
        }
        $arquivos = array_map(fn(Foto $f) => $f->arquivo, $this->fotos());
        $this->excluir();
        return $arquivos;
    }

    /**
     * Remove as fotos marcadas, anexa as novas (já gravadas em disco) e garante UMA foto principal.
     * Retorna os arquivos removidos para apagar do disco depois do commit.
     * @param int[]    $removerIds
     * @param string[] $novosArquivos
     */
    public function sincronizarFotos(array $removerIds, array $novosArquivos, int $principalId = 0): array
    {
        $this->esquecer();
        $removidos = [];
        foreach ($this->fotos() as $f) {
            if (in_array($f->id(), $removerIds, true)) {
                $removidos[] = $f->arquivo;
                $f->excluir();
            }
        }
        $ordem = (int)Database::valor('SELECT COALESCE(MAX(ordem), 0) FROM fotos_animais WHERE animal_id = :a', ['a' => $this->id()]);
        foreach ($novosArquivos as $arquivo) {
            (new Foto(['animal_id' => $this->id(), 'arquivo' => $arquivo, 'ordem' => ++$ordem, 'principal' => false]))->salvar();
        }
        $this->esquecer();

        $ids = array_map('intval', array_column(
            Database::todos('SELECT id FROM fotos_animais WHERE animal_id = :a ORDER BY ordem, id', ['a' => $this->id()]),
            'id'
        ));
        if ($ids) {
            $principal = in_array($principalId, $ids, true) ? $principalId : $ids[0];
            Database::executar('UPDATE fotos_animais SET principal = false WHERE animal_id = :a', ['a' => $this->id()]);
            Database::executar('UPDATE fotos_animais SET principal = true WHERE id = :f', ['f' => $principal]);
        }
        return $removidos;
    }

    /* ---------- consultas ---------- */

    /** RF02/RF03/RN03: só Disponíveis; filtros validados contra as tabelas de domínio. @return static[] */
    public static function catalogo(array $filtros): array
    {
        $where = ["a.status = 'disponivel'"];
        $p = [];
        $campos = ['especie' => 'especies', 'porte' => 'portes', 'sexo' => 'sexos', 'faixa_etaria' => 'faixas_etarias'];
        foreach ($campos as $campo => $tabela) {
            $v = $filtros[$campo] ?? '';
            if (is_string($v) && Dominio::valido($tabela, $v)) {
                $where[] = "a.$campo = :$campo";
                $p[$campo] = $v;
            }
        }
        return static::consultar(
            'SELECT a.*, ' . self::SQL_FOTO . ' AS foto FROM animais a WHERE ' . implode(' AND ', $where) . ' ORDER BY a.criado_em DESC LIMIT 100',
            $p
        );
    }

    /** @return static[] */
    public static function recentes(int $n = 4): array
    {
        return static::consultar(
            "SELECT a.*, " . self::SQL_FOTO . " AS foto FROM animais a WHERE a.status = 'disponivel' ORDER BY a.criado_em DESC LIMIT :n",
            ['n' => $n]
        );
    }

    /** RF13: lista do painel (busca por nome, filtro de status, contagem de pedidos). @return static[] */
    public static function paraAdmin(string $busca, string $status): array
    {
        $where = ['TRUE'];
        $p = [];
        if ($busca !== '') {
            $where[] = 'a.nome ILIKE :b';
            $p['b'] = '%' . $busca . '%';
        }
        if (Dominio::valido('status_animal', $status)) {
            $where[] = 'a.status = :s';
            $p['s'] = $status;
        }
        return static::consultar(
            'SELECT a.*, ' . self::SQL_FOTO . " AS foto,
                (SELECT count(*) FROM solicitacoes_adocao s WHERE s.animal_id = a.id) AS total_pedidos,
                (SELECT count(*) FROM solicitacoes_adocao s WHERE s.animal_id = a.id AND s.status = 'pendente') AS pedidos_pendentes
             FROM animais a WHERE " . implode(' AND ', $where) . ' ORDER BY a.criado_em DESC',
            $p
        );
    }

    /** @return array<string,int> status => quantidade (todos os status presentes) */
    public static function contagemPorStatus(): array
    {
        $r = array_fill_keys(array_keys(Dominio::opcoes('status_animal')), 0);
        foreach (Database::todos('SELECT status, count(*) AS n FROM animais GROUP BY status') as $l) {
            $r[$l['status']] = (int)$l['n'];
        }
        return $r;
    }
}
