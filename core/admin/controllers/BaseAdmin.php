<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 15.02.2021
 * Time: 11:21
 */

namespace core\admin\controllers;
use core\base\controllers\BaseController;
use core\admin\models\Model;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseAdmin extends BaseController
{
    protected $model;

    protected $table;
    protected $columns;

    protected $menu;
    protected  $title;

    protected function inputData() {
        $this->init(true);

        $this->title = 'VG engine';

        if(!$this->model) $this->model = Model::instance();
        if(!$this->menu) $this->menu = Settings::get('projectTables');

        $this->sendNoCacheHeaders();

    }


    protected function outputData() {

    }

    protected function sendNoCacheHeaders() {
        header("Last-Modified: " . gmdate("D, d m Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: max-age=0");
        header("Cache-Control: post-check=0, pre-check=0");
        header("Expires: post-check=0, pre-check=0");
    }

    protected function exectBase() {
        self::inputData();
    }


    protected function createTableData() {
        if(!$this->table) {
            if($this->parameters) $this->table = array_keys($this->parameters)[0];
            else $this->table = Settings::get('defeultTable');
        }

        $this->columns = $this->model->showColumns($this->table);

        if(!$this->columns) new RouteException('Не найдены поля в таблице - ' . $this->table, 2);

    }

}