<?php

namespace App\Models;

use App\Core\{Domain, Model};

class Solicitacao extends Model
{
    /** @return string */
    public function getStatusLabel() { return Domain::getLabel('status_solicitacao', $this->status); }

    /** @return string */
    public function getHousingLabel() { return Domain::getLabel('tipos_moradia', $this->tipo_moradia); }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'pendente';
    }

    /**
     * @return string
     */
    public function getCityLabel()
    {
        return $this->cidade_nome ? $this->cidade_nome . '/' . $this->cidade_uf : '';
    }
}
