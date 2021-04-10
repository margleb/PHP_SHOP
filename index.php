<?php

# константа безопсаности
define('VG_ACCESS', true);

# указываем браузеру пользователя в какой кодировки будем отправлять данные
# отправляются до вывода на экран чего либо
header('Content-Type:text/html; charset=utf-8');
session_start(); # суперглобальный массив сессии (файлы на стороне сервера)

// error_reporting(0); // отключение вывода об ошибка (для php 7.2)

require_once 'config.php'; # базовые настройки для быстрого развертывания на хостинге
require_once 'core/base/settings/internal_settings.php';
require_once 'libraries/function.php';

use core\base\exceptions\RouteException;
use core\base\controllers\BaseRoute;
use core\base\exceptions\DbException;

// $s = \core\base\settings\Settings::instance();
// $s1 = \core\base\settings\ShopSettings::instance();
// exit;

try {
    BaseRoute::routeDirection();
} catch(RouteException $e) {
    exit($e->getMessage());
} catch(DbException $e) {
    exit($e->getMessage());
}
