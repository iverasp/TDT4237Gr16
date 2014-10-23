<?php

namespace tdt4237\webapp\models;

use tdt4237\webapp\Hash;

class User
{
    const FIND_BY_NAME = "SELECT * FROM users WHERE user=:username";
    const INSERT_QUERY = "INSERT INTO users(user, pass, email, age, bio, isadmin, resetToken) VALUES(:user, :pass, :email , :age , :bio, :isadmin, :resetToken)";
    const UPDATE_QUERY = "UPDATE users SET pass= :pass, email= :email, age= :age, bio= :bio, isadmin= :isadmin, resetToken= :resetToken WHERE id= :id";


    const MIN_USER_LENGTH = 3;
    const MAX_USER_LENGTH = 25;

    protected $id = null;
    protected $user;
    protected $pass;
    protected $email;
    protected $bio = 'Bio is empty.';
    protected $age;
    protected $isAdmin = 0;
    protected $resetToken;

    static $app;

    function __construct()
    {
    }

    static function make($id, $username, $hash, $email, $bio, $age, $isAdmin, $resetToken)
    {
        $user = new User();
        $user->id = $id;
        $user->user = $username;
        $user->pass = $hash;
        $user->email = $email;
        $user->bio = $bio;
        $user->age = $age;
        $user->isAdmin = $isAdmin;
        $user->resetToken = $resetToken;

        return $user;
    }

    static function makeEmpty()
    {
        return new User();
    }

    /**
     * Insert or update a user object to db.
     */
    function save()
    {
        if ($this->id === null) {
            $sth = self::$app->db->prepare(self::INSERT_QUERY);
            return $sth->execute(array(
                ':user' => $this->user,
                ':pass' => $this->pass,
                ':email' => $this->email,
                ':age' => $this->age,
                ':bio' => $this->bio,
                ':isadmin' => $this->isAdmin,
                ':resetToken' => $this->resetToken
            ));
        } else {
            $sth = self::$app->db->prepare(self::UPDATE_QUERY);
            return $sth->execute(array(
                ':pass' => $this->pass,
                ':email' => $this->email,
                ':age' => $this->age,
                ':bio' => $this->bio,
                ':isadmin' => $this->isAdmin,
                ':id' => $this->id,
                ':resetToken' => $this->resetToken
            ));
        }
    }

    function getId()
    {
        return $this->id;
    }

    function getUserName()
    {
        return $this->user;
    }

    function getPasswordHash()
    {
        return $this->pass;
    }

    function getEmail()
    {
        return $this->email;
    }

    function getBio()
    {
        return $this->bio;
    }

    function getAge()
    {
        return $this->age;
    }

    function isAdmin()
    {
        return $this->isAdmin === "1";
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setUsername($username)
    {
        $this->user = $username;
    }

    function setHash($hash)
    {
        $this->pass = $hash;
        $this->save();
    }

    function setEmail($email)
    {
        $this->email = $email;
    }

    function setBio($bio)
    {
        $this->bio = $bio;
    }

    function setAge($age)
    {
        $this->age = $age;
    }

    function setResetToken($token) {
        $this->resetToken = $token;
        $this->save();

    }

    /**
     * The caller of this function can check the length of the returned 
     * array. If array length is 0, then all checks passed.
     *
     * @param User $user
     * @return array An array of strings of validation errors
     */
    static function validate(User $user)
    {
        $validationErrors = [];

        if (strlen($user->user) < self::MIN_USER_LENGTH) {
            array_push($validationErrors, "Username too short. Min length is " . self::MIN_USER_LENGTH);
        }

        if (strlen($user->user) > self::MAX_USER_LENGTH) {
            array_push($validationErrors, "Username too long. Max length is " . self::MAX_USER_LENGTH);
        }

        if (preg_match('/^[A-Za-z0-9_]+$/', $user->user) === 0) {
            array_push($validationErrors, 'Username can only contain letters and numbers');
        }

        return $validationErrors;
    }

    static function validateAge(User $user)
    {
        $age = $user->getAge();

        if ($age >= 0 && $age <= 150) {
            return true;
        }

        return false;
    }

    static function findResetId($id) {
        $query = "SELECT * from users where resetToken = :resetToken";
        $sth = self::$app->db->prepare($query);
        $result = $sth->execute(array(
            ':resetToken' => $id
        ));
        
        $row = $sth->fetch();

        if ($row == false) { return null; }

        return User::makeFromSql($row);
    }



    /**
     * Find user in db by username.
     *
     * @param string $username
     * @return mixed User or null if not found.
     */
    static function findByUser($username)
    {
        $query = sprintf(self::FIND_BY_NAME, $username);
        $sth = self::$app->db->prepare($query);
        $result = $sth->execute(array(
            ':username' => $username
        ));
        $row = $sth->fetch();

        if($row == false) {
            return null;
        }

        return User::makeFromSql($row);
    }

    static function deleteByUsername($username)
    {
        $query = "DELETE FROM users WHERE user= :username";
        $sth = self::$app->db->prepare($query);
        return $sth->execute(array(
            ':username' => $username
        ));
    }

    static function all()
    {
        $query = "SELECT * FROM users";
        $results = self::$app->db->query($query);

        $users = [];

        foreach ($results as $row) {
            $user = User::makeFromSql($row);
            array_push($users, $user);
        }

        return $users;
    }

    static function makeFromSql($row)
    {
        return User::make(
            $row['id'],
            $row['user'],
            $row['pass'],
            $row['email'],
            $row['bio'],
            $row['age'],
            $row['isadmin'],
            $row['resetToken']
        );
    }
}
User::$app = \Slim\Slim::getInstance();
