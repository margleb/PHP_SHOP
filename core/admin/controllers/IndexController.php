<?php


namespace core\admin\controllers;
use core\admin\models\Model;
use core\base\controllers\BaseController;

class IndexController extends BaseController
{
    protected function inputData() {

        $db = Model::instance();

        $query = "SELECT name1 FROM articles";
        $res = $db->query($query);

        exit('I am admin panel');
    }

}