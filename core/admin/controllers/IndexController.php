<?php


namespace core\admin\controllers;
use core\admin\models\Model;
use core\base\controllers\BaseController;

class IndexController extends BaseController
{
    protected function inputData() {

        $db = Model::instance();

        $table = 'teachers';

//        for($i = 0; $i < 8; $i++) {
//            $s_id = $db->add('students', [
//                'fields' => ['name' => 'student - ' . $i, 'content' => 'content - ' . $i],
//                'return_id' => true
//            ]);
//            $db->add('teachers', [
//                'fields' => ['name' => 'teacher - ' . $i, 'content' => 'content - ' . $i, 'student_id' => $s_id],
//                'return_id' => true
//            ]);
//        }

//        $query = "DELETE category, products FROM category
//        LEFT JOIN products ON category.id = products.parent_id
//        WHERE id = 1";

        $res = $db->delete($table, [
            // 'fields' => ['id', 'name', 'img'],
            'where' => ['id' => 5],
            'join' => [
                'students' => [
                    'table' => 'students',
                    'on' => ['student_id', 'id']
                ]
            ]
        ]);

        // exit('id =' .  $res['id'] . ' Name = ' . $res['name']);
    }

}