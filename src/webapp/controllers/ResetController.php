<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;

class ResetController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $this->render('reset.twig');
    }

}
