<?php
namespace core\base\controllers;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use core\base\settings\ShopSettings;

class RouteController
{
    static private $_instance;

    protected $routes;
    protected $controllers;
    protected $inputMethod; // метод собирающие данный из базы данных
    protected $outputMethod; // метод подключения вида
    protected $parameters;

    private function __construct() {

        $adress_str = $_SERVER['REQUEST_URI'];

        # -1 так как порядковый символ равен 9
        # cервер по умлочанию подставлет слеш в корневой каталог
        # если слеш стоит в конце строки и это не корень сайта
        if(strpos($adress_str, '/') == strlen($adress_str) - 1 && strpos($adress_str, '/' !== 0)) {
            # rtrim - обраезает пробелы а также символы в конце строки
            # 301 - код ответа сервера
            # переправляем пользователя на ссылку без символа /
            $this->redirect(rtrim($adress_str, '/'), 301);
        }

        # в переменную path сохранили обрезанную строку в которой содержиться имя выполнения скрипта
        # имя скрипта, который выполняет код
        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));

        if($path == PATH) {

         $this->routes = Settings::get('routes');
         if(!$this->routes) throw new RouteException('Сайт находится на техническом обслуживании');

         # если это административная панель
         if(strpos($adress_str, $this->routes['admin']['alias']) === strlen(PATH)) {

         } else { # если пользовательская часть
            $url = explode('/', substr($adress_str, strlen(PATH)));
            $hrUrl = $this->routes['user']['hrUrl'];
            $this->controllers = $this->routes['user']['path'];
            $route = 'user';
         }

         $this->createRoute($route, $url);

         // exit();

        } else {
            try {
                throw new \Exception('Некорректная дериктория сайта');
            } catch(\Exception $e) {
                exit($e->getMessage());
            }
        }
    }

    private function createRoute($var, $arr) {
        $route = [];
        if(!empty($arr[0])) {
            if($this->routes[$var]['routes'][$arr[0]]) {
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);
                $this->controllers .= ucfirst($route[0] . 'Controller');
            } else {
                $this->controllers .= ucfirst($arr[0] . 'Controller');
            }
        } else {
            $this->controllers .= $this->routes['default']['controller'];
        }
        $this->inputMethod = $route[1] ? $route[1] : $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ? $route[2] : $this->routes['default']['outputMethod'];
        return;
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