<?php
/**
 * Created by PhpStorm.
 * User: margleb
 * Date: 16.02.2021
 * Time: 14:35
 */

namespace core\admin\expansion;

use core\base\controllers\Singleton;

class TeachersExpansion
{
    use Singleton;

    public function expansion($args = []) {
        $this->title = 'LALA title';
    }
}