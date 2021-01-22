<?php


namespace core\admin\controllers;
use core\admin\models\Model;
use core\base\controllers\BaseController;

class IndexController extends BaseController
{
    protected function inputData() {

        $db = Model::instance();

        /* ОДИН КО МНОГИМ */

        # вложенный запрос
        // $query = "SELECT id, name FROM product WHERE parent_id = ( SELECT id FROM category WHERE name='Apple')";

        # с помочью одной инструкции LeftJoin
        // $query = "SELECT product.id, product.name FROM product LEFT JOIN category ON product.parent_id = category.id WHERE category.id = 1";
        // $query = "SELECT category.id, category.name, product.id as p_id, product.name as p_name FROM product LEFT JOIN category ON product.parent_id = category.id";

        /* МНОГИЕ КО МНОГИМ */
        $query = "SELECT teachers.id, teachers.name, students.id as s_id, students.name as s_name 
        FROM teachers 
        LEFT JOIN stud_teach ON teachers.id = stud_teach.teachers
        LEFT JOIN students ON stud_teach.students = students.id
        ";

        $res = $db->query($query);

        exit('I am admin panel');
    }

}