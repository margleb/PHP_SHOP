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
            'order' => [1, 'name'],
            'order_direction' => ['DESC'],
            'limit' => '1',
            'join' => [
                [
                    'table' => 'join_table1',
                    'fields' => ['id as j_id', 'name as j_name'],
                    'type' => 'left',
                    'where' => ['name' => 'sasha'],
                    'operand' => ['='],
                    'condition' => ['OR'],
                    'on' => [
                       'table' => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ]
                ],
                'join_table2' => [
                    'table' => 'join_table2',
                    'fields' => ['id as j2_id', 'name as j2_name'],
                    'type' => 'left',
                    'where' => ['name' => 'sasha'],
                    'operand' => ['<>'],
                    'condition' => ['AND'],
                    'on' => [
                        'table' => 'teachers',
                        'fields' => ['id', 'parent_id']
                    ]
                ]
            ]
        ]);


        exit('I am admin panel');
    }

}