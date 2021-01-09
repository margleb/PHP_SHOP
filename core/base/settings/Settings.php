<?php

namespace core\base\settings;

class Settings
{
    private $_singleton;
    private function __construct(){}
    private function __clone(){}

    private $_routes = [
        'admin' => [
            'name' => 'admin',
            'path' => 'core/admin/controllers',
            'hrUrl' => false
        ],
        'settings' => [
            'path' => 'core/base/settings'
        ],
        'plugins' => [
            'path' => 'core/plugins',
            'hrUrl' => false
        ],
        'user' => [
            'path' => 'core/base/user/controllers',
            'hrUrl' => true,
            'routes' => [

            ]
        ],
        'default' => [
            'controller' => 'IndexController',
            'outputMethod' => 'outputData',
            'inputMethod' => 'inputData'
        ]
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