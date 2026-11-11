<?php

namespace App\Services;

use App\Core\Config;
use App\Models\EmailLog;
use App\Persistence\EmailLogPersistence;

class Mailer
{
    /**
     * @param string $to
     * @param string $subject
     * @param string $html
     * @return bool
     */
    public function send($to, $subject, $html)
    {
        $to = trim(str_replace(["\r", "\n"], '', $to));
        $error = null;
        try {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Destinatário inválido');
            }
            $text = trim(html_entity_decode(strip_tags(preg_replace('/<br\s*\/?>|<\/p>/i', "\n", $html)), ENT_QUOTES, 'UTF-8'));
            (new Smtp((array)Config::get('smtp')))->send($to, $subject, $html, $text);
        } catch (\Throwable $Exception) {
            $error = $Exception->getMessage();
            error_log('[email] ' . $to . ' - ' . $error);
        }

        try {
            (new EmailLogPersistence())->save(new EmailLog([
                'destinatario' => $to,
                'assunto' => substr($subject, 0, 255),
                'sucesso' => $error === null,
                'erro' => $error,
            ]));
        } catch (\Throwable $Exception) {
            error_log('[email-log] ' . $Exception->getMessage());
        }
        return $error === null;
    }
}
