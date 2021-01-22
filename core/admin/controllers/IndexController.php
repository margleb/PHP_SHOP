<?php


namespace core\admin\controllers;
use core\admin\models\Model;
use core\base\controllers\BaseController;

class IndexController extends BaseController
{
    protected function inputData() {

        $db = Model::instance();

        $table = 'teachers';

        $color = ['red', 'blue', 'black'];

        $res = $db->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['name' => 'masha', 'surname' => 'Sergeevna', 'fio' => 'Andrey', 'car' => 'Porshe', 'color' => $color],
            'operand' => ['IN', 'LIKE%', '<>', '=', 'NOT IN'], # <> не равно
            'condition' => ['AND', 'OR'],
            'order' => ['fio', 'name'],
            'order_direction' => ['DESC'],
            'limit' => '1'
        ]);


        exit('I am admin panel');
    }

}