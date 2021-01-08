<?php
namespace core\base\controllers;


class RouteController
{
    static private $_instance;

    private function __construct() {}

    // __clone() создание копии обьекта
    private function __clone() {}

    # singleton шаблон
    static public function getInstance() {
        if(self::$_instance instanceof self) {
            return self::$_instance;
        }
        return self::$_instance = new self;
    }
}