<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        log_message('debug', 'Home::index() method was successfully reached.'); // Add this line

        return view('dashboard/index');
    }
}
