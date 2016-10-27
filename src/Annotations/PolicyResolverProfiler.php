<?php
namespace ScorpioT1000\OTR\Annotations;

use \Doctrine\Common\Annotations\AnnotationReader;
use \ScorpioT1000\OTR\Annotations\Policy;
use \ScorpioT1000\OTR\Exceptions\PolicyException;

class PolicyResolverProfiler extends PolicyResolver {
    public $results = [];
    public $timeStart = 0.0;
    
    public function __construct() {
        $this->timeStart = microtime(true);
    }
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyFrom(Policy\Interfaces\Policy $policy = null,
                                              $propertyName,
                                              \ReflectionProperty $p,
                                              AnnotationReader $ar) {
        $result = parent::resolvePropertyPolicyFrom($policy, $propertyName, $p, $ar);
        $this->results[] = '[From] '.number_format(microtime(true) - $this->timeStart, 6)
            .': '.$p->getDeclaringClass()->getName().'.'.$propertyName
            .' -> '.($result ? get_class($result) : 'null');
        return $result;
    }
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyTo(Policy\Interfaces\Policy $policy = null,
                                            $propertyName,
                                            \ReflectionProperty $p,
                                            AnnotationReader $ar) {
        $result = parent::resolvePropertyPolicyTo($policy, $propertyName, $p, $ar);
        $this->results[] = '[To] '.number_format(microtime(true) - $this->timeStart, 6)
            .': '.$p->getDeclaringClass()->getName().'.'.$propertyName
            .' -> '.($result ? get_class($result) : 'null');
        return $result;
    }
}