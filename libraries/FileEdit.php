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

    }

    public function getFiles() {
        return $this->imgArr;
    }

}