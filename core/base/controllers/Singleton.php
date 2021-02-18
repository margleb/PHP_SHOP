<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 18.01.2021
 * Time: 10:53
 */

namespace core\base\controllers;


trait Singleton
{
    private static $_instance;
    private function __construct(){}
    private function __clone(){}

    public static function instance() {
        if(self::$_instance instanceof self) {
            return self::$_instance;
        }

        self::$_instance = new self;

        // создание подкллючения к бд
        if(method_exists(self::$_instance, 'connect'))  {
            self::$_instance->connect();
        }

        return self::$_instance;
    }
}