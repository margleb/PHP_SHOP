<?php


# если константа не определена, то доступ запрещен
defined('VG_ACCESS') or die('Access denied');

# через ключевое слово const в константах можно хранить массивы

const TEMPLATE = 'templates/default/'; # шаблоны пользовательской части сайта
const ADMIN_TEMPLATE = 'core/admin/views/'; # шаблоны административной части сайта

const COOKIE_VERSION = '1.0.0'; # версия cookie файлов
const CRYPT_KEY = ''; # ключ алгоритма шифоравания
const COOKIE_TIME = 60; # время бездействия пользователя
const BLOCK_TIME = 3; # время блокировки пользователя, попытавшего подобрать пароль

# постраничная навигация
const QTY = 8; # количество отображения товаров на странице
const QTY_LINKS = 3; # количество ссылок постраничной навигации

# пути к css и javascript файлам административной части сайта
const ADMIN_CSS_JS = [
    'styles' => [],
    'scripts' => []
];

# пути к css и javascript файлам пользовательской части сайта
const USER_CSS_JS = [
    'styles' => ['css/style.css'],
    'scripts' => []
];

# импорт пространства имен
use core\base\exceptions\RouteException;

// функция автоматической загрузки классов
function autoloadMainClasses($class_name) {
    $class_name = str_replace('\\', '/', $class_name);
    // @ - блкирует вывод всяких ошибок, генерируемых include_once
    if(!@include_once $class_name . '.php') {
        throw new \Exception('Неверное имя файла или подключения - '.$class_name);
    }
}

// регистрирует очередь загрузки классов
spl_autoload_register('autoloadMainClasses');