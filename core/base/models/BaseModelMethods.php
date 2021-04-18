<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 10.02.2021
 * Time: 18:44
 */

namespace core\base\models;


abstract class BaseModelMethods
{

    protected $sqlFunc = ['NOW()'];
    protected $tableRows;

    protected function createFields($set, $table = false, $join = false) {

        $fields = '';

        $join_structure = false;

        if(($join || isset($set['join_structure']) && $set['join_structure']) && $table) {
            $join_structure = true;
            $this->showColumns($table);

            if(isset($this->tableRows[$table]['multi_id_row'])) $set['fields'] = [];
        }

        $concat_table = $table && !$set['concat'] ? $table . '.' : '';

        if(!isset($set['fields']) || !is_array($set['fields']) || !$set['fields']) {

            if(!$join) {
                $fields = $concat_table . '*,';
            } else {
                foreach($this->tableRows[$table] as $key => $item) {
                    if($key !== 'id_row' && $key !== 'multi_id_row') {
                        $fields .= $concat_table . $key . ' as TABLE' . $table . 'TABLE_' . $key . ',';
                    }
                }
            }

        } else {
            $id_field = false;
            foreach($set['fields'] as $field) {
                if($join_structure && !$id_field && $this->tableRows[$table] === $field) {
                    $id_field = true;
                }
                if($field) {
                    if($join && $join_structure) {
                        if(preg_match('/^(.+)?\s+as\s+(.+)/i', $field, $matches)) {
                            $fields .= $concat_table . $matches[1] . ' as TABLE' . $table . 'TABLE_' . $matches[2] . ',';
                        } else {
                            $fields .= $concat_table . $field . ' as TABLE' . $table . 'TABLE_' . $field . ',';
                        }

                    } else {
                        $fields .= $concat_table . $field . ',';
                    }
                }
            }
            if(!$id_field && $join_structure) {

                if($join) {
                    $fields .= $concat_table . $this->tableRows[$table]['id_row'] . ' as TABLE' . $table . 'TABLE_' .  $this->tableRows[$table]['id_row'] . ',';

                } else {

                    $fields .= $concat_table . $this->tableRows[$table]['id_row'] . ',';

                }

            }
        }

        return $fields;

    }


    protected function createOrder($set, $table = false) {

        $table = ($table && !$set['no_concat']) ? $table . '.' : '';

        $order_by = '';

        if(is_array($set['order']) && !empty($set['order'])) {
            $set['order_direction'] = (is_array($set['order_direction']) && !empty($set['order_direction']))
                ? $set['order_direction'] : ['ASC'];

            $order_by = 'ORDER BY ';
            $direct_count = 0;

            foreach($set['order'] as $order) {
                if($set['order_direction'][$direct_count]) {
                    $order_direction = strtoupper($set['order_direction'][$direct_count]);
                    $direct_count++;
                } else {
                    $order_direction = strtoupper($set['order_direction'][$direct_count - 1]);
                }

                if(is_int($order)) $order_by .= $order . ' ' . $order_direction . ',';
                else $order_by .= $table . $order . ' ' . $order_direction . ',';
            }

            $order_by = rtrim($order_by, ',');
        }

        return $order_by;

    }

