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

    protected $all_links = [];
    protected $temp_links = [];

    protected $maxLinks = 5000;
    protected $parsingLogFile = 'parsing_log.txt';
    protected $fileArr = ['jpg', 'png', 'jpeg', 'gif', 'xls', 'xlsx', 'pdf', 'mp4', 'mpeg', 'mp3'];

    protected $filterArr = [
      'url' => [],
      'get' => []
    ];

    protected function inputData($link_counter = 1) {

        // проверяем установлена ли библиотека curl для парсинга
        if(!function_exists('curl_init')) {
            $this->cancel(0,
                'Library CURL as apsent. Creation of sitemap impossible',
                '',
                true);
        }

        if(!$this->userId) $this->execBase();

        // создаем таблицу для парсинга таблиц (нужно в случае если будет валиться сервер
        if(!$this->checkParsingTable()) {
            $this->cancel(0,
                'You have problem with database table parsing_data',
                '',
                true);
        };

        // снимаем ограничение на выполнение скрипта (парсинг занимает много времени)
        set_time_limit(0);

        // чистим лог при парсинге
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . 'log/' . $this->parsingLogFile)) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . 'log/' . $this->parsingLogFile);
        }

        $reserve = $this->model->get('parsing_data')[0];

        foreach($reserve as $name => $item) {
            if($item) $this->$name = json_decode($item);
            else $this->$name = [SITE_URL];
        }

        // количество собираемых ссылок за парсинг
        $this->maxLinks = (int)$link_counter > 1 ? ceil($this->maxLinks / $link_counter) : $this->maxLinks;



        while($this->temp_links) {
            $temp_links_count = count($this->temp_links);
            $links = $this->temp_links;
            $this->temp_links = [];

            if($temp_links_count > $this->maxLinks) {

                $links = array_chunk($links, ceil($temp_links_count / $this->maxLinks));
                $count_chunks = count($links);

                for($i = 0; $i < $count_chunks; $i++) {
                    $this->parsing($links[$i]);
                    unset($links[$i]);
                    if($links) {
                        $this->model->edit('parsing_data', [
                            'fields' => [
                                'temp_links' => json_encode(array_merge(...$links)),
                                'all_links' => json_encode($this->all_links)
                            ]
                        ]);
                    }
                }
            } else {
                $this->parsing($links);
            }

            $this->model->edit('parsing_data', [
                'fields' => [
                    'temp_links' => json_encode($this->temp_links),
                    'all_links' => json_encode($this->all_links)
                ]
            ]);
        }

        // очищаем таблицу при следующем вызове парсера
        $this->model->edit('parsing_data', [
            'fields' => [
                'temp_links' => '',
                'all_links' => ''
            ]
        ]);

        // создам sitemap
        $this->createSitemap();

        // выводим сообщеине об успешно сосзданном sitemap
        !$_SESSION['res']['answer'] && $_SESSION['res']['answer'] = '<div class="success">Sitemap is created</div>';

        $this->redirect();

    }

    protected function parsing($urls) {

        if(!$urls) return; // перестраховка проблемы с памятью

        $curlMultiply = curl_multi_init(); // дескриптор многопоточности

        $curl = [];

        foreach($urls as $i => $url) {

            $curl[$i] = curl_init();
            // куда отправлять запросы (url)
            curl_setopt($curl[$i], CURLOPT_URL, $url);
            // нужны ли ответы с сервера
            curl_setopt($curl[$i], CURLOPT_RETURNTRANSFER, true);
            // возращать ли заголовки
            curl_setopt($curl[$i], CURLOPT_HEADER, true);
            // следовать ли curl за редиректами
            curl_setopt($curl[$i], CURLOPT_FOLLOWLOCATION, 1);
            // ожидание ответа сервера
            curl_setopt($curl[$i], CURLOPT_TIMEOUT, 120);
            // раскодируем страницу сжатую в gzip
            curl_setopt($curl[$i], CURLOPT_ENCODING, 'gzip,deflate');

            // добавляет обычный cURL-дескриптор к набору cURL-дескрипторов
            curl_multi_add_handle($curlMultiply, $curl[$i]);

        }

        do{

            $status = curl_multi_exec($curlMultiply, $active);
            $info = curl_multi_info_read($curlMultiply); // ошибка

            if(false !== $info) {
                if($info['result'] !== 0) {
                    $i = array_search($info['handle'], $curl);
                    // номер и текст ошибки
                    $err = curl_errno($curl[$i]);
                    $message = curl_error($curl[$i]);
                    $handler = curl_getinfo($curl[$i]); // массив с настройками curl

                    if($err != 0) {
                        $this->cancel(0,
                            'Error loading ' . $handler['url'] .
                            'http code: ' . $handler['http_code'] .
                            'error: ' . $err . ' message' . $message);
                    }

                }
            }

            if($status > 0) {
                // еще один вариант ошибки
                $this->cancel(0, curl_multi_strerror($status));
            }

        } while($status === CURLM_CALL_MULTI_PERFORM || $active); // соль

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
            unset($this->all_links[$index]);

            // выставляем заново нумерацию ключей
            $this->all_links = array_values($this->all_links);

            return;
        }

        // проверяем что код ответа от 200 и более
        if(!preg_match('/HTTP\/\d.?\d?\s+20\d/uis', $out)) {
            $this->writeLog('Не корректная ссылка при парсине - ' . $url, $this->parsingLogFile);

            // разрегестрируем ссылку по которой пришли
            unset($this->all_links[$index]);

            // выставляем заново нумерацию ключей
            $this->all_links = array_values($this->all_links);

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

                // $link = 'http:://yandex.ru/image.png';

                foreach($this->fileArr as $ext) {
                    if($ext) {
                        // экранируем символы слешей/точки если таковые есть
                        $ext = addslashes($ext);
                        $ext = str_replace('.', '\.', $ext);

                        // $ - конец строки
                        // \s*? пробель 0 или более раз

                        // Если эта ссылка на файл, то нам не нужно добавлять ее в sitemap
                        if(preg_match('/' . $ext . '(\s*?$|\?[^\/]*$)/ui', $link)) {
                            continue 2; // прерываем первую и вторую итерацию цикла
                        }
                    }
                }


                // относительная или абсолютная ссылка
                if(strpos($link, '/' == 0)) {
                    $link = SITE_URL . $link;
                }


                $site_url = mb_str_replace('.', '\.',
                    mb_str_replace('/', '\/', SITE_URL));

                // если эта ссылка не в массиве all_links
                // и link не равна заглушке
                // если вначале ссылки указан SIT_URL
                if(!in_array($link, $this->all_links) && !preg_match('/^('. $site_url .')?\/?#[^\/]*?$/ui', $link) && strpos($link, SITE_URL) === 0) {

                    // фильтруем ссылку на чпу и get параметры
                    if($this->filter($link)) {
                        $this->all_links[] = $link;
                        $this->parsing($link, count($this->all_links) - 1);
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

    protected function cancel($success = 0, $message = '', $log_message = '', $exit = false) {
        $exitArr  = [];
        $exitArr['success'] = $success;
        $exitArr['message'] = $message ? $message : 'ERROR PARSING';
        $log_message = $log_message ? $log_message : $exitArr['message'];

        $class = 'success';

        if(!$exitArr['success']) {
            $class = 'error';
            $this->writeLog($log_message, 'parsing_log.txt');
        }

        if($exit) {
            $exitArr['message'] =  '<div> class="'.$class.'">'.$exitArr['message'].'</div>';
            exit(json_encode($exitArr));
        }

    }

    protected function createSitemap() {

    }

}