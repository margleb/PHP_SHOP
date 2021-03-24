<?php
namespace core\base\controllers;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

class RouteController extends BaseController
{

    use Singleton; # trait singleton

    protected $routes;

    private function __construct() {

        $adress_str = $_SERVER['REQUEST_URI'];

        if($_SERVER['QUERY_STRING']) {
            $adress_str = substr($adress_str, 0, strpos($adress_str, $_SERVER['QUERY_STRING']) - 1);
        }

        # в переменную path сохранили обрезанную строку в которой содержиться имя выполнения скрипта
        # имя скрипта, который выполняет код
        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));

        # $class = new NClass();

        if($path == PATH) {

        # -1 так как порядковый символ равен 9
        # cервер по умлочанию подставлет слеш в корневой каталог
        # если слеш стоит в конце строки и это не корень сайта
        if(strrpos($adress_str, '/') == strlen($adress_str) - 1 &&
            strrpos($adress_str, '/') !== strlen(PATH) - 1) {
            # rtrim - обраезает пробелы а также символы в конце строки
            # 301 - код ответа сервера
            # переправляем пользователя на ссылку без символа /
            $this->redirect(rtrim($adress_str, '/'), 301);
        }

         $this->routes = Settings::get('routes');
         if(!$this->routes) throw new RouteException('Отсутсвуют маршуруты в базовых настройках', 1);

         $url = explode('/', substr($adress_str, strlen(PATH)));

         # если это административная панель
         if($url[0] && $url[0] === $this->routes['admin']['alias']) {

             array_shift($url); ## удаляем нулевой элемент admin

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

                 $this->controllers = $this->routes['plugins']['path'] . $plugin . $dir;

                 $hrUrl = $this->routes['plugins']['hrUrl'];
                 $route = 'plugins';

             } else {
                 $this->controllers = $this->routes['admin']['path'];
                 $hrUrl = $this->routes['admin']['hrUrl'];
                 $route = 'admin';
             }


         } else { # если пользовательская часть

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

        } else {
            throw new RouteException('Некорректная дериктория сайта', 1);
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

}