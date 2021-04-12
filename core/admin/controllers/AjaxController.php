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

        if(isset($this->data['ajax'])) {
            switch($this->data['ajax']) {
                case 'sitemap':
                    return (new CreateSitemapController())->inputData($this->data['links_counter'], false);
                break;
            }
        }

        return json_encode(['success' => '0', 'messages' => 'No ajax variable']);

    }
}