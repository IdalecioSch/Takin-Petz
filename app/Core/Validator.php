<?php

namespace App\Core;

use App\Utils\StringUtils;

class Validator
{
    private $errors = [];

    /**
     * @param bool $ok
     * @param string $message
     * @return static
     */
    public function rule($ok, $message)
    {
        if (!$ok) {
            $this->errors[] = $message;
        }
        return $this;
    }

    /**
     * @param string $value
     * @param string $message
     * @return static
     */
    public function email($value, $message)
    {
        return $this->rule(filter_var($value, FILTER_VALIDATE_EMAIL) !== false && StringUtils::length($value) <= 160, $message);
    }

    /**
     * @param string $table
     * @param string $value
     * @param string $message
     * @return static
     */
    public function inDomain($table, $value, $message)
    {
        return $this->rule(Domain::isValid($table, $value), $message);
    }

    /**
     * @param string $value
     * @param int $min
     * @param int $max
     * @param string $message
     * @return static
     */
    public function length($value, $min, $max, $message)
    {
        $size = StringUtils::length($value);
        return $this->rule($size >= $min && $size <= $max, $message);
    }

    /**
     * @param array $errors
     * @return static
     */
    public function addErrors($errors)
    {
        $this->errors = array_merge($this->errors, $errors);
        return $this;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return $this->errors !== [];
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
