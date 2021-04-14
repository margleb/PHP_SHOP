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

        return $this->cryptCombine($cipherText, $iv, $hmac);

        $crypt_data = $this->cryptUncombine($res, $ivlen);

    }

    public function decrypt($str) {


        // получаем длину вектора шифрования
        $ivlen = openssl_cipher_iv_length($this->cryptMethod);

        $crypt_data = $this->cryptUncombine($str, $ivlen);

        // дешифруем
        $original_plaintext = openssl_decrypt($crypt_data['str'], $this->cryptMethod, CRYPT_KEY, OPENSSL_RAW_DATA, $crypt_data['iv']);

        $calcmac = hash_hmac($this->hashAlgoritm, $crypt_data['str'], CRYPT_KEY, true);

        if(hash_equals($crypt_data['hmac'], $calcmac)) return $original_plaintext;

        return false;
    }

    protected function cryptCombine($str, $iv, $hmac) {

        $new_str = '';

        $str_len = strlen($str);

        // 1. Вычесляем позицию с которой будем вставляеть вектор шифрования
        $counter = (int)ceil(strlen(CRYPT_KEY) / ($str_len + $this->hashLength));

        $progress = 1;

        if($counter >= $str_len) $counter = 1;

        for($i = 0; $i < $str_len; $i++) {
            if($counter < $str_len) {
                if($counter === $i) {
                    // 3. Начиная с вычесленной позиции записываем букву из вектора шифрования
                    $new_str .= substr($iv, $progress - 1, 1);
                    $progress++;
                    // 4. Увеличиваем позицию через определенное количество букв
                    $counter += $progress;
                }
            } else {
                // 5. Если вычесленна позиция больше строки то останавливам цикл
                break;
            }
            // 2. Заносим в новую строку
            $new_str .= substr($str, $i, 1);
        }

        // 6. Добавляем в конец оставшуюся строку
        $new_str .= substr($str, $i);
        // 7. А также оставшиеся символы с вектора шифрования
        $new_str .= substr($iv, $progress - 1);

        // 7. Добавляем в середину хеш
        $new_str_half = (int)ceil(strlen($new_str) / 2);
        $new_str = substr($new_str, 0, $new_str_half) . $hmac . substr($new_str, $new_str_half);

        return base64_encode($new_str);

    }

    protected function cryptUncombine($str, $ivlen) {

        $crypt_data = [];
        $str = base64_decode($str);

        // 1. вычленяем хеш
        $hash_position = (int)ceil(strlen($str) / 2 - $this->hashLength / 2);
        $crypt_data['hmac'] = substr($str, $hash_position, $this->hashLength);
        $str = str_replace($crypt_data['hmac'], '', $str);

        // 2. Вычесляем позицию вектора шифрования
        $counter = (int)ceil(strlen(CRYPT_KEY) / (strlen($str) - $ivlen + $this->hashLength));

        $progerss = 2;

        $crypt_data['str'] = '';
        $crypt_data['iv'] = '';

        for($i = 0; $i < strlen($str); $i++) {

            // 3. Проверяем добавляли ли мы в конец строки вектор шифроания и оставшуюся строку
            if($ivlen + strlen($crypt_data['str']) < strlen($str)) {

                // 4. Раскидываем на вектор шифрования и символ
                if($i == $counter) {
                    $crypt_data['iv'] .= substr($str, $counter, 1);
                    $progerss++;
                    $counter += $progerss;
                } else {
                    $crypt_data['str'] .= substr($str, $i, 1);
                }

            } else {

                $crypt_data_len = strlen($crypt_data['str']);

                $crypt_data['str'] .= substr($str, $i, strlen($str) - $ivlen - $crypt_data_len);
                $crypt_data['iv'] .= substr($str, $i + (strlen($str) - $ivlen - $crypt_data_len));
                break;

            }
        }

        return $crypt_data;

    }



}