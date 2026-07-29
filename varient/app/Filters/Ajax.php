<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Ajax implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!$request->isAJAX()) {
            return service('response')
                ->setStatusCode(400)
                ->setJSON(['error' => 'Invalid request type']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}