<?php

namespace tdt4237\webapp\models;

class Throttling
{
    static $app;

    function __construct()
    {
    }

    static function handleClient() {
        $DELAY_SECONDS = 5;
        $FIND_BY_IP = "SELECT date FROM throttling WHERE ip= :ip";
        $UPDATE_BY_IP = "UPDATE throttling SET date= :date WHERE ip= :ip";
        $INSERT_NEW = "INSERT INTO throttling(ip, date) VALUES(:ip, :date)";
        $sth = self::$app->db->prepare($FIND_BY_IP);
        $sth->execute(array(
            ':ip' => $_SERVER['REMOTE_ADDR']
        ));
        $result = $sth->fetch();
        if ($result) {
            $sth = self::$app->db->prepare($UPDATE_BY_IP);
            $sth->execute(array(
                ':date' => getdate()['0'],
                ':ip' => $_SERVER['REMOTE_ADDR']
            ));
            return $result['date'] > getdate()['0'] - $DELAY_SECONDS;
        }

        $sth = self::$app->db->prepare($INSERT_NEW);
        $sth->execute(array(
            ':ip' => $_SERVER['REMOTE_ADDR'],
            ':date' => getdate()['0']
        ));
        return false;
    }       
}
Throttling::$app = \Slim\Slim::getInstance();
