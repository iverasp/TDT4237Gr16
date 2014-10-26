<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Hash;
use tdt4237\webapp\models\User;
use tdt4237\webapp\models\Throttling;

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

        if (Throttling::handleClient()) {
            $this->app->flash('info', 'You can only reset password every 5 seconds');
            $this->app->redirect('/');
        }

        $request = $this->app->request;
        $username = Controller::process_url_params($request->post('username'));
        Controller::csrf_check($request);
        if (User::findByUser($username)) {
            $user = User::findByUser($username);
            if($user->getEmail()) {
                /* Generate random token and
                send an email to user with link
                for password reset form */
                
                $token = bin2hex(openssl_random_pseudo_bytes(6));
                $user->setResetToken($token);

                $this->app->render('resetEmail.twig', ['token' => $token]);

                /* The webapp would now send an email to the user
                but as this requires third-pary-libs or a server
                compatible with the mail() function, this has been
                disabled */
                //$this->app->flash('info', 'Reset link sent to email');
                //$this->app->redirect('/reset');
            } else {
                $this->app->flash('info', 'No email registered on user.');
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
            // allow user to reset password
            $this->render('reset.twig');
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
                Controller::csrf_check($request);
                $pass = Controller::process_url_params($request->post('pass'));
                $user->setHash(Hash::make($pass));
                $this->app->flash('info', 'Password reset. Now login');
                $this->app->redirect('/login');
            } else {
                $this->app->redirect('/');
            }
    }

}
