<?php

namespace App\Utils;

use App\Core\Model;

class ModelUtils
{
    /**
     * @param Model[] $models
     * @return int[]
     */
    public static function getIds($models)
    {
        return array_map(function (Model $Model) {
            return $Model->getId();
        }, $models);
    }

    /**
     * @param Model[] $models
     * @return Model[]
     */
    public static function indexById($models)
    {
        return array_combine(self::getIds($models), $models) ?: [];
    }

    /**
     * @param Model[] $models
     * @param string $attribute
     * @return array
     */
    public static function pluck($models, $attribute)
    {
        return array_map(function (Model $Model) use ($attribute) {
            return $Model->$attribute;
        }, $models);
    }

    /**
     * @param Model[] $models
     * @param int $id
     * @return Model|null
     */
    public static function findById($models, $id)
    {
        foreach ($models as $Model) {
            if ($Model->getId() === (int)$id) {
                return $Model;
            }
        }
        return null;
    }

    /**
     * @param Model[] $models
     * @param string $attribute
     * @return array
     */
    public static function groupBy($models, $attribute)
    {
        $groups = [];
        foreach ($models as $Model) {
            $groups[$Model->$attribute][] = $Model;
        }
        return $groups;
    }

    /**
     * @param Model $Model
     * @param array $data
     * @param array $allowed
     * @return Model
     */
    public static function fillOnly(Model $Model, $data, $allowed)
    {
        return $Model->fill(ArrayUtils::only($data, $allowed));
    }

    /**
     * @param Model[] $models
     * @return array
     */
    public static function toArrays($models)
    {
        return array_map(function (Model $Model) {
            return $Model->toArray();
        }, $models);
    }
}
