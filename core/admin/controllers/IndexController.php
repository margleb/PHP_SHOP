<?php


namespace core\admin\controllers;
use core\admin\models\Model;
use core\base\controllers\BaseController;

class IndexController extends BaseController
{
    protected function inputData() {

        $db = Model::instance();

        $table = 'teachers';

        $res = $db->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['name' => 'Masha', 'olya', 'sveta', 'name' => 'Masha', 'surname' => 'Sergeeva'],
            'operand' => ['IN', '<>'], # <> не равно
            'condition' => ['AND'],
            'order' => ['fio', 'name'],
            'order_direction' => ['DESC'],
            'limit' => '1'
        ]);


        exit('I am admin panel');
    }

}