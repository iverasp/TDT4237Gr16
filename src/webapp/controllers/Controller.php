<?php

namespace tdt4237\webapp\controllers;
use tdt4237\webapp\Auth;

class Controller
{
    protected $app;

    function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    function render($template, $variables = [])
    {
        if (! Auth::guest()) {
            $variables['isLoggedIn'] = true;
            $variables['isAdmin'] = Auth::isAdmin();
            $variables['loggedInUsername'] = $_SESSION['user'];
            $variables['csrfToken'] = $_SESSION['csrfToken'];
        }

        print $this->app->render($template, $variables);
    }

    function process_url_params($data) {
        $data = trim($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function csrf_check($request) {
        if (Controller::process_url_params($request->post('csrfToken')) != $_SESSION['csrfToken']) {

            $this->app->flash('info', 'Something went wrong!');
            $this->app->redirect('/');
        }
    }
}
