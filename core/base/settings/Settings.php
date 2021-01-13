<?php

namespace core\base\settings;

class Settings
{
    private static $_instance;
    private function __construct(){}
    private function __clone(){}

    private $routes = [
        'admin' => [
            'alias' => 'admin',
            'path' => 'core/admin/controllers',
            'hrUrl' => false,
            'routes' => [

            ]
        ],
        'settings' => [
            'path' => 'core/base/settings'
        ],
        'plugins' => [
            'path' => 'core/plugins',
            'hrUrl' => false,
            'dif' => false
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

    private $templateArr = [
        'text' => ['name'],
    ];

    private $example = 'example';

    static public function get($propery) {
        return self::instance()->$propery;
    }

    public static function instance() {
        if(self::$_instance instanceof self) {
            return self::$_instance;
        }
        return self::$_instance = new self;
    }

    public function clueProperties($class) {
        $baseProperties = [];
        foreach($this as $name => $item) {
            $property = $class::get($name);

            if(is_array($property) && is_array($item)) {
                $baseProperties[$name] = $this->arrayMergeRecrusive($this->$name, $property); // соединяем 2 массива в один
                continue;
            }

            if(!$property) $baseProperties[$name] = $this->$name; // если указано свойство которого нет в другом файле
        }

        return $baseProperties;
    }

    # соединяем 2 массива в один
    public function arrayMergeRecrusive() {
        $arrays = func_get_args(); // массив с аргументами функции
        $base = array_shift($arrays); // извлекаем первый массив
        foreach($arrays as $array) {
           foreach($array as $key => $value) {
               if(is_array($value) && is_array($base[$key])) {
                   $base[$key] = $this->arrayMergeRecrusive($base[$key], $value);
               } else {
                   if(is_int($key)) {
                       if(!in_array($value, $base)) array_push($base, $value);
                       continue;
                   }
                   $base[$key] = $value;
               }
           }
        }
        return $base;
    }

}