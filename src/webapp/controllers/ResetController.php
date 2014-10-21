<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\User;

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

    function reset() {
        $request = $this->app->request;
        $username = Controller::process_url_params($request->post('email'));
        if (User::findByUser($username)) {
        
        } else {
            $this->app->flash('info', 'User not found in database');
            $this->app->redirect('/reset');
            return;
        }
    }

}
