<?php
namespace ScorpioT1000\OTR\Annotations\Policy\Interfaces;
interface CustomTo extends PolicyTo {
    public function format(\Closure $c);
}