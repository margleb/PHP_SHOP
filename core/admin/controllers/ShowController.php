<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 15.02.2021
 * Time: 11:20
 */

namespace core\admin\controllers;


class ShowController extends BaseAdmin
{
    protected function inputData() {

        $this->execBase();

        $this->createTableData();

        $this->createData();

        return $this->expansion(get_defined_vars());

    }

    protected function outputData() {

    }
}