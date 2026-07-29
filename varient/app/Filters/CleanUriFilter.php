<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CleanUriFilter implements FilterInterface
{
    /**
     * Remove 'index.php' from incoming URLs to enforce clean URLs
     */
    public function before(RequestInterface $request, $args = null)
    {
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Check if '/index.php' exists in the URL
        if (strpos($rawUri, '/index.php') !== false) {

            $clean = str_replace('/index.php', '', $rawUri);

            if ($clean === '') {
                $clean = '/';
            }

            // Build redirect URL (auto-includes port if needed)
            $newUrl = ($request->isSecure() ? 'https://' : 'http://')
                . $request->getUri()->getAuthority()
                . $clean;

            return redirect()->to($newUrl)->setStatusCode(301);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $args = null)
    {
    }
}