<?php
namespace core\base\controllers;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    static private $_instance;

    private function __construct() {
        // $array = ['1', 2, 3, 4];
        // print_arr($array);
        $s = Settings::get('routes');
        $s1 = ShopSettings::get('routes');
        exit();
    }

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