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
        $username = Controller::process_url_params($request->post('username'));

        if (User::findByUser($username)) {
            $user = User::findByUser($username);
            if($user->getEmail()) {
                // send email to user
            } else {
                $this->app->flash('info', 'No email registered on user');
                $this->app->redirect('/reset');
            }
        } else {
            $this->app->flash('info', 'User not found in database');
            $this->app->redirect('/reset');
            return;
        }
    }

}
