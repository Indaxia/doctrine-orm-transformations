<?php
namespace Indaxia\OTR\Annotations\Policy\Interfaces;
interface CustomFrom extends PolicyFrom {
    public function parse(\Closure $c);
}