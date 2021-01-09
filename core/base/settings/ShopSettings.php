<?php

namespace core\base\settings;
use core\base\settings\Settings;

class ShopSettings
{
    private $_singleton;
    private $baseSettings;

    private function __construct(){}
    private function __clone(){}

    private $templateArr = [
        'text' => ['price', 'short'],
        'textarea' => ['goods_content']
    ];

    static public function get($propery) {
        // return self::instance()->$propery;
    }

    private static function instance() {
        if(self::$_singleton instanceof self) {
            return self::$_singleton;
        }
        return self::$_singleton = new self;
    }
}