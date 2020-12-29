<?php


abstract class Grandfather {

    // public $hair = 'Русые';

    public $body = 'Нормальное';

    private $borch = 'Вкусный';

    protected $nose = 'Кривой'; // можно получить ВНУТРИ дочерних классах, а не снаружи!
    private $mouth = 'Косой';

    protected function showMouth() {
        return $this->mouth;
    }

    public function getBorch() {
        return $this->borch;
    }
}

class Man extends Grandfather {

    protected $hair = 'Pусые';

    public function eat($calories) {
        if($calories > 500) {
            $this->body = 'Толстый';
        } else {
            $this->body = 'Худой';
        }
    }

    public function showGrandfatherNose() {
        echo $this->nose . '<br>';
        // echo $this->mouth; // а это приватный
        echo $this->showMouth();
    }

    public function showMouth() {
        $nose = parent::showMouth();
        $nose .= ' не очень';
        echo $nose;
    }

    public function getBorch() {
        $borch = parent::getBorch();
        $borch .= ' и соленый';
        return $borch;
    }

    public function reColor($color) {
        $this->hair = $color;
        echo $this->hair . '<br>';
    }

}

$masha = new Man();
$ivan = new Man();
$petya = new Man();

// доступ только внутри класса или в дочрних классах
// $masha->nose;
echo $masha->showGrandfatherNose() . '<br>';
$borch = $masha->getBorch();

echo $borch . ' и сладкий';

// echo 'Тело Maши - '. $masha->body . '<br>';
// echo 'Тело Ивана - '. $ivan->body . '<br>';


// $masha->hair = 'Белый';

$masha->reColor('Зеленый');

// $masha = new GrandFather(); // не можем создать обьект у абстрактного класса
$masha = new Father();

echo $masha->body;


/*
$masha->eat(200);
$ivan->eat(2000);

echo 'Тело Maши - '. $masha->body . '<br>';
echo 'Тело Ивана - '. $ivan->body . '<br>';
echo 'Тело Ивана - '. $petya->body . '<br>';
*/