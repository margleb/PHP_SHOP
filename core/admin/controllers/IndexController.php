<?php


namespace core\admin\controllers;
use core\admin\models\Model;
use core\base\controllers\BaseController;
use core\base\settings\Settings;

class IndexController extends BaseController
{
    protected function inputData() {

        $reditect = PATH . Settings::get('routes')['admin']['alias'] . '/show';
        $this->redirect($reditect);

    }

}