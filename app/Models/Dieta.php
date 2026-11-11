<?php

namespace App\Models;

use App\Core\{Domain, Model};

class Dieta extends Model
{
    /** @return string */
    public function getFoodTypeLabel() { return Domain::getLabel('tipos_alimentacao', $this->tipo_alimentacao); }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->encerrada_em === null;
    }
}
