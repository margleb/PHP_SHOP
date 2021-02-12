<?php


namespace core\admin\controllers;
use core\admin\models\Model;
use core\base\controllers\BaseController;

class IndexController extends BaseController
{
    protected function inputData() {

        $db = Model::instance();

        $table = 'teachers';

        // $color = ['red', 'blue', 'black'];
        // $c =  json_encode($color);
        // $c =  json_encode($table);
        // echo $c . '<br>';
        // exit(print_arr(json_decode($c)));


        $files['gallery_img'] = [''];
        $files['img'] = '';


        $_POST['id'] = 6;
        $_POST['name'] = '';
        $_POST['content'] = "<p>New' book1<p>";


        $res = $db->edit($table
        // ['fields' => ['id' => 2, 'name' => 'Pasha'],
        // 'where' => ['id' => 1]]
        );

        exit('id =' .  $res['id'] . ' Name = ' . $res['name']);
    }

}