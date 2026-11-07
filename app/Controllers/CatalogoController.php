<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Animal;

final class CatalogoController extends Controller
{
    public function index(): void
    {
        $filtros = [];
        foreach (['especie', 'porte', 'sexo', 'faixa_etaria'] as $campo) {
            $filtros[$campo] = $this->req->consulta($campo);
        }
        $this->view('catalogo/index', [
            'titulo'  => 'Animais para adoção',
            'ativo'   => 'animais',
            'filtros' => $filtros,
            'animais' => Animal::catalogo($filtros),
        ]);
    }

    public function mostrar(int $id): void
    {
        $animal = Animal::buscar($id);
        if (!$animal || !$animal->estaDisponivel()) { // RN03
            $this->view('catalogo/indisponivel', ['titulo' => 'Animal não encontrado', 'ativo' => 'animais'], 'site', 404);
            return;
        }
        $this->view('catalogo/mostrar', ['titulo' => $animal->nome, 'ativo' => 'animais', 'animal' => $animal]);
    }
}
