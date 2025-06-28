<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever you want here
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // If the user is not logged in, redirect them to the login page
        if (!session()->has('isLoggedIn') || !session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please log in to access this page.');
            return redirect()->to(base_url('login'));
        }
    }

    /**
     * Allows post-processing of the response
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here for authentication, primarily for post-processing
    }
}
