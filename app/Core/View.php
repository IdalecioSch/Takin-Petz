<?php

namespace App\Core;

class View
{
    /**
     * @param string $template
     * @param array $data
     * @param string|null $layout
     * @return string
     * @throws \Exception
     */
    public static function render($template, $data = [], $layout = 'site')
    {
        $content = self::capture($template, $data);
        if ($layout === null) {
            return $content;
        }
        return self::capture('layouts/' . $layout, $data + ['content' => $content]);
    }

    /**
     * @param string $__template
     * @param array $__data
     * @return string
     * @throws \Throwable
     */
    private static function capture($__template, $__data)
    {
        extract($__data, EXTR_SKIP);
        ob_start();
        try {
            require ROOT . '/app/Views/' . $__template . '.php';
            return (string)ob_get_clean();
        } catch (\Throwable $Exception) {
            ob_end_clean();
            throw $Exception;
        }
    }
}
