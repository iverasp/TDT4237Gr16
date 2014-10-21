<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Hash;
use tdt4237\webapp\models\User;

class ResetController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $this->render('resetForm.twig');
    }

    function reset() {
        $request = $this->app->request;
        $username = Controller::process_url_params($request->post('username'));

        if (User::findByUser($username)) {
            $user = User::findByUser($username);
            if($user->getEmail()) {
                // send email to user

                $token = "hemmelig"; //should be random
                $user->setResetToken($token);

                $this->app->flash('info', 'Reset link sent to email');
                $this->app->redirect('/reset');
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

    function resetId($id) {
        $user = User::findResetId($id);
        if ($user) {
            $this->render('reset.twig');
            // allow user to reset password
        } else {
            $this->app->flash('info', 'Token not found in database');
            $this->app->redirect('/reset');
        }
    }

    function resetPass($id) {
            $user = User::findResetId($id);
            if ($user) {
            $user->setResetToken(null);
            $request = $this->app->request;
            $pass = Controller::process_url_params($request->post('pass'));
            $user->setHash(Hash::make($pass));
            $this->app->flash('info', 'Password reset. Now login');
            $this->app->redirect('/login');
            }

    }

}
