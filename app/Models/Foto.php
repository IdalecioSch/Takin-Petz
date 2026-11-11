<?php

namespace App\Models;

use App\Core\Model;

class Foto extends Model
{
    /**
     * @return string
     */
    public function getUrl()
    {
        return photo_url($this->arquivo);
    }
}
