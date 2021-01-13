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
        if(strrpos($adress_str, '/') == strlen($adress_str) - 1 && strpos($adress_str, '/' !== 0)) {
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

             $url = explode('/', substr($adress_str, strlen(PATH . $this->routes['admin']['alias']) + 1));

             # лежит ли в нулевом элементе обращение к плагину
             if($url[0] && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH .  $this->routes['plugins']['path'] . $url[0])) {

                 $plugin = array_shift($url);

                 # cуществует ли для плагина файл настроек
                 $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin . 'Settings');

                 if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')) {
                     $pluginSettings = str_replace('/', '\\', $pluginSettings);
                     $this->routes = $pluginSettings::get('routes');
                 };

                 $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] .'/' : '/';
                 $dir = str_replace('//', '/', $dir); // защита замена на один слеш

                 $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;

                 $hrUrl = $this->routes['plugins']['hrUrl'];
                 $route = 'plugins';

             } else {
                 $this->controller = $this->routes['admin']['path'];
                 $hrUrl = $this->routes['admin']['hrUrl'];
                 $route = 'admin';
             }


         } else { # если пользовательская часть

            $url = explode('/', substr($adress_str, strlen(PATH)));
            $hrUrl = $this->routes['user']['hrUrl'];
            $this->controllers = $this->routes['user']['path'];
            $route = 'user';
         }

         $this->createRoute($route, $url);

         # чпу
         if($url[1]) {
             $count = count($url);
             $key = '';

             if(!$hrUrl) {
                 $i = 1;
             } else {
                 $this->parameters['alias'] = $url[1];
                 $i = 2;
             }

             for( ; $i < $count; $i++) {
                if(!$key) {
                    $key = $url[$i];
                    $this->parameters[$key] = '';
                } else {
                    $this->parameters[$key] = $url[$i];
                    $key = '';
                }
             }

         }

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