<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 02.04.2021
 * Time: 8:24
 */

namespace libraries;


class TextModify
{
    protected $translitArr = [ 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => 'y', 'ы' => 'y',
        'ь' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', ' ' => '-',
    ];

    // буквы которые мягкий знак должен смягчать
    protected $lowerLetter = ['а', 'е', 'и', 'о', 'у', 'э'];


    public function translit($str) {

        $str = mb_strtolower($str);
        $temp_arr = [];

        for($i = 0; $i < mb_strlen($str); $i++) {
            $temp_arr[] = mb_substr($str, $i, 1);
        }

        $link = '';

        if($temp_arr) {
           foreach($temp_arr as $key => $char) {
               if(array_key_exists($char, $this->translitArr)) {

                   switch ($char) {
                       case 'ъ': // если после твердого знака e, заменяем на y
                           if($temp_arr[$key+1] == 'е') $link .= 'y';
                       break;
                       case 'ы': // если посе ы идет й, заменяем на i
                           if($temp_arr[$key+1] == 'й') $link .= 'i';
                           else $link .= $this->translitArr[$char];
                       break;
                       case 'ь': // если мягкий знак не последний, и после него нет букв из lower latter то добавляем y
                           if($temp_arr[$key+1] !== count($temp_arr) && in_array($temp_arr[$key+1], $this->lowerLetter)) {
                                $link .= $this->translitArr[$char];
                           } // или ничего не добавлем
                       break;
                       default:
                           $link .= $this->translitArr[$char]; // по стандарту просто заменяем из массива translit arr
                       break;
                   }

               } else {
                  $link .= $char;
               }
           }

        }

        // i - регистронезависимый
        // u - работа с мультибайтовой кодировкой (unicode)
        // ^ - не символы если в кв.скорбке, иначе начало строки
        if($link) {
            // убираем все цифры и спец символы
            $link = preg_replace('/[^a-z0-9_-]/iu', '', $link);
            // если знаков дифис два и более, то заменить на один дефис
            $link = preg_replace('/-{2,}/iu', '-', $link);
            // если знаков нижнее подчеркивание два и более, то заменить на одно подчеркивание
            $link = preg_replace('/_{2,}/iu', '_', $link);
            // обрезаем колцевые дифисы
            $link = preg_replace('/(^[-_]+)|([-_]+$)/iu', '', $link);
        }
        return $link;
    }


}