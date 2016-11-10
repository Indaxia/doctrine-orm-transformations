<?php
namespace Indaxia\OTR\Annotations\Policy\Interfaces;
interface CustomTo extends PolicyTo {
    public function format(\Closure $c);
}