<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 02.04.2021
 * Time: 11:04
 */

namespace core\admin\controllers;


class EditController extends BaseAdmin
{
    protected function inputData() {
        if(!$this->userId) $this->execBase();
    }


    protected function checkOldAlias($id) {

        $tables = $this->model->showTables();

        if(in_array('old_alias', $tables)) {

            $old_alias = $this->model->get($this->table, [
               'fields' => ['alias'],
               'where' =>  [$this->columns['id_row'] => $id]
            ])[0]['alias'];


            // если в таблице уже храниться этот alias
            if($old_alias && $old_alias !== $_POST['alias']) {
                $this->model->delete('old_alias', [
                   'where' => ['alias' => $old_alias, 'table_name' => $this->table]
                ]);

                // удаляем его из старой таблицы
                $this->model->delete('old_alias', [
                    'where' => ['alias' => $old_alias, 'table_name' => $this->table]
                ]);

                // а также удаляем (если есть) alias из поста
                $this->model->delete('old_alias', [
                    'where' => ['alias' => $_POST['alias'], 'table_name' => $this->table]
                ]);

                // заносим старую ссылку в old_tables
                $this->model->add('old_alias', [
                    // индентифактор нужен для того, чтобы 2 одинкаовых alias имели развличие в индентифиакторе
                   'fields' => ['alias' => $old_alias, 'table_name' => $this->table, 'table_id' => $id]
                ]);
            }
        }

    }

}