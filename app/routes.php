<?php

use App\Controllers\{AdoptionController, CatalogController, CityApiController, ContactController, HomeController, PageController};
use App\Controllers\Admin\{AnimalController, AnimalHealthController, CorController, DashboardController, LoginController,
    RacaController, SintomaController, SolicitacaoController, VacinaController, VeterinarioController};
use App\Core\Router;

return function (Router $Router) {
    // ----- site público -----
    $Router->get('/', HomeController::class, 'index');
    $Router->get('/animais', CatalogController::class, 'index');
    $Router->get('/animais/{id}', CatalogController::class, 'show');
    $Router->get('/animais/{id}/adotar', AdoptionController::class, 'form');
    $Router->post('/animais/{id}/adotar', AdoptionController::class, 'submit');
    $Router->get('/animais/{id}/adotar/sucesso', AdoptionController::class, 'success');
    $Router->get('/sobre', PageController::class, 'about');
    $Router->get('/contato', ContactController::class, 'form');
    $Router->post('/contato', ContactController::class, 'submit');
    $Router->get('/api/cidades/{uf}', CityApiController::class, 'index');

    // ----- painel administrativo -----
    $Router->get('/admin/login', LoginController::class, 'form');
    $Router->post('/admin/login', LoginController::class, 'login');
    $Router->post('/admin/logout', LoginController::class, 'logout');
    $Router->get('/admin', DashboardController::class, 'index');

    $Router->get('/admin/animais', AnimalController::class, 'index');
    $Router->get('/admin/animais/novo', AnimalController::class, 'create');
    $Router->post('/admin/animais', AnimalController::class, 'store');
    $Router->get('/admin/animais/{id}/editar', AnimalController::class, 'edit');
    $Router->post('/admin/animais/{id}', AnimalController::class, 'update');
    $Router->post('/admin/animais/{id}/status', AnimalController::class, 'changeStatus');
    $Router->post('/admin/animais/{id}/excluir', AnimalController::class, 'destroy');

    $Router->get('/admin/animais/{id}/saude', AnimalHealthController::class, 'index');
    $Router->post('/admin/animais/{id}/saude/vacinas', AnimalHealthController::class, 'storeVaccination');
    $Router->post('/admin/animais/{id}/saude/vacinas/{recordId}/excluir', AnimalHealthController::class, 'deleteVaccination');
    $Router->post('/admin/animais/{id}/saude/dietas', AnimalHealthController::class, 'storeDiet');
    $Router->post('/admin/animais/{id}/saude/dietas/{recordId}/encerrar', AnimalHealthController::class, 'endDiet');
    $Router->post('/admin/animais/{id}/saude/dietas/{recordId}/excluir', AnimalHealthController::class, 'deleteDiet');
    $Router->post('/admin/animais/{id}/saude/consultas', AnimalHealthController::class, 'storeConsultation');
    $Router->post('/admin/animais/{id}/saude/consultas/{recordId}/excluir', AnimalHealthController::class, 'deleteConsultation');

    $Router->get('/admin/pedidos', SolicitacaoController::class, 'index');
    $Router->get('/admin/pedidos/{id}', SolicitacaoController::class, 'show');
    $Router->post('/admin/pedidos/{id}/aprovar', SolicitacaoController::class, 'approve');
    $Router->post('/admin/pedidos/{id}/recusar', SolicitacaoController::class, 'refuse');
    $Router->post('/admin/pedidos/{id}/excluir-dados', SolicitacaoController::class, 'deletePersonalData');

    // cadastros (CRUD genérico)
    $Router->crud('/admin/racas', RacaController::class);
    $Router->crud('/admin/cores', CorController::class);
    $Router->crud('/admin/veterinarios', VeterinarioController::class);
    $Router->crud('/admin/vacinas', VacinaController::class);
    $Router->crud('/admin/sintomas', SintomaController::class);
};
