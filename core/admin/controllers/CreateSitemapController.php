<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 04.04.2021
 * Time: 16:03
 */

namespace core\admin\controllers;


use core\base\controllers\BaseMethods;

class CreateSitemapController extends BaseAdmin
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

    protected function parsing($url, $index = 0) {
        // если в конце указан слеш, то прекращаем выполнение
        // для того чтобы в site map не появлялось 2 ссылки с конечным слешем и без него
        if(mb_strlen(SITE_URL) + 1 == mb_strlen($url) &&
            mb_strrpos($url, '/') == mb_strlen($url) -1) return;

        $curl = curl_init();
        // куда отправлять запросы (url)
        curl_setopt($curl, CURLOPT_URL, $url);
        // нужны ли ответы с сервера
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // возращать ли заголовки
        curl_setopt($curl, CURLOPT_HEADER, true);
        // следовать ли curl за редиректами
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        // ожидание ответа сервера
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        // ограничием обьем данных для загрузки curl
        curl_setopt($curl, CURLOPT_RANGE, 0 - 4194304);

        // инициализируем curl
        $out = curl_exec($curl);

        // останавливаем curl
        curl_close($curl);

        // echo('CURL SUCCESS');
        // exit($out);

        // разбираем заголовки
        // s - многострочный поиск

        // ищем только html страницы
        if(!preg_match("/Content-Type:\s+text\/html/uis", $out)) {


            // разрегестрируем ссылку по которой пришли
            unset($this->linkArr[$index]);

            // выставляем заново нумерацию ключей
            $this->linkArr = array_values($this->linkArr);

            return;
        }


        // проверяем что код ответа от 200 и более
        if(!preg_match('/HTTP\/\d.?\d?\s+20\d/uis', $out)) {
            $this->writeLog('Не корректная ссылка при парсине - ' . $url, $this->parsingLogFile);

            // разрегестрируем ссылку по которой пришли
            unset($this->linkArr[$index]);

            // выставляем заново нумерацию ключей
            $this->linkArr = array_values($this->linkArr);

            $_SESSION['res']['answer'] = '<div class="error">Incorrect link in parsing - '. $url . '<br>Sitemap is created</div>';

            return;
        } else {
            exit('yes');
        }



    }

    protected function filter($link) {

    }

    protected function createSitemap() {

    }

}