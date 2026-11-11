<?php

namespace App\Models;

use App\Core\Model;

class Cidade extends Model
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->nome . '/' . $this->uf;
    }
}