    protected function createWhere($set, $table = false, $instruction = 'WHERE ') {

        $table = ($table && !$set['no_concat']) ? $table . '.' : '';

        $where = '';

        if(is_string($set['where'])) {
            return $instruction . ' ' . trim($set['where']);
        }

        if(is_array($set['where']) && !empty($set['where'])) {
            $set['operand'] = (is_array($set['operand']) && !empty($set['operand'])) ? $set['operand'] : ['='];
            $set['condition'] = (is_array($set['condition']) && !empty($set['condition'])) ? $set['condition'] : ['AND'];

            $where = $instruction;

            $o_count = 0;
            $c_count = 0;

            foreach($set['where'] as $key => $item) {

                $where .= ' ';

                if($set['operand'][$o_count]) {
                    $operand = $set['operand'][$o_count];
                    $o_count++;
                } else {
                    $operand = $set['operand'][$o_count - 1];
                }

                if($set['condition'][$c_count]) {
                    $condition = $set['condition'][$c_count];
                    $c_count++;
                } else {
                    $condition = $set['condition'][$c_count - 1];
                }

                if($operand === 'IN' || $operand == 'NOT IN') {

                    if(is_string($item) && strpos($item, 'SELECT') === 0) {
                        $in_str = $item;
                    } else {
                        if(is_array($item)) $temp_item = $item;
                        else $temp_item = explode(', ', $item);

                        $in_str = '';

                        foreach($temp_item as $v) {
                            $in_str .= "'" . addslashes(trim($v)) . "',";
                        }
                    }

                    $where .= $table . $key . ' ' . $operand . ' (' .  trim($in_str, ',') . ') ' . $condition;

                } else if(strpos($operand, 'LIKE') !== false) {

                    $like_template = explode('%', $operand);

                    foreach($like_template as  $it_key => $it) {
                        if(!$it) {
                            if(!$it_key) {
                                $item = '%'.$item;
                            } else {
                                $item .= '%';
                            }
                        }
                    }
                    $where .= $table . $key . ' LIKE ' . "'" . addslashes($item) . "' $condition";

                } else {
                    if(strpos($item, 'SELECT') === 0) {
                        $where .= $table . $key . $operand . '(' . $item . ") $condition";
                    } else {
                        $where .= $table . $key . $operand . "'" . addslashes($item) . "' $condition";
                    }
                }

            }
            $where = substr($where, 0, strrpos($where, $condition));
        }

        return $where;

    }

