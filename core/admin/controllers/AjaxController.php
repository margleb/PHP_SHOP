<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 10.04.2021
 * Time: 15:00
 */

namespace core\admin\controllers;
use core\base\controllers\BaseAjax;


class AjaxController extends BaseAjax
{
    public function ajax() {
        return 'ADMIN AJAX';
    }
}