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
      'url' => ['order'],
      'get' => []
    ];

    protected function inputData() {

        // проверяем установлена ли библиотека curl для парсинга
        if(!function_exists('curl_init')) {
            $this->writeLog('Отсутсвуте библиотека CURL');
            $_SESSION['res']['anwser'] = '<div class="error">Library CURL as apsent. Creation of sitemap impossible</div>';
            $this->redirect();
        }

        if(!$this->userId) $this->execBase();

        // создаем таблицу для парсинга таблиц (нужно в случае если будет валиться сервер
        if(!$this->checkParsingTable()) return false;

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
        if(!preg_match('/Content-Type:\s+text\/html/uis', $out)) {


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
        }


        // \s*? пробель 0 или более раз
        // [^>]*? любые символы кроме знакак больше
        // \s*? пробел 0 или более раз
        // ["\'] двойная либо одинарная ковычка
        // (.+?) внутри ковычек любыве символы один или более раз
        // \1 ссылается на переменную (["'])
        // $str = "<a class=\"class\" id=\"1\" href='href-link' data-id='sdfsdf'>";
        preg_match_all('/<a\s*?[^>]*?href\s*?=(["\'])(.+?)\1[^>]*?>/ui', $out, $links);


        if($links[2]) {

            // $links[2] = [];
            // $links[2][0] = 'http://yandex.ru/image.jpg?ver1.1';

            foreach($links[2] as $link) {

                // если в конце указан слеш, то прекращаем выполнение
                // для того чтобы в site map не появлялось 2 ссылки с конечным слешем и без него
                if($link == '/' || $link == SITE_URL . '/') continue;

                foreach($this->fileArr as $ext) {
                    if($ext) {
                        // экранируем символы слешей/точки если таковые есть
                        $ext = addslashes($ext);
                        $ext = str_replace('.', '\.', $ext);

                        // $ - конец строки
                        // \s*? пробель 0 или более раз

                        // Если эта ссылка на файл, то нам не нужно добавлять ее в sitemap
                        if(preg_match('/' . $ext . '\s*?$|\?[^\/]/ui', $link)) {
                            continue 2; // прерываем первую и вторую итерацию цикла
                        }
                    }
                }


                // относительная или абсолютная ссылка
                if(strpos($link, '/' == 0)) {
                    $link = SITE_URL . $link;
                }

                // если эта ссылка не в массиве linkArr
                // и link не равна заглушке
                // если вначале ссылки указан SIT_URL
                if(!in_array($link, $this->linkArr) && $link !== '#' && strpos($link, SITE_URL) === 0) {

                    // фильтруем ссылку на чпу и get параметры
                    if($this->filter($link)) {
                        $this->linkArr[] = $link;
                        $this->parsing($link, count($this->linkArr) - 1);
                    }

                }
            }
            // exit;
        }
    }

    protected function filter($link) {

        // $link = 'http:://yandex.ru/ord/id?Masha=ASC&amp;sddsad=111';
        // $link = 'http:://yandex.ru/masha/asc?masha=desc';

        if($this->filterArr) {
            foreach($this->filterArr as $type => $values) {
                if($values) {
                    foreach($values as $item) {
                        $item = str_replace('/', '\/', addslashes($item));
                        if($type == 'url') {
                            if(preg_match('/^[^\?]*' . $item . '/ui', $link)) {
                                return false;
                            }
                        }

                        if($type === 'get') {
                           if(preg_match('/(\?|&amp;|=|&)'. $item . '(=|&amp;|&|$)/ui', $link, $matches)) {
                               return false;
                           }
                        }
                    }
                }
            }
        }

        return true;
    }

    protected function checkParsingTable() {
        $tables = $this->model->showTables();
        if(!in_array('parsing_data', $tables)) {
            $query = 'CREATE TABLE parsing_data (all_links text, temp_links text)';
            if(!$this->model->query($query, 'c') ||
                !$this->model->add('parsing_data', ['fields' => ['all_links' => '', 'temp_links' => '']])
            ){return false;}
        }
        return true;
    }

    protected function createSitemap() {

    }

}