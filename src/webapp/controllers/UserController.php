<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\User;
use tdt4237\webapp\Hash;
use tdt4237\webapp\Auth;

class UserController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::guest()) {
            $this->render('newUserForm.twig', []);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function create()
    {
        $request = $this->app->request;
        $email = Controller::process_url_params($request->post('email'));
        $username = Controller::process_url_params($request->post('user'));
        $pass = Controller::process_url_params($request->post('pass'));
        if (User::findByUser($username)) {
            $this->app->flash('info', 'Username exists. Pick another');
            $this->app->redirect('/user/new');
        }
        if (strlen($pass) < 8) {
            $this->app->flash('info', 'Password must be longer than 8 characters');
            $this->app->redirect('/user/new');
        }
        if (strlen($username) > 25) {
            $this->app->flash('info', 'Please keep your username length less than 50 characters');
            $this->app->redirect('/user/new');
        }

        $hashed = Hash::make($pass);

        $user = User::makeEmpty();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setHash($hashed);

        $validationErrors = User::validate($user);

        if (sizeof($validationErrors) > 0) {
            $errors = join("<br>\n", $validationErrors);
            $this->app->flashNow('error', $errors);
            $this->render('newUserForm.twig', ['username' => $username]);
        } else {
            $user->save();
            $this->app->flash('info', 'Thanks for creating a user. Now log in.');
            $this->app->redirect('/login');
        }
    }

    function all()
    {
        $users = User::all();
        $this->render('users.twig', ['users' => $users]);
    }

    function logout()
    {

        if ($this->app->request->isPost()) {
            $request = $this->app->request;
            Controller::csrf_check($request);
        }
        Auth::logout();
        $this->app->redirect('/?msg=Successfully logged out.');
        session_unset();
        session_destroy();
    }

    function show($username)
    {
        $user = User::findByUser($username);

        $this->render('showuser.twig', [
            'user' => $user,
            'username' => Controller::process_url_params($username)
        ]);
    }
    function error_flasher($error){
        $this->app->flash('info', $error);
        $this->app->redirect('/user/edit');
    }

    function upload_profile_image($user)
    {
        if ( isset($_FILES['image']['error']) and !is_array($_FILES['image']['error']) ) {
            if( isset($_FILES['image']) and is_uploaded_file($_FILES['image']['tmp_name']) ) {
                list($width, $height, $type, $attr) = getimagesize($_FILES['image']['tmp_name']);
                $filesize  = filesize($_FILES['image']['tmp_name']);
                if ($width <= 500 and $height <= 500 and $filesize <= 1*1000*1000) {
                    //save only one image per user, unique by their id in the filestore.
                    $filename = $user->getId() . '.jpg';
                    $image_path = 'web/images/users/' . $filename;

                    // Safe way to check file type.
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                    if ($mime == 'image/jpg' or $mime == 'image/jpeg' or $mime == 'image/png') {
                        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
                    } else {
                        $this->error_flasher('Not a valid image type.');
                    }
                    finfo_close($finfo);
                } else {
                    $this->error_flasher('Image is too large. Max size is 500x500px and 1MB');
                }
            } else { // image is not set or is not an uploaded file.
                $this->error_flasher('Not an image.');
            }
        } else {
            $this->error_flasher('Invalid file.');
        }
    }

    function edit()
    {
        if (Auth::guest()) {
            $this->app->flash('info', 'You must be logged in to edit your profile.');
            $this->app->redirect('/login');
            return;
        }

        $user = Auth::user();

        if (! $user) {
            throw new \Exception("Unable to fetch logged in user's object from db.");
        }

        if ($this->app->request->isPost()) {
            $request = $this->app->request;
            Controller::csrf_check($request);
            $email = Controller::process_url_params($request->post('email'));
            $bio = Controller::process_url_params($request->post('bio'));
            $age = Controller::process_url_params($request->post('age'));
            //$image = Controller::process_url_params($request->post('image'));
            $this->upload_profile_image($user);
            $user->setEmail($email);
            $user->setBio($bio);
            $user->setAge($age);

            if (! User::validateAge($user)) {
                $this->app->flashNow('error', 'Age must be between 0 and 150.');
            } else {
                $user->save();
                $this->app->flashNow('info', 'Your profile was successfully saved.');
            }
        }

        $this->render('edituser.twig', ['user' => $user, 'csrfToken' => $_SESSION['csrfToken']]);
    }
}
