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
    protected $bad_links = [];

    protected $maxLinks = 5000;
    protected $parsingLogFile = 'parsing_log.txt';
    protected $fileArr = ['jpg', 'png', 'jpeg', 'gif', 'xls', 'xlsx', 'pdf', 'mp4', 'mpeg', 'mp3'];

    protected $filterArr = [
      'url' => ['order', 'page'],
      'get' => []
    ];

    public function inputData($link_counter = 1, $redirect = true) {


        $links_counter = $this->clearNum($link_counter);

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

        $table_rows = [];

        foreach($reserve as $name => $item) {

            $table_rows[$name] = '';

            if($item) $this->$name = json_decode($item);
            elseif ($name === 'all_links' || $name === 'temp_links') $this->$name = [SITE_URL];
        }

        // количество собираемых ссылок за парсинг
        $this->maxLinks = (int)$link_counter > 1 ? ceil($this->maxLinks / $link_counter) : $this->maxLinks;

//        if($link_counter == 1) $lk = 1;
//        else $lk = 0;

        while($this->temp_links) {
            $temp_links_count = count($this->temp_links);
            $links = $this->temp_links;
            $this->temp_links = [];

            if($temp_links_count > $this->maxLinks) {

                $links = array_chunk($links, ceil($temp_links_count / $this->maxLinks));
                $count_chunks = count($links);

                for($i = 0; $i < $count_chunks; $i++) {
                    $this->parsing($links[$i]);

                   // if($lk === 3 ) $this->hahah();

                    unset($links[$i]);
                    if($links) {

                        foreach($table_rows as $name => $item) {
                            if($name === 'temp_links') $table_rows[$name] = json_encode(array_merge(...$links));
                            else $table_rows[$name] = json_encode($this->$name);
                        }

                        $this->model->edit('parsing_data', [
                            'fields' => $table_rows
                        ]);
                    }

                    // if($lk === 2) $lk = 3;
                }
            } else {
                $this->parsing($links);
                // $lk && $lk = 2;
            }

            foreach($table_rows as $name => $item) {
                 $table_rows[$name] = json_encode($this->$name);
            }

            $this->model->edit('parsing_data', [
                'fields' => $table_rows
            ]);
        }

        foreach($table_rows as $name => $item) {
            $table_rows[$name] = '';
        }

        // очищаем таблицу при следующем вызове парсера
        $this->model->edit('parsing_data', [
            'fields' => $table_rows
        ]);


        if($this->all_links) {
            foreach($this->all_links as $key => $link) {
                if(!$this->filter($link) || in_array($link, $this->bad_links)) unset($this->all_links[$key]);
            }
        }

        // создам sitemap
        $this->createSitemap();

        if($redirect) {
            // выводим сообщеине об успешно сосзданном sitemap
            !$_SESSION['res']['answer'] && $_SESSION['res']['answer'] = '<div class="success">Sitemap is created</div>';
            $this->redirect();

        } else {
            $this->cancel(1,
                'Sitemap is created! ' . count($this->all_links) . ' links',
                '', true);
        }

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


        $result = [];
        // возращаем результат из multicurl
        foreach($urls as $i => $url) {
            $result[$i] = curl_multi_getcontent($curl[$i]);
            curl_multi_remove_handle($curlMultiply, $curl[$i]);
            curl_close($curl[$i]);

            // ищем только html страницы
            if(!preg_match('/Content-Type:\s+text\/html/uis', $result[$i])) {
                $this->bad_links[] = $url;
                $this->cancel(0, 'Incorrect content type ' . $url);
                continue;
            }

            // проверяем что код ответа от 200 и более
            if(!preg_match('/HTTP\/\d.?\d?\s+20\d/uis', $result[$i])) {
                $this->bad_links[] = $url;
                $this->cancel(0, 'Incorrect server code ' . $url);
                continue;
            }

            $this->createLinks($result[$i]);

        }

        // завершаем мультипотоковое соединение
        curl_multi_close($curlMultiply);


    }

    protected function createLinks($content) {
        if($content) {

            // \s*? пробель 0 или более раз
            // [^>]*? любые символы кроме знакак больше
            // \s*? пробел 0 или более раз
            // ["\'] двойная либо одинарная ковычка
            // (.+?) внутри ковычек любыве символы один или более раз
            // \1 ссылается на переменную (["'])
            // $str = "<a class=\"class\" id=\"1\" href='href-link' data-id='sdfsdf'>";
            preg_match_all('/<a\s*?[^>]*?href\s*?=(["\'])(.+?)\1[^>]*?>/ui', $content, $links);


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
                    if(!in_array($link, $this->bad_links) &&
                        !preg_match('/^('. $site_url .')?\/?#[^\/]*?$/ui', $link) &&
                        strpos($link, SITE_URL) == 0 &&
                        !in_array($link, $this->all_links)) {
                        $this->temp_links[] = $link;
                        $this->all_links[] = $link;
                    }
                }
                // exit;
            }
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
            $query = 'CREATE TABLE parsing_data (all_links longtext, temp_links longtext, bad_links longtext)';
            if(!$this->model->query($query, 'c') ||
                !$this->model->add('parsing_data', [
                    'fields' => [
                        'all_links' => '',
                        'temp_links' => '',
                        'bad_links' => ''
                    ]])
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

        $dom = new \domDocument('1.0', 'utf-8');
        $dom->formatOutput = true; // форматирования выходных данных

        // 1. Cоздаем корневой элемент urlset
        $root = $dom->createElement('urlset');
        $root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $root->setAttribute('xmlns:xls', 'http://w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        $dom->appendChild($root);

        // 2. Формируем dom
        $sxe = simplexml_import_dom($dom);

        // Y - полный год/ y - последние 2 цифры
        $date = new \DateTime();
        $lastMod = $date->format('Y-m-d') . 'T' . $date->format('H:i:s+01:00');

        if($this->all_links) {
            foreach($this->all_links as $item) {

                $elem = trim(mb_substr($item, mb_strlen(SITE_URL)), '/');
                $elem = explode('/', $elem);
                $count = '0.' . (count($elem) - 1);
                // 3. записываем уровень приоритета в xml в зависимости от глубины деритории
                $priority = 1 - (float)$count;
                // если приоритет у нас 0, то записываем 1.0
                if($priority == 1) $priority = '1.0';

                // 4. добавляем url в dom
                $urlMain = $sxe->addChild('url');
                // 5. добавляем тег loc в url
                $urlMain->addChild('loc', htmlspecialchars($item));
                // 6. добавляем дату в корректом формате
                $urlMain->addChild('lastmod', $lastMod);
                // 7. указываем как часто обновлять ссылку
                $urlMain->addChild('changefreq', 'weekly');
                // 9. указываем приоритет
                $urlMain->addChild('priority', $priority);

            }
        }

        // 10. сохраняем файл
        $dom->save($_SERVER['DOCUMENT_ROOT'] . PATH . 'sitemap.xml');

    }

}