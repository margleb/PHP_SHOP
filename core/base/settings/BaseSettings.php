<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 02.04.2021
 * Time: 8:11
 */

namespace core\base\settings;
use core\base\controllers\Singleton;
use core\base\settings\Settings;



trait BaseSettings
{

    use Singleton {
        instance as SingletonInstance; // псевдоним
    }

    private $baseSettings;

    static public function get($propery) {
        return self::instance()->$propery;
    }

    public static function instance() {
        if(self::$_instance instanceof self) {
            return self::$_instance;
        }
        self::SingletonInstance()->baseSettings = Settings::instance(); // получаем базовые настройки
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