<?php

namespace App\Core;

use App\Utils\ControllerUtils;

/** Todo controller possui a Persistence e o Model do assunto dele. */
abstract class Controller
{
    protected $Persistence;
    protected $Model;

    /**
     */
    public function __construct()
    {
        $this->Persistence = $this->createPersistence();
        $this->Model = $this->createModel();
    }

    /** @return Persistence|null */
    protected function createPersistence()
    {
        return null;
    }

    /** @return Model|null */
    protected function createModel()
    {
        return null;
    }

    /**
     * @return void
     */
    public function before()
    {
    }

    /**
     * @param string $template
     * @param array $data
     * @param string $layout
     * @param int $status
     * @return void
     */
    protected function view($template, $data = [], $layout = 'site', $status = 200)
    {
        http_response_code($status);
        echo View::render($template, $data, $layout);
    }

    /**
     * @param string $path
     * @return void
     */
    protected function redirect($path)
    {
        ControllerUtils::redirect($path);
    }

    /**
     * @param string $type
     * @param string $message
     * @return void
     */
    protected function flash($type, $message)
    {
        Session::flash($type, $message);
    }

    /**
     * @return void
     * @throws HttpException
     */
    protected function verifyCsrf()
    {
        Csrf::verify();
    }
}
