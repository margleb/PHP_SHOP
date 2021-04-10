<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 10.04.2021
 * Time: 14:37
 */

namespace core\base\controllers;


class BaseRoute
{
    use Singleton, BaseMethods;

    public static function routeDirection() {

        //1 Асинхронный ли запрос или нет
        if(self::instance()->isAjax()) {
            exit((new BaseAjax())->route());
        }
        RouteController::instance()->route();
    }

}