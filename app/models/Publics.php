<?php

use \Phalcon\Mvc\Model\Validator\Uniqueness;

/**
 * @desc	MM => MysqlModal, 封装对mysql数据表的访问基础类
 * @author 	zhonglei
 */
class Publics extends Phalcon\Mvc\Model {

    public static function connect() {
        $config = new \Phalcon\Config\Adapter\Ini('../app/config/config.ini');
        $conn = mysqli_connect($config->database->host, $config->database->username, $config->database->password);

        mysqli_query($conn, 'SET NAMES "UTF8"');
        mysqli_select_db($conn, $config->database->name);

        return $conn;
    }

}
