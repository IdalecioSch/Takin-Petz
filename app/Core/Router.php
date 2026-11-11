<?php

namespace App\Core;

use App\Utils\ControllerUtils;

class Router
{
    private $routes = [];

    /**
     * @param string $pattern
     * @param string $controller
     * @param string $action
     * @return void
     */
    public function get($pattern, $controller, $action)
    {
        $this->add('GET', $pattern, $controller, $action);
    }

    /**
     * @param string $pattern
     * @param string $controller
     * @param string $action
     * @return void
     */
    public function post($pattern, $controller, $action)
    {
        $this->add('POST', $pattern, $controller, $action);
    }

    /**
     * @param string $prefix
     * @param string $controller
     * @return void
     */
    public function crud($prefix, $controller)
    {
        $this->get($prefix, $controller, 'index');
        $this->get($prefix . '/novo', $controller, 'create');
        $this->post($prefix, $controller, 'store');
        $this->get($prefix . '/{id}/editar', $controller, 'edit');
        $this->post($prefix . '/{id}', $controller, 'update');
        $this->post($prefix . '/{id}/excluir', $controller, 'destroy');
    }

    /**
     * @param string $method
     * @param string $pattern
     * @param string $controller
     * @param string $action
     * @return void
     */
    private function add($method, $pattern, $controller, $action)
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
        $this->routes[] = compact('method', 'regex', 'controller', 'action');
    }

    /**
     * @return void
     * @throws HttpException
     */
    public function dispatch()
    {
        $path = ControllerUtils::getPath();
        foreach ($this->routes as $route) {
            if ($route['method'] !== ControllerUtils::getMethod() || !preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            $params = [];
            foreach ($matches as $name => $value) {
                if (is_string($name)) {
                    $params[] = ctype_digit($value) ? (int)$value : $value;
                }
            }
            $Controller = new $route['controller']();
            $Controller->before();
            call_user_func_array([$Controller, $route['action']], $params);
            return;
        }
        throw new HttpException(404, Messages::ERROR_NOT_FOUND);
    }
}
