<?php
namespace Indaxia\OTR\Annotations;

use \Doctrine\Common\Annotations\Reader;
use \Indaxia\OTR\Annotations\Policy;
use \Indaxia\OTR\Annotations\Annotation;
use \Indaxia\OTR\Exceptions\PolicyException;

/** Resolves policies passed by \Indaxia\OTR\Traits\Transformable */
class PolicyResolver {
    
    /** Allows to use serialize/unserialize in entity array field types.
     * @see https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2015-0231 */
    const IGNORE_CVE_2015_0231 = 0x01;
    /** Don't use parent's policy when nested policies are not specified */
    const NO_PROPAGATION = 0x02;
    /** Allow setting to null value for non-nullable scalar types */
    const ALLOW_NON_NULLABLE = 0x08;
    /** Replaces empty "simple_array" type with array(null) and vice versa
     * to fix doctrine empty simple_array issue #4673.
     * @see https://github.com/doctrine/doctrine2/issues/4673 */
    const SIMPLE_ARRAY_FIX = 0x10;
    
    protected $options;
    
    /** @param integer $options can be merged with | operator.
     * @see constants **/
    public function __construct($options = 0x00) {
        $this->options = $options;
    }
    
    /** Retrieves policy list of Entity's property for ITransformable::toArray() and returns the resolved one.
     * @param Policy\Interfaces\Policy $policy parent's policy
     * @param string $propertyName
     * @param \ReflectionProperty $p the property
     * @param Reader $ar
     * @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyTo(Policy\Interfaces\Policy $policy = null,
                                            $propertyName,
                                            \ReflectionProperty $p,
                                            Reader $ar) {
        $policies = [];
        
        // global
        $pa = $ar->getPropertyAnnotations($p);
        foreach($pa as $a) {
            if($a instanceof Policy\Interfaces\PolicyTo) {
                $policies[] = $this->cloneWithLowerPriority($a); 
            }
        }
        
        // propagating
        if($this->isPropagating($policy) && ($policy instanceof Policy\Interfaces\PolicyTo)) {
            $policies[] = $this->cloneWithLowerPriority($policy)->clear();
        } else { // not propagating
            $policies[] = $this->createAutoWithDoubleLoweredPriority();
        }
        
        // local
        if(isset($policy->nested[$propertyName])) {
            if($policy->nested[$propertyName] instanceof Policy\Interfaces\PolicyTo) {
                $policies[] = $policy->nested[$propertyName];
            } else {
                $policies[] = (new Policy\To\Auto())->insideOf($policy->nested[$propertyName]);
            }
        }
        
        return $this->merge($policies);
    }
    
    /** Retrieves policy list of Entity's property for ITransformable::fromArray() and returns the resolved one.
     * @param Policy\Interfaces\Policy $policy parent's policy
     * @param string $propertyName
     * @param \ReflectionProperty $p the property
     * @param Reader $ar
     * @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyFrom(Policy\Interfaces\Policy $policy = null,
                                              $propertyName,
                                              \ReflectionProperty $p,
                                              Reader $ar) {
        $policies = [];
        
        
        // global
        $pa = $ar->getPropertyAnnotations($p);
        foreach($pa as $a) {
            if($a instanceof Policy\Interfaces\PolicyFrom) {
                $policies[] = $this->cloneWithLowerPriority($a); 
            }
        }
        
        // skip id by default
        if($ar->getPropertyAnnotation($p, 'Doctrine\ORM\Mapping\Id')) {
            $policies[] = $this->createSkipWithLoweredPriority();
        }
        
        // propagating
        if($this->isPropagating($policy) && ($policy instanceof Policy\Interfaces\PolicyFrom)) {
            $policies[] = $this->cloneWithLowerPriority($policy)->clear();
        } else { // not propagating
            $policies[] = $this->createAutoWithDoubleLoweredPriority();
        }
        
        // local
        if(isset($policy->nested[$propertyName])) {
            if($policy->nested[$propertyName] instanceof Policy\Interfaces\PolicyFrom) {
                $policies[] = $policy->nested[$propertyName];
            } else {
                $policies[] = (new Policy\From\Auto())->insideOf($policy->nested[$propertyName]);
            }
        }
        
        return $this->merge($policies);
    }
    
    /** Merges property list into priority one and returns it
     * @param array $policies list of policies to merge
     * @return Policy\Interfaces\Policy */
    public function merge(array $policies) {
        $last = reset($policies);
        for($i = 1; $i < count($policies); ++$i) {
            if($policies[$i]->isPriorityGreaterThanOrEqualTo($last)) {
                $last = $policies[$i];
            }
        }
        return $last;
    }
    
    
    /** @return Policy\Auto */
    protected function createAutoWithDoubleLoweredPriority() {
        $new = new Policy\Auto();
        $new->priority = $new->getLowerPriority(2.0);
        return $new;
    }
    
    /** @return Policy\Skip */
    protected function createSkipWithLoweredPriority() {
        $new = new Policy\Skip();
        $new->priority = $new->getLowerPriority(1.0);
        return $new;
    }
    
    /** @return Policy\Interfaces\Policy */
    protected function cloneWithLowerPriority($policy, $times = 1.0) {
        $new = clone $policy;
        $new->priority = $policy->getLowerPriority($times);
        return $new;
    }
    
    /** @return boolean */
    protected function isPropagating($policy) {
         return $policy && $policy->propagating && !$this->hasOption(PolicyResolver::NO_PROPAGATION);
    }
    
    public function getOptions() { return $this->options; }
    public function setOptions($v) { $this->options = $v; }
    public function hasOption($o) { return $this->options & $o; }
    
    public function isNumberType($t) {
        switch($t) {
            case 'integer':
            case 'smallint':
            case 'bigint':
            case 'float':
            case 'decimal':
                return true;
        }
        return false;
    }
    
    public function increaseDepth() {}
    public function decreaseDepth() {}
}