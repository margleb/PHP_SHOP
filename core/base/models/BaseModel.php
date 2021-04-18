<?php

namespace core\base\models;

use core\base\exceptions\DbException;

abstract class BaseModel extends BaseModelMethods
{


    protected $db;

    protected function connect()
    {
        $this->db = @new \mysqli(HOST, USER, PASS, DB_NAME);

        if($this->db->connect_error) {
            throw new DbException('Ошибка подключения к базе данных: ' . $this->db->connect_errno . '  ' . $this->db->connect_error);
        }

        $this->db->query('SET NAMES UTF8');
    }

    /**
     * @param $query
     * @param string $crud - r - SELECT / c - INSERT / u - UPDATE / d - DELETE
     * @param bool $return_id
     * @return array|bool|mixed
     * @throws DbException
     */
    final public function query($query, $crud = 'r', $return_id = false) {

        $result = $this->db->query($query);

        # -1 это ошибка сервера
        if($this->db->affected_rows == -1) {
            // throw new DbException('Ошибка в SQL запросе: ' . $query . ' - ' . $this->db->errno . ' ' . $this->db->error);
        }

        switch($crud) {
            case 'r':

                if($result->num_rows) {

                    $res = [];

                    for($i = 0; $i < $result->num_rows; $i++) {
                        $res[] = $result->fetch_assoc(); // возращает массив каждого ряда выборки
                    }

                    return $res;
                }

                return false;

                break;

            case 'c':

                if($return_id) return $this->db->insert_id;

                return true;

                break;

            default:

                return true;

                break;

        }

    }

    /**
     * @param $table - Таблица базы данных
     * @param array $set
     * 'fields' => ['id', 'name'],
     * 'where' => ['fio' => 'smirnova', 'name' => 'Masha', 'surname' => 'Sergeeva'],
     * 'operand' => ['=', '<>'], # <> не равно
     * 'condition' => ['AND'],
     * 'order' => ['fio', 'name'],
     * 'order_direction' => ['ASC', 'DESC'],
     * 'limit' => '1'
     */

    final public function get($table, $set = []) {

        $fields = $this->createFields($set, $table);

        $order = $this->createOrder($set, $table);

        $where = $this->createWhere($set, $table);

        if(!$where) $new_where = true;
        else $new_where = false;

        $join_arr = $this->createJoin($set, $table, $new_where);

        $fields .= $join_arr['fields'];
        $join = $join_arr['join'];
        $where .= $join_arr['where'];

        $fields = rtrim($fields, ', ');


        $limit = $set['limit'] ? 'LIMIT ' . $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        // exit($query);

        $res = $this->query($query);

        // существует ли флаг стркутрирования данных
        if(isset($set['join_structure']) && $set['join_structure'] && $res) {

            $res = $this->joinStructure($res, $table);

        }

        return $res;

    }


    final public function add($table, $set = []) {

        $set['fields'] = (is_array($set['fields']) && !empty($set['fields'])) ? $set['fields'] : $_POST;
        $set['files'] = (is_array($set['files']) && !empty($set['files'])) ? $set['files'] : false;

        if(!$set['fields'] && !$set['files']) return false;

        $set['return_id'] = $set['return_id'] ? true : false;
        // исключает данные эементы массива из добавления в запрос
        $set['except'] = (is_array($set['except']) && !empty($set['except'])) ? $set['except'] : false;

        $insert_arr = $this->createInsert($set['fields'], $set['files'], $set['except']);

        $query = "INSERT INTO $table {$insert_arr['fields']} VALUES {$insert_arr['values']}";
        return $this->query($query, 'c', $set['return_id']);
    }


    final public function edit($table, $set = []) {

        $set['fields'] = (is_array($set['fields']) && !empty($set['fields'])) ? $set['fields'] : $_POST;
        $set['files'] = (is_array($set['files']) && !empty($set['files'])) ? $set['files'] : false;

        if(!$set['fields'] && !$set['files']) return false;

        // исключает данные эементы массива из добавления в запрос
        $set['except'] = (is_array($set['except']) && !empty($set['except'])) ? $set['except'] : false;

        if(!$set['all_rows']) {
            if ($set['where']) {
                $where = $this->createWhere($set);
            } else {
                $columns = $this->showColumns($table);
                if (!$columns) return false;

                if ($columns['id_row'] && $set['fields'][$columns['id_row']]) {
                    $where = 'WHERE ' . $columns['id_row'] . '=' . $set['fields'][$columns['id_row']];
                    unset($set['fields'][$columns['id_row']]);
                }
            }
        }
        $update = $this->createUpdate($set['fields'], $set['files'], $set['except']);

        $query = "UPDATE $table SET $update $where";

        return $this->query($query, 'u');

    }


    public function delete($table, $set = []) {
        $table = trim($table);
        $where = $this->createWhere($set, $table);
        $columns = $this->showColumns($table);
        if(!$columns) return false;

        if(is_array($set['fields']) && !empty($set['fields'])) {
                if($columns['id_row']) {
                    $key = array_search($columns['id_row'], $set['fields']);
                    if($key !== false) unset($set['fields'][$key]);
                }

                $fields = [];

                foreach($set['fields'] as $field) {
                    $fields[$field] = $columns[$field]['Default'];
                }

                $update = $this->createUpdate($fields, false, false);

                $query = "UPDATE $table SET $update $where";

         } else {

            $join_arr = $this->createJoin($set, $table);
            $join = $join_arr['join'];
            $join_tables = $join_arr['tables'];

            $query = 'DELETE ' . $table . $join_tables . ' FROM ' . $table . ' ' . $join . ' ' . $where;

        }

        return $this->query($query, 'u');

    }


    final public function showColumns($table) {

        if(!isset($this->tableRows[$table]) || $this->tableRows[$table]) {

            $checkTable = $this->createTableAlias($table);


            if($this->tableRows[$checkTable['table']]) {

                return $this->tableRows[$checkTable['alias']] = $this->tableRows[$checkTable['table']];

            }

            $query = "SHOW COLUMNS FROM {$checkTable['table']}";
            $res = $this->query($query);

            $this->tableRows[$checkTable['table']] = [];

            if($res) {
                foreach($res as $row) {

                    $this->tableRows[$checkTable['table']][$row['Field']] = $row; // ассоциативный массив


                    if($row['Key'] === 'PRI') {

                        if(!isset($this->tableRows[$checkTable['table']]['id_row'])) {
                            $this->tableRows[$checkTable['table']]['id_row'] = $row['Field'];
                        } else {
                            if(!isset($this->tableRows[$checkTable['table']]['multi_id_row'])) {
                                $this->tableRows[$checkTable['table']]['multi_id_row'][] = $this->tableRows[$checkTable['table']]['id_row'];
                            }
                            $this->tableRows[$checkTable['table']]['multi_id_row'][] = $row['Field'];
                        }
                    }
                }
            }
        }

        if(isset($checkTable) && $checkTable['table'] !== $checkTable['alias']) {

            return $this->tableRows[$checkTable['alias']] = $this->tableRows[$checkTable['table']];

        }

        return $this->tableRows[$table];
    }

    final public function showTables() {
        $query = 'SHOW TABLES';

        $tables = $this->query($query);

        $table_arr = [];

        if($tables) {
            foreach($tables as $table) {
                $table_arr[] = reset($table);
            }
        }

        return $table_arr;

    }

}