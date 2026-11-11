<?php

namespace App\Core;

use App\Utils\PersistenciaUtils;

/**
 * Base de todas as Persistence: é aqui (e nas filhas) que ficam os SQLs.
 * Convenção: a tabela principal sempre usa o alias "t" nos SELECTs.
 */
abstract class Persistence
{
    /** @return string */
    abstract public function getTable();

    /** @return string */
    abstract public function getModelClass();

    /** @return string INSERT sem RETURNING (a base acrescenta) */
    abstract public function getSqlInsert();

    /** @return string UPDATE com WHERE id = :id */
    abstract public function getSqlUpdate();

    /**
     * @param Model $Model
     * @return array
     */
    abstract public function getParams(Model $Model);

    /** @return string */
    public function getPrimaryKey()
    {
        return 'id';
    }

    /** @return string */
    public function getDefaultOrderBy()
    {
        return 't.id';
    }

    /** @return string */
    public function getSqlGetAll()
    {
        return 'SELECT t.* FROM ' . $this->getTable() . ' t';
    }

    /** @return string */
    public function getSqlCount()
    {
        return 'SELECT count(*) FROM ' . $this->getTable() . ' t';
    }

    /** @return string */
    public function getSqlGetById()
    {
        return $this->getSqlGetAll() . ' WHERE t.' . $this->getPrimaryKey() . ' = :id';
    }

    /** @return string */
    public function getSqlDelete()
    {
        return 'DELETE FROM ' . $this->getTable() . ' WHERE ' . $this->getPrimaryKey() . ' = :id';
    }

    /**
     * @param array $row
     * @return Model
     */
    public function newModel($row = [])
    {
        $Class = $this->getModelClass();
        return new $Class($row);
    }

    /**
     * @param string|array $conditions
     * @param array $params
     * @param string|null $orderBy
     * @param int|null $limit
     * @return Model[]
     */
    public function getAllModel($conditions = [], $params = [], $orderBy = null, $limit = null)
    {
        $sql = $this->getSqlGetAll() . PersistenciaUtils::buildWhere($conditions)
            . ' ORDER BY ' . ($orderBy ?: $this->getDefaultOrderBy());
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        return array_map([$this, 'newModel'], Database::fetchAll($sql, $params));
    }

    /**
     * @param string|array $conditions
     * @param array $params
     * @return Model|null
     */
    public function getFirstModel($conditions = [], $params = [])
    {
        return ($models = $this->getAllModel($conditions, $params, null, 1)) ? $models[0] : null;
    }

    /**
     * @param int $id
     * @return Model|null
     */
    public function getModelById($id)
    {
        $row = Database::fetchOne($this->getSqlGetById(), ['id' => $id]);
        if (!$row) {
            return null;
        }
        $Model = $this->newModel($row);
        $this->afterRefresh($Model);
        return $Model;
    }

    /**
     * @param int $id
     * @return Model
     * @throws HttpException
     */
    public function getModelByIdOrFail($id)
    {
        $Model = $this->getModelById($id);
        if (!$Model) {
            throw new HttpException(404, Messages::ERROR_RECORD_NOT_FOUND);
        }
        return $Model;
    }

    /**
     * @param string|array $conditions
     * @param array $params
     * @return int
     */
    public function count($conditions = [], $params = [])
    {
        return (int)Database::fetchValue($this->getSqlCount() . PersistenciaUtils::buildWhere($conditions), $params);
    }

    /**
     * @param Model $Model
     * @return Model
     */
    public function save(Model $Model)
    {
        return $Model->isNew() ? $this->insert($Model) : $this->update($Model);
    }

    /**
     * @param Model $Model
     * @return Model
     */
    public function insert(Model $Model)
    {
        $id = Database::fetchValue($this->getSqlInsert() . ' RETURNING ' . $this->getPrimaryKey(), $this->getParams($Model));
        $Model->fill([$this->getPrimaryKey() => $id]);
        return $this->refresh($Model);
    }

    /**
     * @param Model $Model
     * @return Model
     */
    public function update(Model $Model)
    {
        Database::execute($this->getSqlUpdate(), $this->getParams($Model) + ['id' => $Model->getId()]);
        return $this->refresh($Model);
    }

    /**
     * @param Model $Model
     * @return void
     */
    public function delete(Model $Model)
    {
        Database::execute($this->getSqlDelete(), ['id' => $Model->getId()]);
    }

    /**
     * @param Model $Model
     * @return Model
     * @throws \RuntimeException
     */
    public function refresh(Model $Model)
    {
        $row = Database::fetchOne($this->getSqlGetById(), ['id' => $Model->getId()]);
        if (!$row) {
            throw new \RuntimeException('Registro não encontrado ao atualizar o model.');
        }
        $Model->fill($row);
        $this->afterRefresh($Model);
        return $Model;
    }

    /**
     * @param Model $Model
     * @return void
     */
    protected function afterRefresh(Model $Model)
    {
    }
}
