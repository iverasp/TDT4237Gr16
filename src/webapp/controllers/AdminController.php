<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\User;

class AdminController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $this->check_auth();

        $variables = [
            'users' => User::all()
        ];
        $this->render('admin.twig', $variables);
    }

    function delete()
    {
        $this->check_auth();
        $request = $this->app->request;
        Controller::csrf_check($request);
        $username = $request->post('username');
        $username = Controller::process_url_params($username);
        if (User::deleteByUsername($username) === true) {
            $this->app->flash('info', "Sucessfully deleted '$username'");
        } else {
            $this->app->flash('info', "An error ocurred. Unable to delete user '$username'.");
        }

        $this->app->redirect('/admin');
    }

    function check_auth() {
        if (Auth::guest()) {
            $this->app->flash('info', 'You must be logged in to use this feature');
            $this->app->redirect('/');
        }

        if (! Auth::isAdmin()) {
            $this->app->flash('info', 'You must be administrator to use this feature');
            $this->app->redirect('/');
        }
        return;
    }
}
