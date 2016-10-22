<?php
namespace ScorpioT1000\OTR\Annotations\Interfaces;
interface CustomFrom extends PolicyFrom {
    public function prove(Closure $c);
}