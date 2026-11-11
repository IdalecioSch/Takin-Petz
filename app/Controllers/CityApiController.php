<?php

namespace App\Controllers;

use App\Core\{Controller, Domain};
use App\Models\Cidade;
use App\Persistence\CidadePersistence;
use App\Services\IbgeClient;
use App\Utils\ControllerUtils;

class CityApiController extends Controller
{
    /** @return CidadePersistence */
    protected function createPersistence() { return new CidadePersistence(); }

    /** @return Cidade */
    protected function createModel() { return new Cidade(); }

    /**
     * @param string $uf
     * @return void
     */
    public function index($uf)
    {
        $uf = strtoupper($uf);
        if (!Domain::isValid('estados', $uf)) {
            ControllerUtils::json([], 404);
            return;
        }
        // primeira vez que a UF é pedida: importa os municípios do IBGE e guarda no banco
        if ($this->Persistence->countByState($uf) === 0) {
            $this->Persistence->insertMany($uf, (new IbgeClient())->fetchCityNames($uf));
        }
        $cities = array_map(function (Cidade $Cidade) {
            return ['id' => $Cidade->getId(), 'name' => $Cidade->nome];
        }, $this->Persistence->getByState($uf));
        ControllerUtils::json($cities);
    }
}
