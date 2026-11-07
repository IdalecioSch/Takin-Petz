<?php
declare(strict_types=1);

use App\Controllers\{AdocaoController, CatalogoController, ContatoController, HomeController, PaginaController};
use App\Controllers\Admin\{AnimalController, LoginController, PainelController, SolicitacaoController};
use App\Core\Router;

return function (Router $r): void {
    // ----- site público -----
    $r->get('/', HomeController::class, 'index');
    $r->get('/animais', CatalogoController::class, 'index');
    $r->get('/animais/{id}', CatalogoController::class, 'mostrar');
    $r->get('/animais/{id}/adotar', AdocaoController::class, 'formulario');
    $r->post('/animais/{id}/adotar', AdocaoController::class, 'enviar');
    $r->get('/animais/{id}/adotar/sucesso', AdocaoController::class, 'sucesso');
    $r->get('/sobre', PaginaController::class, 'sobre');
    $r->get('/contato', ContatoController::class, 'formulario');
    $r->post('/contato', ContatoController::class, 'enviar');

    // ----- painel administrativo -----
    $r->get('/admin/login', LoginController::class, 'formulario');
    $r->post('/admin/login', LoginController::class, 'entrar');
    $r->post('/admin/logout', LoginController::class, 'sair');

    $r->get('/admin', PainelController::class, 'index');

    $r->get('/admin/animais', AnimalController::class, 'index');
    $r->get('/admin/animais/novo', AnimalController::class, 'criar');
    $r->post('/admin/animais', AnimalController::class, 'salvar');
    $r->get('/admin/animais/{id}/editar', AnimalController::class, 'editar');
    $r->post('/admin/animais/{id}', AnimalController::class, 'atualizar');
    $r->post('/admin/animais/{id}/status', AnimalController::class, 'alterarStatus');
    $r->post('/admin/animais/{id}/excluir', AnimalController::class, 'excluir');

    $r->get('/admin/pedidos', SolicitacaoController::class, 'index');
    $r->get('/admin/pedidos/{id}', SolicitacaoController::class, 'mostrar');
    $r->post('/admin/pedidos/{id}/aprovar', SolicitacaoController::class, 'aprovar');
    $r->post('/admin/pedidos/{id}/recusar', SolicitacaoController::class, 'recusar');
    $r->post('/admin/pedidos/{id}/excluir-dados', SolicitacaoController::class, 'excluirDados');
};
