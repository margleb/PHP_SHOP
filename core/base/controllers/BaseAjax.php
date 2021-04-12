<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 10.04.2021
 * Time: 14:41
 */

namespace core\base\controllers;


use core\base\settings\Settings;

class BaseAjax extends BaseController
{
    public function route() {

        $route = Settings::get('routes');

        $controller = $route['user']['path']. 'AjaxController';

        $data = $this->isPost() ? $_POST : $_GET;

        // 2. Какой контроллер подключать? (пользовательский или административный)
        if(isset($data['ADMIN_MODE'])) {
            unset($data['ADMIN_MODE']);
            $controller = $route['admin']['path']. 'AjaxController';
        }

        $controller = str_replace('/', '\\', $controller);
        $ajax = new $controller;

        $ajax->createAjaxData($data);

        return ($ajax->ajax());

    }

    protected function createAjaxData($data) {
        $this->data = $data;
    }
}