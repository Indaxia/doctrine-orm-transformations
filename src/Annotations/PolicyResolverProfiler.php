<?php
namespace Indaxia\OTR\Annotations;

use \Doctrine\Common\Annotations\Reader;
use \Indaxia\OTR\Annotations\Policy;
use \Indaxia\OTR\Exceptions\PolicyException;

class PolicyResolverProfiler extends PolicyResolver {
    public $results = [];
    public $timeStart = 0.0;
    
    public function __construct($options = 0x00) {
        $this->timeStart = microtime(true);
        parent::__construct($options);
    }
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyFrom(Policy\Interfaces\Policy $policy = null,
                                              $propertyName,
                                              \ReflectionProperty $p,
                                              Reader $ar) {
        $result = parent::resolvePropertyPolicyFrom($policy, $propertyName, $p, $ar);
        $this->results[] = '[From] '.number_format(microtime(true) - $this->timeStart, 6)
            .': '.$p->getDeclaringClass()->getName().'.'.$propertyName
            .' -> '.($result ? get_class($result).' (p'.$result->priority.')' : 'null');
        return $result;
    }
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyTo(Policy\Interfaces\Policy $policy = null,
                                            $propertyName,
                                            \ReflectionProperty $p,
                                            Reader $ar) {
        $result = parent::resolvePropertyPolicyTo($policy, $propertyName, $p, $ar);
        $this->results[] = '[To] '.number_format(microtime(true) - $this->timeStart, 6)
            .': '.$p->getDeclaringClass()->getName().'.'.$propertyName
            .' -> '.($result ? get_class($result).' (p'.$result->priority.')' : 'null');
        return $result;
    }
}