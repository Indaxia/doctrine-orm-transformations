<?php
namespace Indaxia\OTR\Annotations;

use \Doctrine\Common\Annotations\Reader;
use \Indaxia\OTR\Annotations\Policy;
use \Indaxia\OTR\Exceptions\PolicyException;

class PolicyResolverProfiler extends PolicyResolver {
    public $results = [];
    public $timeStart = 0.0;
    
    const PRIORITY_DETAILS = 0x10000;
    const NO_DEPTH_PADDING = 0x20000;
    
    public $currentDepth = 0; // for recursion purposes
    
    public function __construct($options = 0x00) {
        $this->timeStart = microtime(true);
        parent::__construct($options);
    }
    
    /** {@inheritDoc}
     * Adds resolution profiler details. */
    public function resolvePropertyPolicyFrom(Policy\Interfaces\Policy $policy = null,
                                              $propertyName,
                                              \ReflectionProperty $p,
                                              Reader $ar) {
        $this->results[] = $this->padding().'[From] ';
        $el = & $this->results[count($this->results)-1];
        $result = parent::resolvePropertyPolicyFrom($policy, $propertyName, $p, $ar);
        $el .= number_format(microtime(true) - $this->timeStart, 6)
            .': '.$p->getDeclaringShortClass()->getName().'.'.$propertyName
            .' -> '.($result ? (new \ReflectionClass($result))->getShortName()
            .' (p '.rtrim(number_format($result->priority, 16),'0').')' : 'null');
        return $result;
    }
    
    /** {@inheritDoc}
     * Adds resolution profiler details. */
    public function resolvePropertyPolicyTo(Policy\Interfaces\Policy $policy = null,
                                            $propertyName,
                                            \ReflectionProperty $p,
                                            Reader $ar) {
        $this->results[] = $this->padding().'[To] ';
        $el = & $this->results[count($this->results)-1];
        $result = parent::resolvePropertyPolicyTo($policy, $propertyName, $p, $ar);
        $el .= number_format(microtime(true) - $this->timeStart, 6)
            .': '.$p->getDeclaringClass()->getShortName().'.'.$propertyName
            .' -> '.($result ? (new \ReflectionClass($result))->getShortName()
            .' (p '.rtrim(number_format($result->priority, 16),'0').')' : 'null');
        return $result;
    }
    
    /** {@inheritDoc}
     * Adds profiler details if static::PROFILER_DETAILS option is passed to constructor. */
    public function merge(array $policies) {
        $result = parent::merge($policies);
        if($this->hasOption(static::PRIORITY_DETAILS)) {
            foreach($policies as $p) {
                $this->addResult($p);
            }
            $this->results[] = '';
        }
        return $result;
    }
    
    protected function addResult($policy) {
        $this->results[] = $this->padding().'    - '.(new \ReflectionClass($policy))->getShortName()
            .' (p '.rtrim(number_format($policy->priority, 16),'0').')'
            .($policy->nested ? ' {...}('.count($policy->nested).')' : '');
    }
    
    protected function padding() {
        return $this->hasOption(static::NO_DEPTH_PADDING) ? '' : str_repeat('    ', $this->currentDepth);
    }
    
    public function increaseDepth() { $this->currentDepth++; }
    public function decreaseDepth() { $this->currentDepth--; }
}