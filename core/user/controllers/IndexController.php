<?php

namespace core\user\controllers;
use core\base\controllers\BaseController;
use core\base\models\Crypt;
use core\admin\models\Model;

class IndexController extends BaseController {

    protected $name;

    protected function inputData() {

    //  $str = '1234567890abcde';
    //  $en_str = Crypt::instance()->encrypt($str);
    //  $dec_str = Crypt::instance()->decrypt($en_str);

     $model  = Model::instance();

     // $arr['fields'] = false;
     // $a = isset($arr['fields']) ? 1 : 2;

     $res = $model->get('goods', [
         'where' => ['id' => '27, 28'],
         'operand' => ['IN'],
         'join' => [
             'goods_filters' => [
                 'fields' => null,
                 'on' => ['id', 'teachers']],
             'filters f' => [
                 'fields' => ['name as student_name'],
                 'on' => ['students', 'id']
             ],
             [
                 'table' => 'filters',
                 'on' => ['parent_id', 'id']
             ]
         ],
         // 'join_structure' => true,
         'order' => ['id'],
         'order_direction' => ['DESC']
     ]);

     exit;
    
    }
}