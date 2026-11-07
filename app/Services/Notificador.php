<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Models\{MensagemContato, Solicitacao};

/** Monta e dispara os e-mails do sistema (RF23, RF24, RF25, RF10). */
final class Notificador
{
    public function __construct(private Mailer $mailer = new Mailer())
    {
    }

    /** RN18: confirmação ao solicitante + aviso à entidade. */
    public function pedidoRecebido(Solicitacao $s): void
    {
        $pessoa = $s->pessoa();
        $animal = $s->animal();

        $this->mailer->enviar($pessoa->email, 'Recebemos seu pedido de adoção - ' . $animal->nome, $this->corpo(
            'Recebemos seu pedido de adoção',
            '<p>Olá, ' . e($pessoa->nome) . '!</p>'
            . '<p>Recebemos seu pedido para adotar <strong>' . e($animal->nome) . '</strong>. '
            . 'Nossa equipe vai analisar e entrar em contato pelo e-mail ou telefone informado.</p>'
            . '<p>Obrigado por pensar em adotar!</p>'
        ));

        $this->mailer->enviar((string)Config::get('entidade.email'), 'Novo pedido de adoção - ' . $animal->nome, $this->corpo(
            'Novo pedido de adoção',
            '<p><strong>' . e($pessoa->nome) . '</strong> pediu para adotar <strong>' . e($animal->nome) . '</strong>.</p>'
            . '<p>Cidade: ' . e($pessoa->cidade()?->rotulo()) . '<br>Contato: ' . e($pessoa->email) . ' / ' . e($pessoa->telefone) . '</p>'
            . '<p>Acesse o painel administrativo para analisar o pedido #' . (int)$s->id() . '.</p>'
        ));
    }

    /** RN19: resultado (aprovado/recusado) ao solicitante. */
    public function statusAlterado(Solicitacao $s): void
    {
        $pessoa = $s->pessoa();
        $nomeAnimal = $s->animal()->nome;

        if ($s->status === 'aprovado') {
            $assunto = 'Pedido aprovado - ' . $nomeAnimal;
            $html = $this->corpo('Seu pedido foi aprovado!',
                '<p>Olá, ' . e($pessoa->nome) . '!</p>'
                . '<p>Temos uma ótima notícia: seu pedido para adotar <strong>' . e($nomeAnimal) . '</strong> foi <strong>aprovado</strong>.</p>'
                . '<p>Entraremos em contato para combinar os próximos passos.</p>');
        } else {
            $motivo = trim((string)$s->motivo_recusa);
            $assunto = 'Atualização do seu pedido - ' . $nomeAnimal;
            $html = $this->corpo('Sobre seu pedido de adoção',
                '<p>Olá, ' . e($pessoa->nome) . '.</p>'
                . '<p>Infelizmente não foi possível seguir com seu pedido para adotar <strong>' . e($nomeAnimal) . '</strong>.</p>'
                . ($motivo !== '' ? '<p>Motivo: ' . nl2br(e($motivo)) . '</p>' : '')
                . '<p>Agradecemos o interesse. Outros bichinhos esperam por um lar!</p>');
        }
        $this->mailer->enviar($pessoa->email, $assunto, $html);
    }

    /** RF10: mensagem do formulário de contato para a entidade. */
    public function contatoRecebido(MensagemContato $m): void
    {
        $p = $m->pessoa();
        $this->mailer->enviar((string)Config::get('entidade.email'), 'Contato pelo site - ' . $p->nome, $this->corpo(
            'Nova mensagem de contato',
            '<p><strong>' . e($p->nome) . '</strong> (' . e($p->email) . ') escreveu:</p><p>' . nl2br(e($m->mensagem)) . '</p>'
        ));
    }

    private function corpo(string $titulo, string $conteudo): string
    {
        return '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#222">'
            . '<h2 style="color:#b4532a">' . e($titulo) . '</h2>' . $conteudo
            . '<hr style="border:none;border-top:1px solid #ddd;margin:24px 0">'
            . '<p style="font-size:12px;color:#777">' . e(Config::get('entidade.nome')) . '</p></div>';
    }
}
