<?php

namespace App\Controllers;
use CodeIgniter\Controller;

class Welcome extends Controller{


    public function index()
    {
        echo "Welcome my Boy";
    }

    public function test($id){
        echo "Welcome    ".$id;
    }

    public function _remap($method){
        echo $method;
    }

}

?>    