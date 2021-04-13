<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 13.04.2021
 * Time: 7:01
 */

namespace core\base\models;
use core\base\controllers\Singleton;

class Crypt
{
    use Singleton;

    private $cryptMethod = 'AES-128-CBC';
    private $hashAlgoritm = 'sha256';
    private $hashLength = 32;

    public function encrypt($str) {

        // длина вектора шифрования
        $ivlen = openssl_cipher_iv_length($this->cryptMethod);
        // генерируем псевдослучайную последовательность
        $iv = openssl_random_pseudo_bytes($ivlen);
        // зашифровываем данные
        $cipherText = openssl_encrypt($str, $this->cryptMethod, CRYPT_KEY, OPENSSL_RAW_DATA, $iv);

        // хеш ключ
        $hmac = hash_hmac($this->hashAlgoritm, $cipherText, CRYPT_KEY, true);

        return base64_encode($iv.$hmac.$cipherText);
    }

    public function decrypt($str) {
        // декодируем
        $crypt_str = base64_decode($str);
        // получаем длину вектора шифрования
        $ivlen = openssl_cipher_iv_length($this->cryptMethod);
        // из шифрованной строки вытаскиваем первые 16 символов
        $iv = substr($crypt_str, 0, $ivlen);
        // получаем hash ключ
        $hmac = substr($crypt_str, $ivlen, $this->hashLength);
        // получаем конечную шифруемую строку
        $cipherText = substr($crypt_str, $ivlen + $this->hashLength);
        // дешифруем
        $original_plaintext = openssl_decrypt($cipherText, $this->cryptMethod, CRYPT_KEY, OPENSSL_RAW_DATA, $iv);

        $calcmac = hash_hmac($this->hashAlgoritm, $cipherText, CRYPT_KEY, true);

        if(hash_equals($hmac, $calcmac)) return $original_plaintext;

        return false;
    }

}