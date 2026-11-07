<?php
declare(strict_types=1);

namespace App\Services;

/** Cliente SMTP mínimo (sockets): STARTTLS/SSL, AUTH LOGIN, mensagem texto + HTML. */
final class Smtp
{
    /** @var resource|null */
    private $fp = null;

    public function __construct(private array $c)
    {
    }

    public function enviar(string $para, string $assunto, string $html, string $texto): void
    {
        $c = $this->c;
        $seg = $c['seguranca'] ?? 'tls';
        $alvo = ($seg === 'ssl' ? 'ssl://' : 'tcp://') . $c['host'] . ':' . $c['porta'];
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);

        $this->fp = @stream_socket_client($alvo, $errno, $errstr, (float)($c['timeout'] ?? 10), STREAM_CLIENT_CONNECT, $ctx);
        if (!$this->fp) {
            throw new \RuntimeException("SMTP: não conectou em {$c['host']}:{$c['porta']} ($errstr)");
        }
        stream_set_timeout($this->fp, (int)($c['timeout'] ?? 10));

        try {
            $this->esperar([220]);
            $ehlo = gethostname() ?: 'localhost';
            $this->cmd("EHLO $ehlo", [250]);

            if ($seg === 'tls') {
                $this->cmd('STARTTLS', [220]);
                if (!stream_socket_enable_crypto($this->fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('SMTP: falha ao iniciar TLS');
                }
                $this->cmd("EHLO $ehlo", [250]);
            }

            if (!empty($c['usuario'])) {
                $this->cmd('AUTH LOGIN', [334]);
                $this->cmd(base64_encode($c['usuario']), [334]);
                $this->cmd(base64_encode($c['senha']), [235]);
            }

            $this->cmd('MAIL FROM:<' . $c['remetente_email'] . '>', [250]);
            $this->cmd('RCPT TO:<' . $para . '>', [250, 251]);
            $this->cmd('DATA', [354]);

            $msg = preg_replace('/^\./m', '..', $this->montar($para, $assunto, $html, $texto)); // dot-stuffing
            fwrite($this->fp, $msg . "\r\n.\r\n");
            $this->esperar([250]);

            try {
                $this->cmd('QUIT', [221]);
            } catch (\Throwable) {
            }
        } finally {
            if (is_resource($this->fp)) {
                fclose($this->fp);
            }
        }
    }

    private function montar(string $para, string $assunto, string $html, string $texto): string
    {
        $c = $this->c;
        $nome = $this->limpar($c['remetente_nome'] ?? '');
        $from = $nome !== '' ? $this->codificar($nome) . ' <' . $c['remetente_email'] . '>' : $c['remetente_email'];
        $dominio = substr(strrchr($c['remetente_email'], '@') ?: '@localhost', 1);
        $limite = '=_' . bin2hex(random_bytes(12));

        $cabecalhos = [
            'Date: ' . date('r'),
            'From: ' . $from,
            'To: ' . $para,
            'Subject: ' . $this->codificar($this->limpar($assunto)),
            'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . $dominio . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $limite . '"',
        ];
        $parte = fn(string $tipo, string $corpo) =>
            "--$limite\r\nContent-Type: $tipo; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($corpo), 76, "\r\n");

        return implode("\r\n", $cabecalhos) . "\r\n\r\n"
            . $parte('text/plain', $texto) . $parte('text/html', $html) . "--$limite--";
    }

    private function limpar(string $s): string
    {
        return trim(str_replace(["\r", "\n"], ' ', $s)); // evita header injection
    }

    private function codificar(string $s): string
    {
        return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
    }

    private function cmd(string $linha, array $ok): string
    {
        fwrite($this->fp, $linha . "\r\n");
        return $this->esperar($ok);
    }

    private function esperar(array $ok): string
    {
        $resp = '';
        while (($l = fgets($this->fp, 515)) !== false) {
            $resp .= $l;
            if (strlen($l) < 4 || $l[3] === ' ') {
                break;
            }
        }
        if (!in_array((int)substr($resp, 0, 3), $ok, true)) {
            throw new \RuntimeException('SMTP: ' . (trim($resp) ?: 'sem resposta/timeout'));
        }
        return $resp;
    }
}
