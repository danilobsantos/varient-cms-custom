<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $router = service('router');
        $controller = basename($router->controllerName());

        if (!authCheck()) {
            $targetUrl = (str_contains($controller, 'HomeController') || str_contains($controller, 'AccountController') || str_contains($controller, 'EarningsController'))
                ? langBaseUrl()
                : adminUrl('login');

            redirect()->to($targetUrl)->send();
            exit;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}