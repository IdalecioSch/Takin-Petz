<?php

namespace App\Models;

use App\Core\{Domain, Model};

class Animal extends Model
{
    /** @return string */
    public function getSpeciesLabel() { return Domain::getLabel('especies', $this->especie); }

    /** @return string */
    public function getSexLabel() { return Domain::getLabel('sexos', $this->sexo); }

    /** @return string */
    public function getSizeLabel() { return Domain::getLabel('portes', $this->porte); }

    /** @return string */
    public function getAgeGroupLabel() { return Domain::getLabel('faixas_etarias', $this->faixa_etaria); }

    /** @return string */
    public function getStatusLabel() { return Domain::getLabel('status_animal', $this->status); }

    /** @return string */
    public function getBreedName() { return (string)$this->raca_nome; }

    /** @return string */
    public function getColorName() { return (string)$this->cor_nome; }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->status === 'disponivel';
    }

    /**
     * @return bool
     */
    public function hasRequests()
    {
        return (int)$this->total_pedidos > 0;
    }

    /**
     * @return Foto[]
     */
    public function getPhotos()
    {
        return $this->photos ?: [];
    }

    /**
     * @return string|null
     */
    public function getMainPhoto()
    {
        $photos = $this->getPhotos();
        return $this->foto ?: ($photos ? $photos[0]->arquivo : null);
    }
}