    protected function createJoin($set, $table, $new_where = false) {

        $fields = '';
        $join = '';
        $where = '';

        if($set['join']) {
            $join_table = $table;
            foreach($set['join'] as $key => $item) {
                if(is_int($key)) {
                    if(!$item['table']) continue;
                    else $key = $item['table'];
                }
                if($join) $join .= ' ';
                if($item['on']) {
                    $join_fields = [];

                    switch(2) {
                        // фикс для php 7.2
                        case (is_array($item['on']['fields']) && count($item['on']['fields'])):
                            $join_fields = $item['on']['fields'];
                            break;
                        case (is_array($item['on']) && count($item['on'])):
                            $join_fields = $item['on'];
                            break;
                        default:
                            continue 2;
                            break;
                    }

                    if(!$item['type']) $join .= 'LEFT JOIN ';
                    else $join .= trim(strtoupper($item['type'])) . ' JOIN ';

                    $join .= $key . ' ON ';

                    if($item['on']['table']) $join .= $item['on']['table'];
                    else $join .= $join_table;

                    $join .= '.' . $join_fields[0] . '=' . $key . '.' . $join_fields[1];

                    $join_table = $key;

                    if($new_where) {
                        if($item['where']) {
                            $new_where = false;
                        }
                        $group_condition = 'WHERE';
                    } else {
                        $group_condition = $item['group_condition'] ? strtoupper($item['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($item, $key, $set['join_structure']);
                    $where .=  $this->createWhere($item, $key, $group_condition);

                }
            }
        }

        return compact('fields', 'join', 'where');

    }

    protected function createInsert($fields, $files, $except) {

        $insert_arr = [];

        $insert_arr['fields'] = '(';

        $array_type = array_keys($fields)[0];

        // если множественная вставка
        if(is_int($array_type)) {

            $check_fields = false; // предотвращает появление ненужных колонок
            $count_fields = 0; // предотвращает появление ненужных полей

             foreach ($fields as $i => $item) {

                $insert_arr['values'] .= '(';

                if(!$count_fields) $count_fields = count($fields[$i]); // будем равнятся на это кол-во эментов в массиве

                $j = 0; // счетчик подсчитывает кол-во элементов в массиве

                foreach($item as $row => $value) {
                    if($except && in_array($row, $except)) continue;

                    if(!$check_fields) $insert_arr['fields'] .= $row . ',';

                    if(in_array($value, $this->sqlFunc)) {
                        $insert_arr['values'] .= $value . ',';
                    } else if($value == 'NULL' || $value === NULL) {
                        $insert_arr['values'] .= "NULL" . ',';
                    } else {
                        $insert_arr['values'] .= "'" . addslashes($value) . "',";
                    }

                    $j++;


                    if($j == $count_fields) break; // ломаем цикл если количество полей больше чем должно быть

                }

                if($j < $count_fields) { // если количество значений меньше, то добиваем их пустыми
                    for(; $j < $count_fields; $j++) {
                        $insert_arr['values'] .= "NULL" . ',';
                    }
                }


                $insert_arr['values'] = rtrim($insert_arr['values'], ',') . '),';

                if(!$check_fields) $check_fields = true; // уже заполнили fields, поэтому больше не нужно

                //35.56
            }

        } else {

            $insert_arr['values'] = '(';

            if($fields) {
                foreach($fields as $row => $value) {
                    if($except && in_array($row, $except)) continue;

                    $insert_arr['fields'] .= $row . ',';

                    if(in_array($value, $this->sqlFunc)) {
                        $insert_arr['values'] .= $value . ',';
                    } else if($value == 'NULL' || $value === NULL) {
                        $insert_arr['values'] .= "NULL" . ',';
                    } else {
                        $insert_arr['values'] .= "'" . addslashes($value) . "',";
                    }

                }
            }
            if($files) {
                foreach($files as $row => $file) {

                    $insert_arr['fields'] .= $row . ',';

                    if(is_array($file)) $insert_arr['values'] .= "'" . addslashes(json_encode($file)) . "',";
                    else $insert_arr['values'] .= "'" . addslashes($file) . "',";
                }
            }


            $insert_arr['values'] = rtrim($insert_arr['values'], ',') . ')';

        }


        $insert_arr['fields'] = rtrim($insert_arr['fields'], ',') . ')';
        $insert_arr['values'] = rtrim($insert_arr['values'], ',');

        return $insert_arr;
    }


    protected function createUpdate($fields, $files, $except) {

        $update = '';

        if($fields) {
            foreach($fields as $row => $value) {
                if($except && in_array($row, $except)) continue;

                $update .= $row . '=';

                if(in_array($value, $this->sqlFunc)) {
                    $update .= $value . ',';
                } elseif($value == NULL) {
                    $update .= "NULL" . ',';
                } else {
                    $update .= "'" . addslashes($value) . "',";
                }

            }
        }

        if($files) {
            foreach($files as $row => $file) {

                $update .= $row . '=';

                if(is_array($file)) $update .= "'" . addslashes(json_encode($file)) . "',";
                else $update .= "'" . addslashes($file) . "',";

            }
        }

        return rtrim($update, ',');

    }

    protected function joinStructure($res, $table) {

        $join_arr = [];

        $id_row = $this->tableRows[$table]['id_row'];

        foreach($res as $value) {
            if($value) {

                if(!isset($join_arr[$value[$id_row]])) $join_arr[$value[$id_row]] = [];

                foreach($value as $key => $item) {

                    // 2. Если это уникальный ключ
                    if(preg_match('/TABLE(.+)?TABLE/u', $key, $matches)) {

                        $table_name_normal = $matches[1];

                        // 4. создаем уникальный ключ в зависимости от составной он или нет
                        if(!isset($this->tableRows[$table_name_normal]['multi_id_row'])) {

                            $join_id_row = $value[$matches[0] . '_' . $this->tableRows[$table_name_normal]['id_row']];

                        } else {

                            $join_id_row = '';

                            foreach($this->tableRows[$table_name_normal]['multi_id_row'] as $multi) {

                                $join_id_row .= $value[$matches[0] . '_' . $multi];

                            }

                        }

                        $row = preg_replace('/TABLE(.+)TABLE_/u', '', $key);

                        if($join_id_row && !isset($join_arr[$value[$id_row]]['join'][$table_name_normal][$join_id_row][$row])) {
                            // 3. создаем join вложенность
                            $join_arr[$value[$id_row]]['join'][$table_name_normal][$join_id_row][$row] = $item;

                        }

                        continue;

                    }


                    // 1. Вкладываем индентитификатор и (ключ - значение)
                    $join_arr[$value[$id_row]][$key] = $item;

                }
            }
        }

        return $join_arr;
    }

}