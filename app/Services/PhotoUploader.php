<?php

namespace App\Services;

use App\Core\{Config, Messages};

class PhotoUploader
{
    private static $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    private $directory;
    private $maxBytes;

    /**
     */
    public function __construct()
    {
        $this->directory = ROOT . '/public/uploads';
        $this->maxBytes = (int)Config::get('upload.max_bytes');
    }

    /**
     * @param array $files
     * @return string[]
     */
    public function validate($files)
    {
        $errors = [];
        $Finfo = new \finfo(FILEINFO_MIME_TYPE);
        foreach ($files as $file) {
            $name = $file['name'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $tooBig = in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
                $errors[] = Messages::get($tooBig ? Messages::V_PHOTO_TOO_LARGE : Messages::V_PHOTO_UPLOAD, $name);
            } elseif ($file['size'] > $this->maxBytes) {
                $errors[] = Messages::get(Messages::V_PHOTO_LIMIT, $name, round($this->maxBytes / 1048576, 1));
            } elseif (!isset(self::$types[$Finfo->file($file['tmp_name'])]) || @getimagesize($file['tmp_name']) === false) {
                $errors[] = Messages::get(Messages::V_PHOTO_TYPE, $name);
            }
        }
        return $errors;
    }

    /**
     * @param array $files
     * @return string[]
     * @throws \RuntimeException
     */
    public function store($files)
    {
        $Finfo = new \finfo(FILEINFO_MIME_TYPE);
        $stored = [];
        foreach ($files as $file) {
            $name = bin2hex(random_bytes(16)) . '.' . self::$types[$Finfo->file($file['tmp_name'])];
            if (!move_uploaded_file($file['tmp_name'], $this->directory . '/' . $name)) {
                $this->remove($stored);
                throw new \RuntimeException(Messages::PHOTO_SAVE_FAILED);
            }
            $stored[] = $name;
        }
        return $stored;
    }

    /**
     * @param string[] $names
     * @return void
     */
    public function remove($names)
    {
        foreach ($names as $name) {
            $path = $this->directory . '/' . basename($name);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
