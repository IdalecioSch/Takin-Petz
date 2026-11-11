<?php

use App\Core\{Config, Csrf, Domain, Messages, Session, View};
use App\Utils\DateUtils;

// Atalhos para uso nas views.

/**
 * @param string $path
 * @param mixed $default
 * @return mixed
 */
function config($path, $default = null)
{
    return Config::get($path, $default);
}

/**
 * @param mixed $value
 * @return string
 */
function e($value)
{
    return htmlspecialchars((string)($value === null ? '' : $value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * @param string $path
 * @return string
 */
function url($path = '')
{
    return rtrim((string)Config::get('base_path', ''), '/') . '/' . ltrim($path, '/');
}

/**
 * @param string $field
 * @param mixed $default
 * @return string
 */
function old($field, $default = '')
{
    $value = isset($_POST[$field]) ? $_POST[$field] : $default;
    return e(is_scalar($value) ? $value : '');
}

/**
 * @return string
 */
function csrf_field()
{
    return Csrf::field();
}

/**
 * @param string $message
 * @param mixed ...$args
 * @return string
 */
function msg($message)
{
    return call_user_func_array([Messages::class, 'get'], func_get_args());
}

/**
 * @param string|null $file
 * @return string
 */
function photo_url($file)
{
    return $file ? url('uploads/' . rawurlencode($file)) : url('img/no-photo.svg');
}

/**
 * @param string|null $timestamp
 * @param bool $withTime
 * @return string
 */
function date_br($timestamp, $withTime = true)
{
    return DateUtils::formatBr($timestamp, $withTime);
}

/**
 * @param mixed $value
 * @return string
 */
function yes_no($value)
{
    return $value ? 'Sim' : 'Não';
}

/**
 * @param mixed $current
 * @param mixed $value
 * @return string
 */
function selected($current, $value)
{
    return (string)$current === (string)$value ? ' selected' : '';
}

/**
 * @param mixed $value
 * @return string
 */
function checked($value)
{
    return $value ? ' checked' : '';
}

/**
 * @param string $table
 * @param string|null $code
 * @return string
 */
function label($table, $code)
{
    return Domain::getLabel($table, $code);
}

/**
 * @param string $table
 * @return array
 */
function domain($table)
{
    return Domain::getOptions($table);
}

/**
 * @param string $name
 * @param array $data
 * @return void
 */
function partial($name, $data = [])
{
    echo View::render('partials/' . $name, $data, null);
}

/**
 * @return string
 */
function flash_render()
{
    $html = '';
    foreach (Session::pullFlashes() as $flash) {
        $html .= '<div class="alert alert-' . e($flash['type']) . '" role="alert">' . e($flash['message']) . '</div>';
    }
    return $html;
}
