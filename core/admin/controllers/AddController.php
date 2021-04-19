<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 15.03.2021
 * Time: 13:51
 */

namespace core\admin\controllers;


use core\base\settings\Settings;

class AddController extends BaseAdmin
{

    protected $action = 'add'; // свой-во определяющее добавление либо редактирование

    protected function inputData() {

        if(!$this->userId) $this->execBase();

        $this->checkPost(); // проверяем пришло ли что нибудь в post

        $this->createTableData();

        $this->createForeignData();

        $this->createMenuPosition();

        $this->createRadio();

        $this->createOutputData();

        $this->createManyToMany();

//        $this->data = [
//            'name' => 'Masha',
//            'keywords' => 'Ключевики',
//            'img' => 'kitten.jpg',
//            'gallery_img' => json_encode(['kitten.jpg', 'kitten_2.jpg']),
//        ];

    }

    protected function createForeignProperty($arr, $rootItems) {

        if(in_array($this->table, $rootItems['tables'])) {
            $this->foreignData[$arr['COLUMN_NAME']][0]['id'] = 0;
            $this->foreignData[$arr['COLUMN_NAME']][0]['name'] = $rootItems['name'];
        }


        $orderData = $this->createOrderData($arr['REFERENCED_TABLE_NAME']);


        if($this->data) {
            if($arr['REFERENCED_TABLE_NAME'] === $this->table) {
                $where[$this->columns['id_row']] = $this->data[$this->columns['id_row']];
                $operand[] = '<>';
            }
        }

        $foreign = $this->model->get($arr['REFERENCED_TABLE_NAME'], [
            'fields' => [$arr['REFERENCED_COLUMN_NAME'] . ' as id', $orderData['name'], $orderData['parent_id']],
            'where' => $where,
            'operand' => $operand,
            'order' => $orderData['order']
        ]);

        if($foreign) {
            if($this->foreignData[$arr['COLUMN_NAME']]) {
                foreach($foreign as $value) {
                    $this->foreignData[$arr['COLUMN_NAME']][] = $value;
                }
            } else {
                $this->foreignData[$arr['COLUMN_NAME']] = $foreign;
            }
        }

    }


    protected function createForeignData($settings = false) {

        if(!$settings) $settings = Settings::instance();

        $rootItems = $settings::get('rootItems');

        $keys = $this->model->showForeignKeys($this->table);

        if($keys) {
            foreach($keys as $item) {
                $this->createForeignProperty($item, $rootItems);
            }
        } elseif ($this->columns['parent_id']) {

            $arr['COLUMN_NAME'] = 'parent_id';
            $arr['REFERENCED_COLUMN_NAME'] = $this->columns['id_row'];
            $arr['REFERENCED_TABLE_NAME'] = $this->table;

            $this->createForeignProperty($arr, $rootItems);
        }
        return;
    }

    protected function createMenuPosition($settings = false) {

        if($this->columns['menu_position']) {
            if(!$settings) $settings = Settings::instance();
            $rootItems = $settings::get('rootItems');
            if($this->columns['parent_id']) {
                if(in_array($this->table, $rootItems['tables'])) {
                    $where = 'parent_id IS NULL OR parent_id = 0';
                } else {
                    $parent = $this->model->showForeignKeys($this->table, 'parent_id')[0];
                    if($parent) {

                        if($this->table === $parent['REFERENCED_TABLE_NAME']) {
                            $where = 'parent_id IS NULL OR parent_id = 0';
                        } else {
                            $columns = $this->model->showColumns($parent['REFERENCED_TABLE_NAME']);
                            if($columns['parent_id']) $order[] = 'parent_id';
                            else $order[] = $parent['REFERENCED_COLUMN_NAME'];

                            $id = $this->model->get($parent['REFERENCED_TABLE_NAME'], [
                                'fields' => [$parent['REFERENCED_COLUMN_NAME']],
                                'order' => $order,
                                'limit' => '1'
                            ])[0][$parent['REFERENCED_COLUMN_NAME']];

                            if($id) $where = ['parent_id' => $id];
                        }
                    } else {
                        $where = 'parent_id IS NULL OR parent_id = 0';
                    }
                }
            }

            $menu_pos = $this->model->get($this->table, [
               'fields' => ['COUNT(*) as count'],
               'where' => $where,
               'no_concat' => true
            ])[0]['count'] + 1;

            for($i = 1; $i <= $menu_pos; $i++) {
                $this->foreignData['menu_position'][$i - 1]['id'] = $i;
                $this->foreignData['menu_position'][$i - 1]['name'] = $i;
            }

        }

        return; /* 00.35.58 */
    }

}