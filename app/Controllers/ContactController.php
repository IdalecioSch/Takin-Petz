<?php

namespace App\Controllers;

use App\Core\{Controller, Database, Messages, Validator};
use App\Models\MensagemContato;
use App\Persistence\{MensagemContatoPersistence, PessoaPersistence};
use App\Services\Notifier;
use App\Utils\{ControllerUtils, StringUtils};

class ContactController extends Controller
{
    /** @return MensagemContatoPersistence */
    protected function createPersistence() { return new MensagemContatoPersistence(); }

    /** @return MensagemContato */
    protected function createModel() { return new MensagemContato(); }

    /**
     * @return void
     */
    public function form()
    {
        $this->renderForm([]);
    }

    /**
     * @return void
     */
    public function submit()
    {
        $this->verifyCsrf();
        if (ControllerUtils::getPost('website') !== '') {
            $this->redirect('/');
        }
        $name = ControllerUtils::getPost('nome');
        $email = StringUtils::normalizeEmail(ControllerUtils::getPost('email'));
        $text = ControllerUtils::getPost('mensagem');

        $Validator = new Validator();
        $Validator
            ->rule($name !== '' && StringUtils::length($name) <= 160, Messages::V_NAME)
            ->email($email, Messages::V_EMAIL)
            ->length($text, 10, 3000, Messages::get(Messages::V_MESSAGE_LENGTH, 10, 3000))
            ->rule(ControllerUtils::isChecked('lgpd'), Messages::V_LGPD_CONTACT);
        if ($Validator->failed()) {
            $this->renderForm($Validator->getErrors());
            return;
        }

        $MensagemContato = Database::transaction(function () use ($name, $email, $text) {
            $Pessoa = (new PessoaPersistence())->register($name, $email);
            return $this->Persistence->register($Pessoa, $text);
        });
        (new Notifier())->contactReceived($MensagemContato);

        $this->flash('ok', Messages::CONTACT_SENT);
        $this->redirect('/contato');
    }

    /**
     * @param array $errors
     * @return void
     */
    private function renderForm($errors)
    {
        $this->view('contact/form', ['title' => 'Contato', 'active' => 'contato', 'errors' => $errors]);
    }
}
