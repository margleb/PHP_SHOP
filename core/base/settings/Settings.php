<?php

namespace core\base\settings;
use core\base\controllers\Singleton;

class Settings
{


    use Singleton; # трейд Singleton

    private $routes = [
        'admin' => [
            'alias' => 'admin',
            # 'alias' => 'sudo',
            'path' => 'core/admin/controllers/',
            'hrUrl' => false,
            'routes' => [
                'product' => 'goods/getGoods/sale'
            ]
        ],
        'settings' => [
            'path' => 'core/base/settings/'
        ],
        'plugins' => [
            'path' => 'core/plugins/',
            'hrUrl' => false,
            'dir' => false
        ],
        'user' => [
            'path' => 'core/user/controllers/',
            'hrUrl' => true,
            'routes' => [
                // alias для каталога; hello - входной; bye - выходной метод
                # 'catalog' => 'site/hello/bye'
                'site' => 'index/hello'
            ]
        ],
        'default' => [
            'controller' => 'IndexController',
            'outputMethod' => 'outputData',
            'inputMethod' => 'inputData'
        ],
    ];

    private $templateArr = [
        'text' => ['name'],
    ];

    private $example = 'example';

    static public function get($propery) {
        return self::instance()->$propery;
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