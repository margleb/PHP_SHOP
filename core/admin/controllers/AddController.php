<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 15.03.2021
 * Time: 13:51
 */

namespace core\admin\controllers;


class AddController extends BaseAdmin
{
    protected function inputData() {

        if(!$this->userId) $this->execBase();

        $this->createTableData();

        $this->createOutputData();
    }


}