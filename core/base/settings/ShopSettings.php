<?php

namespace core\base\settings;
use core\base\controllers\Singleton;
use core\base\settings\Settings;

class ShopSettings
{

    use Singleton {
        instance as traitInstance; // псевдоним
    }

    private $baseSettings;

    private $routes = [
        'plugins' => [
            'dir' => false,
            'routes' => [

            ]
        ],
    ];

    private $templateArr = [
        'text' => ['price', 'short'],
        'textarea' => ['goods_content']
    ];

    static public function get($propery) {
        return self::instance()->$propery;
    }

    private static function instance() {
        if(self::$_instance instanceof self) {
            return self::$_instance;
        }
        self::traitInstance()->baseSettings = Settings::instance(); // получаем базовые настройки
        $baseProperties = self::$_instance->baseSettings->clueProperties(get_class()); // соединяем с базовыми настройками
        self::$_instance->setProperty($baseProperties); // записывем в обьект все склеенные свойства
        return self::$_instance;
    }

    # записывет в обьект все базовые свойства
    protected function setProperty($properties) {
        if($properties) {
            foreach($properties as $name => $property) {
                $this->$name = $property;
            }
        }
    }
}