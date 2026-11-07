<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Active Record: cada model representa uma tabela e sabe se gravar/ler/excluir sozinho.
 *
 *   $animal = Animal::buscar(3);
 *   $animal->nome = 'Rex';
 *   $animal->salvar();            // UPDATE só das colunas alteradas
 *   (new Animal([...]))->salvar(); // INSERT ... RETURNING *
 */
abstract class Model
{
    protected static string $tabela;
    protected static string $chave = 'id';
    /** Colunas que podem ser gravadas (a chave primária fica de fora). */
    protected static array $colunas = [];

    protected array $atributos = [];
    protected array $originais = [];
    protected bool $existe = false;
    private array $memo = [];

    public function __construct(array $atributos = [])
    {
        $this->preencher($atributos);
    }

    /* ---------- atributos ---------- */

    public function preencher(array $dados): static
    {
        foreach ($dados as $k => $v) {
            $this->atributos[$k] = $v;
        }
        return $this;
    }

    public function __get(string $k): mixed
    {
        return $this->atributos[$k] ?? null;
    }

    public function __set(string $k, mixed $v): void
    {
        $this->atributos[$k] = $v;
    }

    public function __isset(string $k): bool
    {
        return isset($this->atributos[$k]);
    }

    public function id(): ?int
    {
        $v = $this->atributos[static::$chave] ?? null;
        return $v === null ? null : (int)$v;
    }

    public function existe(): bool
    {
        return $this->existe;
    }

    public function paraArray(): array
    {
        return $this->atributos;
    }

    public static function hidratar(array $linha): static
    {
        $m = new static($linha);
        $m->originais = $linha;
        $m->existe = true;
        return $m;
    }

    /* ---------- consultas ---------- */

    public static function buscar(int|string $id): ?static
    {
        $l = Database::um('SELECT * FROM ' . static::$tabela . ' WHERE ' . static::$chave . ' = :id', ['id' => $id]);
        return $l ? static::hidratar($l) : null;
    }

    public static function buscarOuFalhar(int|string $id): static
    {
        return static::buscar($id) ?? throw new HttpException(404, 'Registro não encontrado.');
    }

    /** @return static[] */
    public static function onde(string $where = 'TRUE', array $params = [], string $ordem = '', ?int $limite = null): array
    {
        $sql = 'SELECT * FROM ' . static::$tabela . ' WHERE ' . $where
             . ($ordem !== '' ? ' ORDER BY ' . $ordem : '')
             . ($limite !== null ? ' LIMIT ' . $limite : '');
        return static::consultar($sql, $params);
    }

    public static function primeiro(string $where, array $params = []): ?static
    {
        return static::onde($where, $params, '', 1)[0] ?? null;
    }

    public static function contar(string $where = 'TRUE', array $params = []): int
    {
        return (int)Database::valor('SELECT count(*) FROM ' . static::$tabela . ' WHERE ' . $where, $params);
    }

    /** SQL livre que devolve linhas hidratadas como models (colunas extras viram atributos). @return static[] */
    public static function consultar(string $sql, array $params = []): array
    {
        return array_map(fn(array $l) => static::hidratar($l), Database::todos($sql, $params));
    }

    /** Busca por critério; se não existir, cria com $dados. */
    protected static function obterOuCriar(string $where, array $params, array $dados): static
    {
        return static::primeiro($where, $params) ?? (new static($dados))->salvar();
    }

    /* ---------- persistência ---------- */

    public function salvar(): static
    {
        $this->antesDeSalvar();

        if ($this->existe) {
            $alteradas = [];
            foreach (static::$colunas as $c) {
                if (array_key_exists($c, $this->atributos)
                    && (!array_key_exists($c, $this->originais) || $this->atributos[$c] !== $this->originais[$c])) {
                    $alteradas[$c] = $this->atributos[$c];
                }
            }
            if (!$alteradas) {
                return $this;
            }
            $sets = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($alteradas)));
            $alteradas['__id'] = $this->id();
            $linha = Database::um('UPDATE ' . static::$tabela . " SET $sets WHERE " . static::$chave . ' = :__id RETURNING *', $alteradas);
        } else {
            $dados = array_intersect_key($this->atributos, array_flip(static::$colunas));
            if ($dados) {
                $cols = implode(', ', array_keys($dados));
                $vals = implode(', ', array_map(fn($c) => ":$c", array_keys($dados)));
                $linha = Database::um('INSERT INTO ' . static::$tabela . " ($cols) VALUES ($vals) RETURNING *", $dados);
            } else {
                $linha = Database::um('INSERT INTO ' . static::$tabela . ' DEFAULT VALUES RETURNING *');
            }
        }

        $this->atributos = array_merge($this->atributos, $linha);
        $this->originais = array_merge($this->originais, $linha);
        $this->existe = true;
        $this->memo = [];
        return $this;
    }

    public function excluir(): void
    {
        Database::executar('DELETE FROM ' . static::$tabela . ' WHERE ' . static::$chave . ' = :id', ['id' => $this->id()]);
        $this->existe = false;
    }

    /** Gancho chamado antes de INSERT/UPDATE. */
    protected function antesDeSalvar(): void
    {
    }

    /* ---------- relacionamentos ---------- */

    /** @template T of Model @param class-string<T> $classe @return ?T */
    protected function pertenceA(string $classe, string $fk): ?Model
    {
        return $this->memorizar($classe . ':' . $fk, fn() => $this->$fk === null ? null : $classe::buscar($this->$fk));
    }

    /** @return Model[] */
    protected function temMuitos(string $classe, string $fk, string $ordem = ''): array
    {
        return $this->memorizar($classe . '*' . $fk, fn() => $classe::onde("$fk = :v", ['v' => $this->id()], $ordem));
    }

    protected function memorizar(string $chave, callable $fn): mixed
    {
        if (!array_key_exists($chave, $this->memo)) {
            $this->memo[$chave] = $fn();
        }
        return $this->memo[$chave];
    }

    protected function esquecer(): void
    {
        $this->memo = [];
    }
}
