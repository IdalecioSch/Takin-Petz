<?php

namespace App\Utils;

use App\Core\Config;

class ControllerUtils
{
    /**
     * @return string
     */
    public static function getMethod()
    {
        return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }

    /**
     * @return string
     */
    public static function getPath()
    {
        $path = rawurldecode(parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH) ?: '/');
        $base = rtrim((string)Config::get('base_path', ''), '/');
        if ($base !== '' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }
        return '/' . trim($path, '/');
    }

    /**
     * @return bool
     */
    public static function isPost()
    {
        return self::getMethod() === 'POST';
    }

    /**
     * @param string $field
     * @return string
     */
    public static function getPost($field)
    {
        return StringUtils::clean(ArrayUtils::get($_POST, $field, ''));
    }

    /**
     * @param string $field
     * @return string
     */
    public static function getQuery($field)
    {
        return StringUtils::clean(ArrayUtils::get($_GET, $field, ''));
    }

    /**
     * @param string $field
     * @return int
     */
    public static function getPostInt($field)
    {
        return (int)self::getPost($field);
    }

    /**
     * @param string $field
     * @return bool
     */
    public static function isChecked($field)
    {
        return !empty($_POST[$field]);
    }

    /**
     * @param string $field
     * @return int[]
     */
    public static function getPostIntList($field)
    {
        return ArrayUtils::toInts(ArrayUtils::get($_POST, $field, []));
    }

    /**
     * @param string $field
     * @return array
     */
    public static function getFiles($field)
    {
        $files = ArrayUtils::get($_FILES, $field);
        if (!$files || !is_array($files['name'])) {
            return [];
        }
        $list = [];
        foreach ($files['name'] as $i => $name) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $list[] = ['name' => $name, 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
        }
        return $list;
    }

    /**
     * @param string $path
     * @return void
     */
    public static function redirect($path)
    {
        header('Location: ' . url($path));
        exit;
    }

    /**
     * @param mixed $data
     * @param int $status
     * @return void
     */
    public static function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
