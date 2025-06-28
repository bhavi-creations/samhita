<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    protected $userModel;
    protected $session;
    protected $validation;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
        helper(['form', 'url']);
    }

    /**
     * Display the login form.
     */
    public function login(): string
    {
        // If already logged in, redirect to dashboard
        if ($this->session->has('isLoggedIn') && $this->session->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }

        $data = [
            'title'      => 'Login',
            'validation' => $this->validation,
        ];
        return view('auth/login', $data);
    }

    /**
     * Handle the login form submission.
     */
    public function attemptLogin(): ResponseInterface
    {
        $rules = [
            'username_or_email' => 'required',
            'password'          => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            log_message('debug', 'Auth::attemptLogin - Validation failed for login attempt.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $usernameOrEmail = $this->request->getPost('username_or_email');
        $password = $this->request->getPost('password');

        log_message('debug', 'Auth::attemptLogin - Attempting login for: ' . $usernameOrEmail);

        $user = $this->userModel->findByCredentials($usernameOrEmail);

        if (!$user) {
            log_message('debug', 'Auth::attemptLogin - User not found for: ' . $usernameOrEmail);
            $this->session->setFlashdata('error', 'Invalid username/email or password.');
            return redirect()->back()->withInput();
        }

        log_message('debug', 'Auth::attemptLogin - User found. Hashed password from DB: ' . $user['password']);
        log_message('debug', 'Auth::attemptLogin - Plaintext password from form (for verification): ' . $password);

        if (!password_verify($password, $user['password'])) {
            log_message('debug', 'Auth::attemptLogin - Password verification failed for user: ' . $usernameOrEmail);
            $this->session->setFlashdata('error', 'Invalid username/email or password.');
            return redirect()->back()->withInput();
        }

        // Authentication successful! Set session data.
        $this->session->set([
            'user_id'    => $user['id'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'isLoggedIn' => true,
        ]);

        log_message('debug', 'Auth::attemptLogin - User ' . $usernameOrEmail . ' successfully logged in.');
        $this->session->setFlashdata('success', 'You have successfully logged in!');
        return redirect()->to(base_url('dashboard'));
    }

    /**
     * Log the user out.
     */
    public function logout(): ResponseInterface
    {
        log_message('debug', 'Auth::logout - User ' . ($this->session->get('username') ?? 'N/A') . ' logging out.');
        $this->session->destroy();
        $this->session->setFlashdata('success', 'You have been logged out.');
        return redirect()->to(base_url('login'));
    }
}
