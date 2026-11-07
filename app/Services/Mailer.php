<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Models\EmailLog;

final class Mailer
{
    /**
     * Envia e registra em emails_enviados. Nunca lança exceção (RN20):
     * se falhar, o fluxo principal continua e a falha fica no log.
     */
    public function enviar(string $para, string $assunto, string $html): bool
    {
        $para = trim(str_replace(["\r", "\n"], '', $para));
        $erro = null;
        try {
            if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Destinatário inválido');
            }
            $texto = trim(html_entity_decode(strip_tags(preg_replace('/<br\s*\/?>|<\/p>/i', "\n", $html)), ENT_QUOTES, 'UTF-8'));
            (new Smtp((array)Config::get('smtp')))->enviar($para, $assunto, $html, $texto);
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
            error_log('[email] ' . $para . ' - ' . $erro);
        }

        try {
            (new EmailLog([
                'destinatario' => $para,
                'assunto'      => mb_substr($assunto, 0, 255),
                'sucesso'      => $erro === null,
                'erro'         => $erro,
            ]))->salvar();
        } catch (\Throwable $e) {
            error_log('[email-log] ' . $e->getMessage());
        }
        return $erro === null;
    }
}
