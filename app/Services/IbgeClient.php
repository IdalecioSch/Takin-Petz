<?php

namespace App\Services;

use App\Core\Config;

/** Lista de municípios por UF vinda da API de localidades do IBGE. */
class IbgeClient
{
    /**
     * @param string $uf
     * @return string[]
     */
    public function fetchCityNames($uf)
    {
        $url = sprintf(Config::get('ibge.cities_url', 'https://servicodados.ibge.gov.br/api/v1/localidades/estados/%s/municipios'), rawurlencode($uf));
        $Context = stream_context_create(['http' => ['timeout' => 6, 'header' => "Accept: application/json\r\n"]]);
        $json = @file_get_contents($url, false, $Context);
        $data = $json === false ? null : json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }
        return array_values(array_filter(array_column($data, 'nome')));
    }
}
