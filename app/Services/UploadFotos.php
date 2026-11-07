<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/** RN17 / RNF06: valida o tipo real, o tamanho, e grava com nome aleatório. */
final class UploadFotos
{
    private const TIPOS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    private string $diretorio;
    private int $maxBytes;

    public function __construct()
    {
        $this->diretorio = ROOT . '/public/uploads';
        $this->maxBytes = (int)Config::get('upload.max_bytes');
    }

    /** @return string[] mensagens de erro */
    public function validar(array $arquivos): array
    {
        $erros = [];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        foreach ($arquivos as $a) {
            $n = $a['name'];
            if ($a['error'] !== UPLOAD_ERR_OK) {
                $erros[] = in_array($a['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                    ? "A foto \"$n\" é grande demais." : "Falha ao enviar a foto \"$n\".";
                continue;
            }
            if ($a['size'] > $this->maxBytes) {
                $erros[] = "A foto \"$n\" passa do limite de " . round($this->maxBytes / 1048576, 1) . ' MB.';
                continue;
            }
            if (!isset(self::TIPOS[$finfo->file($a['tmp_name'])]) || @getimagesize($a['tmp_name']) === false) {
                $erros[] = "A foto \"$n\" precisa ser JPG, PNG ou WEBP válida.";
            }
        }
        return $erros;
    }

    /** @return string[] nomes gravados */
    public function salvar(array $arquivos): array
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $salvos = [];
        foreach ($arquivos as $a) {
            $nome = bin2hex(random_bytes(16)) . '.' . self::TIPOS[$finfo->file($a['tmp_name'])];
            if (!move_uploaded_file($a['tmp_name'], $this->diretorio . '/' . $nome)) {
                $this->apagar($salvos);
                throw new \RuntimeException('Não foi possível salvar a foto (verifique a permissão de public/uploads).');
            }
            $salvos[] = $nome;
        }
        return $salvos;
    }

    public function apagar(array $nomes): void
    {
        foreach ($nomes as $n) {
            $p = $this->diretorio . '/' . basename($n);
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }
}
