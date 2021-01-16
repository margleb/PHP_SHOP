<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 15.01.2021
 * Time: 14:00
 */

namespace core\base\controllers;


use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseController
{

    protected $page;
    protected $errors;

    protected $controllers;
    protected $inputMethod; // метод собирающие данный из базы данных
    protected $outputMethod; // метод подключения вида
    protected $parameters;

    public function route() {

        // обьект, находящийся в свойстве controller
        $controller = str_replace('/', '\\', $this->controllers);

        try {
            // метод ReflectionMethod, проверяет существование методов в данном классе
            $object = new \ReflectionMethod($controller, 'request');
            $args = [ // массив параметров с входным и выходним методом
                'paramenters' => $this->parameters,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod
            ];
            // если метод есть, то он вызывает его, передав в него массив $args
            $object->invoke(new $controller, $args);

        } catch(\ReflectionException $e) {
            throw new RouteException($e->getMessage());
        }
    }

    public function request($args) {

        $this->parameters = $args['parameters'];

        $inputData = $args['inputMethod'];
        $outputData = $args['outputMethod'];

        $data = $this->$inputData();

        if(method_exists($this, $outputData)) {

            $page = $this->$outputData($data);
            if($page) $this->page = $page;

        } elseif($data) {
            $this->page = $data;
        }

        # логирование ошибок
        if($this->errors) {
            $this->writeLog();
        }

        # завершаем скрипт, показав пользователю страницу
        $this->getPage();

    }

    protected function render($path = '', $paramenters = []) {

        // импортирует переменные в текущую таблицу символов
        extract($paramenters);

        if(!$path) {
            # \ReflectionClass - предоставляет инфу о классе
            # $this - обьект класса IndexController

            $class = new \ReflectionClass($this);

            # пространство имен этого класса
            $space = str_replace('\\', '/',$class->getNamespaceName() . '\\');
            $routes = Settings::get('routes');

            if($space === $routes['user']['path']) $template = TEMPLATE;
            else $template = ADMIN_TEMPLATE;

            $path = $template . explode('controller', strtolower($class->getShortName()))[0];
        }

        # if(!@include_once $path . '.php') throw new RouteException('Отсутсвует шаблон - ' . $path);
        # exit();

        ob_start(); // открытие буфера обмена

        if(!@include_once $path . '.php') throw new RouteException('Отсутсвует шаблон - ' . $path);

        return ob_get_clean(); // вернет в переменную template то, что находится в буфере обмена, после чего закроет буфер

    }

    protected function getPage() { // метод показывающий страницу
        if(is_array($this->page)) {
            foreach($this->page as $block) echo $block;
        } else {
            echo $this->page;
        }
        exit();
    }

}