<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\{Database, Dominio, Model};

class Solicitacao extends Model
{
    public const MOTIVO_AUTOMATICO = 'Outro pedido de adoção foi aprovado para este animal.';

    protected static string $tabela = 'solicitacoes_adocao';
    protected static array $colunas = [
        'animal_id', 'pessoa_id', 'tipo_moradia', 'tem_outros_animais', 'motivacao', 'termo_aceito_em', 'lgpd_aceito_em',
        'status', 'motivo_recusa', 'decidido_em', 'decidido_por',
    ];

    /* ---------- relacionamentos ---------- */
    public function animal(): ?Animal         { return $this->pertenceA(Animal::class, 'animal_id'); }
    public function pessoa(): ?Pessoa         { return $this->pertenceA(Pessoa::class, 'pessoa_id'); }
    public function decisor(): ?Administrador { return $this->pertenceA(Administrador::class, 'decidido_por'); }

    public function statusNome(): string  { return Dominio::nome('status_solicitacao', $this->status); }
    public function moradiaNome(): string { return Dominio::nome('tipos_moradia', $this->tipo_moradia); }
    public function pendente(): bool      { return $this->status === 'pendente'; }

    /* ---------- criação ---------- */

    public static function jaPendente(Animal $animal, Pessoa $pessoa): bool
    {
        return static::contar("animal_id = :a AND pessoa_id = :p AND status = 'pendente'", ['a' => $animal->id(), 'p' => $pessoa->id()]) > 0;
    }

    /** RN09: nasce Pendente. O aceite do termo e da LGPD fica registrado com data/hora. */
    public static function abrir(Animal $animal, Pessoa $pessoa, array $dados): static
    {
        $agora = date('Y-m-d H:i:sP');
        return (new static([
            'animal_id'          => $animal->id(),
            'pessoa_id'          => $pessoa->id(),
            'tipo_moradia'       => $dados['tipo_moradia'],
            'tem_outros_animais' => (bool)$dados['tem_outros_animais'],
            'motivacao'          => $dados['motivacao'],
            'termo_aceito_em'    => $agora,
            'lgpd_aceito_em'     => $agora,
        ]))->salvar();
    }

    /* ---------- decisão ---------- */

    /**
     * RN04/RN11/RN12: aprova este pedido, marca o animal como Adotado e recusa os demais pendentes.
     * @return static[] pedidos recusados automaticamente (para avisar por e-mail)
     */
    public function aprovar(Administrador $admin): array
    {
        return Database::transacao(function () use ($admin): array {
            $animal = $this->travar();
            if ($animal->status === 'adotado') {
                throw new \DomainException('Este animal já consta como adotado.');
            }
            $this->decidir('aprovado', $admin, null);
            $animal->status = 'adotado';
            $animal->salvar();

            $outras = static::onde("animal_id = :a AND status = 'pendente' AND id <> :id", ['a' => $animal->id(), 'id' => $this->id()]);
            foreach ($outras as $o) {
                $o->decidir('recusado', $admin, self::MOTIVO_AUTOMATICO);
            }
            return $outras;
        });
    }

    public function recusar(Administrador $admin, ?string $motivo): void
    {
        Database::transacao(function () use ($admin, $motivo): void {
            $this->travar();
            $this->decidir('recusado', $admin, $motivo ?: null);
        });
    }

    /** Bloqueia animal e pedido (nessa ordem, evita deadlock) e confere se ainda está pendente. */
    private function travar(): Animal
    {
        Database::valor('SELECT id FROM animais WHERE id = :id FOR UPDATE', ['id' => $this->animal_id]);
        $status = Database::valor('SELECT status FROM solicitacoes_adocao WHERE id = :id FOR UPDATE', ['id' => $this->id()]);
        if ($status !== 'pendente') {
            throw new \DomainException('Este pedido já foi decidido.');
        }
        return Animal::buscarOuFalhar((int)$this->animal_id);
    }

    private function decidir(string $status, Administrador $admin, ?string $motivo): void
    {
        $this->preencher([
            'status'        => $status,
            'decidido_em'   => date('Y-m-d H:i:sP'),
            'decidido_por'  => $admin->id(),
            'motivo_recusa' => $status === 'recusado' ? $motivo : null,
        ])->salvar();
    }

    /* ---------- consultas ---------- */

    /** RF17: filtro por status e por animal. Colunas extras: animal_nome, pessoa_nome. @return static[] */
    public static function listar(string $status, int $animalId): array
    {
        $where = ['TRUE'];
        $p = [];
        if (Dominio::valido('status_solicitacao', $status)) {
            $where[] = 's.status = :st';
            $p['st'] = $status;
        }
        if ($animalId > 0) {
            $where[] = 's.animal_id = :an';
            $p['an'] = $animalId;
        }
        return static::consultar(
            'SELECT s.*, a.nome AS animal_nome, p.nome AS pessoa_nome
               FROM solicitacoes_adocao s
               JOIN animais a ON a.id = s.animal_id
               JOIN pessoas p ON p.id = s.pessoa_id
              WHERE ' . implode(' AND ', $where) . "
              ORDER BY (s.status = 'pendente') DESC, s.criado_em DESC LIMIT 200",
            $p
        );
    }

    /** @return static[] */
    public static function pendentesRecentes(int $n = 5): array
    {
        return static::consultar(
            "SELECT s.*, a.nome AS animal_nome, p.nome AS pessoa_nome
               FROM solicitacoes_adocao s
               JOIN animais a ON a.id = s.animal_id
               JOIN pessoas p ON p.id = s.pessoa_id
              WHERE s.status = 'pendente' ORDER BY s.criado_em DESC LIMIT :n",
            ['n' => $n]
        );
    }

    public static function totalPendentes(): int
    {
        return static::contar("status = 'pendente'");
    }
}
