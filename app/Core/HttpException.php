<?php

namespace App\Core;

class HttpException extends \RuntimeException
{
    private $status;

    /**
     * @param int $status
     * @param string $message
     */
    public function __construct($status, $message = '')
    {
        parent::__construct($message, $status);
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
