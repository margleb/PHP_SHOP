<?php

namespace core\base\exceptions;
use core\base\controllers\BaseMethods;


class RouteException extends \Exception {

    protected $messages;
    use BaseMethods;

    public function __construct($message = "", $code = 0) {
        # вызоваем метод родительского класса
        # без вызова род. конструктора мы не будем видеть системные ошибки
        parent::__construct($message, $code);

        $this->messages = include 'messages.php';

        # ошибка либо код ошибки
        $error = $this->getMessage() ? $this->getMessage() : $this->messages[$this->getCode()];

        $error .= "\r\n" . 'file' . $this->getFile() . "\r\n" . 'In line ' . $this->getLine() . "\r\n";

        # сообщение для пользователя
        if($this->messages[$this->getCode()]) $this->message = $this->messages[$this->getCode()];

        # записываем лог ошибки
        $this->writeLog($error);

    }

}