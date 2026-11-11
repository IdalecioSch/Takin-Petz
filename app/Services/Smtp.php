<?php

namespace App\Services;

/** Cliente SMTP mínimo (sockets): STARTTLS/SSL, AUTH LOGIN, mensagem texto + HTML. */
class Smtp
{
    private $socket;
    private $config;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $html
     * @param string $text
     * @return void
     * @throws \RuntimeException
     */
    public function send($to, $subject, $html, $text)
    {
        $c = $this->config;
        $security = isset($c['seguranca']) ? $c['seguranca'] : 'tls';
        $target = ($security === 'ssl' ? 'ssl://' : 'tcp://') . $c['host'] . ':' . $c['porta'];
        $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $timeout = isset($c['timeout']) ? (int)$c['timeout'] : 10;

        $this->socket = @stream_socket_client($target, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        if (!$this->socket) {
            throw new \RuntimeException("SMTP: não conectou em {$c['host']}:{$c['porta']} ($errstr)");
        }
        stream_set_timeout($this->socket, $timeout);

        try {
            $this->expect([220]);
            $ehlo = gethostname() ?: 'localhost';
            $this->command("EHLO $ehlo", [250]);

            if ($security === 'tls') {
                $this->command('STARTTLS', [220]);
                if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('SMTP: falha ao iniciar TLS');
                }
                $this->command("EHLO $ehlo", [250]);
            }

            if (!empty($c['usuario'])) {
                $this->command('AUTH LOGIN', [334]);
                $this->command(base64_encode($c['usuario']), [334]);
                $this->command(base64_encode($c['senha']), [235]);
            }

            $this->command('MAIL FROM:<' . $c['remetente_email'] . '>', [250]);
            $this->command('RCPT TO:<' . $to . '>', [250, 251]);
            $this->command('DATA', [354]);

            $message = preg_replace('/^\./m', '..', $this->buildMessage($to, $subject, $html, $text));
            fwrite($this->socket, $message . "\r\n.\r\n");
            $this->expect([250]);

            try {
                $this->command('QUIT', [221]);
            } catch (\Throwable $Ignored) {
            }
        } finally {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
        }
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $html
     * @param string $text
     * @return string
     */
    private function buildMessage($to, $subject, $html, $text)
    {
        $c = $this->config;
        $name = $this->clean(isset($c['remetente_nome']) ? $c['remetente_nome'] : '');
        $from = $name !== '' ? $this->encode($name) . ' <' . $c['remetente_email'] . '>' : $c['remetente_email'];
        $domain = substr(strrchr($c['remetente_email'], '@') ?: '@localhost', 1);
        $boundary = '=_' . bin2hex(random_bytes(12));

        $headers = [
            'Date: ' . date('r'),
            'From: ' . $from,
            'To: ' . $to,
            'Subject: ' . $this->encode($this->clean($subject)),
            'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . $domain . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];
        $part = function ($type, $body) use ($boundary) {
            return "--$boundary\r\nContent-Type: $type; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($body), 76, "\r\n");
        };

        return implode("\r\n", $headers) . "\r\n\r\n" . $part('text/plain', $text) . $part('text/html', $html) . "--$boundary--";
    }

    /**
     * @param string $value
     * @return string
     */
    private function clean($value)
    {
        return trim(str_replace(["\r", "\n"], ' ', $value)); // evita header injection
    }

    /**
     * @param string $value
     * @return string
     */
    private function encode($value)
    {
        return preg_match('/[^\x20-\x7E]/', $value) ? '=?UTF-8?B?' . base64_encode($value) . '?=' : $value;
    }

    /**
     * @param string $line
     * @param int[] $expected
     * @return string
     * @throws \RuntimeException
     */
    private function command($line, $expected)
    {
        fwrite($this->socket, $line . "\r\n");
        return $this->expect($expected);
    }

    /**
     * @param int[] $expected
     * @return string
     * @throws \RuntimeException
     */
    private function expect($expected)
    {
        $response = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        if (!in_array((int)substr($response, 0, 3), $expected, true)) {
            throw new \RuntimeException('SMTP: ' . (trim($response) ?: 'sem resposta/timeout'));
        }
        return $response;
    }
}
