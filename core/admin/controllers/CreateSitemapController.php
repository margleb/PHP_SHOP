<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 04.04.2021
 * Time: 16:03
 */

namespace core\admin\controllers;


use core\base\controllers\BaseMethods;

class CreateSitemapController
{
    use BaseMethods;

    protected $linkArr = [];
    protected $parsingLogFile = 'parsing_log.txt';
    protected $fileArr = ['jpg', 'png', 'jpeg', 'gif', 'xls', 'xlsx', 'pdf', 'mp4', 'mpeg', 'mp3'];

    protected $filterArr = [
      'url' => [],
      'get' => []
    ];

    protected function inputData() {

        // проверяем установлена ли библиотека curl для парсинга
        if(!function_exists('curl_init')) {
            $this->writeLog('Отсутсвуте библиотека CURL');
            $_SESSION['res']['anwser'] = '<div class="error">Library CURL as apsent. Creation of sitemap impossible</div>';
            $this->redirect();
        }

        // снимаем ограничение на выполнение скрипта (парсинг занимает много времени)
        set_time_limit(0);

        // чистим лог при парсинге
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . 'log/' . $this->parsingLogFile)) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . 'log/' . $this->parsingLogFile);
        }

        // запускаем парсинг
        $this->parsing(SITE_URL);

        // создам sitemap
        $this->createSitemap();

        // выводим сообщеине об успешно сосзданном sitemap
        !$_SESSION['res']['answer'] && $_SESSION['res']['answer'] = '<div class="success">Sitemap is created</div>';

        $this->redirect();

    }

    protected function parsing($usl, $index = 0) {

    }

    protected function filter($link) {

    }

    protected function createSitemap() {

    }

}