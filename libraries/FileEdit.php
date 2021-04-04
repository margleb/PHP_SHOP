<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 04.04.2021
 * Time: 13:10
 */

namespace libraries;


class FileEdit
{

    protected $imgArr = [];
    protected $directory;

    public function addFile($directory = false) {
        if(!$directory) $this->directory = $_SERVER['DOCUMENT_ROOT'].PATH.UPLOAD_DIR;
        else $this->directory = $directory;

        foreach($_FILES as $key => $file) {
            // если загружается несколько фотографий
            if(is_array($file['name'])) {

                $file_arr = [];

                for($i=0; $i < count($file['name']); $i++) {
                    // если не пустое название
                    if(!empty($file['name'][$i])) {
                        $file_arr['name'] = $file['name'][$i];
                        $file_arr['type'] = $file['type'][$i];
                        $file_arr['tmp_name'] = $file['tmp_name'][$i];
                        $file_arr['error'] = $file['error'][$i];
                        $file_arr['size'] = $file['size'][$i];

                        $res_name = $this->createFile($file_arr);

                        if($res_name) $this->imgArr[$key][] = $res_name;

                    }
                }


            } else {

                if($file['name']) {
                    $res_name = $this->createFile($file);

                    if($res_name) $this->imgArr[$key] = $res_name;

                }


            }
        }

        return $this->getFiles();

    }

    protected function createFile($file) {

        $fileNameArr = explode('.', $file['name']);
        $ext = $fileNameArr[count($fileNameArr) - 1]; // расширение файла
        unset($fileNameArr[count($fileNameArr) - 1]);

        $fileName = implode('.', $fileNameArr);

        // транслитирируем его
        $textModify = (new TextModify())->translit($fileName);

        // проверяем есть ли файл
        $fileName = $this->checkFile($fileName, $ext);

        $fileFullName = $this->directory . $fileName;

        if($this->uploadFile($file['tmp_name'], $fileFullName)) {
            return $fileName;
        }

        return false;

    }

    protected function uploadFile($tmpName, $dest) {
        if(move_uploaded_file($tmpName, $dest)) return true;
        return false;
    }

    protected function checkFile($fileName, $ext, $fileLastName = '') {
        if(!file_exists($this->directory . $fileName . $fileLastName . '.' . $ext)) {
            return $fileName . $fileLastName . '.' . $ext;
        }

        return $this->checkFile($fileName, $ext, '_' . hash('crc32', time() . mt_rand(1, 1000)));

    }


    public function getFiles() {
        return $this->imgArr;
    }

}