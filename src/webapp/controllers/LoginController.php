<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\Throttling;

class LoginController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {
            $this->render('login.twig', []);
        }
    }

    function login()
    {
        $request = $this->app->request;
        $user = Controller::process_url_params($request->post('user'));
        $pass = Controller::process_url_params($request->post('pass'));
        // Poor mans throttling
        if (Throttling::handleClient()) {
            $this->app->flash('info', 'You can only log in every 5 seconds');
            $this->app->redirect('/');
        }

        if (Auth::checkCredentials($user, $pass)) {
            $_SESSION['user'] = $user;

            $isAdmin = Auth::user()->isAdmin();

            if ($isAdmin) {
                $_SESSION["isadmin"] = true;
            } else {
                $_SESSION["isadmin"] = false;
            }

            session_regenerate_id();

            $_SESSION['csrfToken'] = base64_encode(openssl_random_pseudo_bytes(32));

            $this->app->flash('info', "You are now successfully logged in as $user.");
            $this->app->redirect('/');
        } else {
            $this->app->flashNow('error', 'Incorrect user/pass combination.');
            $this->render('login.twig', []);
        }
    }
}
