<?php

namespace App\Services;

use App\Core\{Config, Messages};
use App\Models\{MensagemContato, Solicitacao};

class Notifier
{
    private $Mailer;

    /**
     */
    public function __construct()
    {
        $this->Mailer = new Mailer();
    }

    /**
     * @param Solicitacao $Solicitacao
     * @return void
     */
    public function requestReceived(Solicitacao $Solicitacao)
    {
        $name = e($Solicitacao->pessoa_nome);
        $animal = e($Solicitacao->animal_nome);

        $this->Mailer->send($Solicitacao->pessoa_email, Messages::get(Messages::EMAIL_REQUEST_RECEIVED_SUBJECT, $Solicitacao->animal_nome),
            $this->wrap(Messages::EMAIL_REQUEST_RECEIVED_TITLE,
                '<p>' . Messages::get(Messages::EMAIL_GREETING, $name) . '</p>'
                . '<p>' . Messages::get(Messages::EMAIL_REQUEST_RECEIVED_BODY, $animal) . '</p>'
                . '<p>' . Messages::EMAIL_REQUEST_RECEIVED_THANKS . '</p>'));

        $this->Mailer->send((string)Config::get('entidade.email'), Messages::get(Messages::EMAIL_NEW_REQUEST_SUBJECT, $Solicitacao->animal_nome),
            $this->wrap(Messages::EMAIL_NEW_REQUEST_TITLE,
                '<p>' . Messages::get(Messages::EMAIL_NEW_REQUEST_BODY, $name, $animal) . '</p>'
                . '<p>' . Messages::get(Messages::EMAIL_NEW_REQUEST_DETAILS, e($Solicitacao->getCityLabel()), e($Solicitacao->pessoa_email), e($Solicitacao->pessoa_telefone)) . '</p>'
                . '<p>' . Messages::get(Messages::EMAIL_NEW_REQUEST_PANEL, $Solicitacao->getId()) . '</p>'));
    }

    /**
     * @param Solicitacao $Solicitacao
     * @return void
     */
    public function statusChanged(Solicitacao $Solicitacao)
    {
        $name = e($Solicitacao->pessoa_nome);
        $animal = e($Solicitacao->animal_nome);

        if ($Solicitacao->status === 'aprovado') {
            $subject = Messages::get(Messages::EMAIL_APPROVED_SUBJECT, $Solicitacao->animal_nome);
            $html = $this->wrap(Messages::EMAIL_APPROVED_TITLE,
                '<p>' . Messages::get(Messages::EMAIL_GREETING, $name) . '</p>'
                . '<p>' . Messages::get(Messages::EMAIL_APPROVED_BODY, $animal) . '</p>'
                . '<p>' . Messages::EMAIL_APPROVED_NEXT . '</p>');
        } else {
            $reason = trim((string)$Solicitacao->motivo_recusa);
            $subject = Messages::get(Messages::EMAIL_REFUSED_SUBJECT, $Solicitacao->animal_nome);
            $html = $this->wrap(Messages::EMAIL_REFUSED_TITLE,
                '<p>' . Messages::get(Messages::EMAIL_GREETING, $name) . '</p>'
                . '<p>' . Messages::get(Messages::EMAIL_REFUSED_BODY, $animal) . '</p>'
                . ($reason !== '' ? '<p>' . Messages::get(Messages::EMAIL_REFUSED_REASON, nl2br(e($reason))) . '</p>' : '')
                . '<p>' . Messages::EMAIL_REFUSED_CLOSING . '</p>');
        }
        $this->Mailer->send($Solicitacao->pessoa_email, $subject, $html);
    }

    /**
     * @param MensagemContato $MensagemContato
     * @return void
     */
    public function contactReceived(MensagemContato $MensagemContato)
    {
        $this->Mailer->send((string)Config::get('entidade.email'), Messages::get(Messages::EMAIL_CONTACT_SUBJECT, $MensagemContato->pessoa_nome),
            $this->wrap(Messages::EMAIL_CONTACT_TITLE,
                '<p>' . Messages::get(Messages::EMAIL_CONTACT_BODY, e($MensagemContato->pessoa_nome), e($MensagemContato->pessoa_email)) . '</p>'
                . '<p>' . nl2br(e($MensagemContato->mensagem)) . '</p>'));
    }

    /**
     * @param string $title
     * @param string $content
     * @return string
     */
    private function wrap($title, $content)
    {
        return '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#1f2a44">'
            . '<h2 style="color:#e85f2a">' . e($title) . '</h2>' . $content
            . '<hr style="border:none;border-top:1px solid #eadfd2;margin:24px 0">'
            . '<p style="font-size:12px;color:#6b7488">' . e(Messages::SITE_NAME) . ' · ' . e(Config::get('entidade.nome')) . '</p></div>';
    }
}
