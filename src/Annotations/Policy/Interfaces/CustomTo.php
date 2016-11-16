<?php
namespace Indaxia\OTR\Annotations\Policy\Interfaces;
interface CustomTo extends PolicyTo {
    public function format(\Closure $handler);
    public function transform(\Closure $handler);
}