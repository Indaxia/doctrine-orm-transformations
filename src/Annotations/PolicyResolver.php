<?php
namespace ScorpioT1000\OTR\Annotations;

use \Doctrine\Common\Annotations\AnnotationReader;
use \ScorpioT1000\OTR\Annotations\Policy;
use \ScorpioT1000\OTR\Exceptions\PolicyException;

class PolicyResolver {
    public $resolved;
    
    /**
     * @param Policy\Interfaces\Policy|null $policy
     * @param \ReflectionProperty $p
     * @param AnnotationReader $ar
     * @return PI\Policy */
    public function resolve($policy, \ReflectionProperty $p, AnnotationReader $ar) {
        $result = new Policy\Auto();
        
        return $result;
    }
}