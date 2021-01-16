<?php

namespace core\user\controllers;
use core\base\controllers\BaseController;

class IndexController extends BaseController {

    protected $name;

    protected function inputData() {

        $name = 'Ivan';

        $content = $this->render('', compact('name'));
        $header = $this->render(TEMPLATE .'header');
        $footer = $this->render(TEMPLATE .'footer');

        # возрващает массив с переменными
        return compact('header', 'content', 'footer');
    }

//    protected function outputData() {
//        $vars = func_get_arg(0);
//
//        $this->page =  $this->render(TEMPLATE . 'templater', $vars);
//    }

}