<?php

namespace App\Models;

use App\Core\Model;

class Pessoa extends Model
{
    /**
     * @return string
     */
    public function getCityLabel()
    {
        return $this->cidade_nome ? $this->cidade_nome . '/' . $this->cidade_uf : '';
    }
}
