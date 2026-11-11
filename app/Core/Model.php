<?php

namespace App\Core;

/** Objeto de dados. Quem fala com o banco é a Persistence de cada model. */
abstract class Model
{
    protected $attributes = [];

    /**
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->fill($data);
    }

    /**
     * @param array $data
     * @return static
     */
    public function fill($data)
    {
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return isset($this->attributes['id']) ? (int)$this->attributes['id'] : null;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->getId() === null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}
