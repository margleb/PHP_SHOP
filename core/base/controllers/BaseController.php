<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 15.01.2021
 * Time: 14:00
 */

namespace core\base\controllers;


use core\base\exceptions\RouteException;

abstract class BaseController
{
    protected $controllers;
    protected $inputMethod; // метод собирающие данный из базы данных
    protected $outputMethod; // метод подключения вида
    protected $parameters;

    public function route() {

        $controller = str_replace('/', '\\', $this->controllers);

        try {
            # класс отвечающий за проверку и работу с методами
            # ищет метод request в классе controller
            # таким образом обезопасили себя от возможных ошибок
            $object = new \ReflectionMethod($controller, 'request');
            $args = [
                'paramenters' => $this->parameters,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod
            ];
            # вызывает метод request и передает в него аргументы
            $object->invoke(new $controller, $args);

        } catch(\ReflectionException $e) {
            throw new RouteException($e);
        }

    }

    public function request($args) {

    }

}