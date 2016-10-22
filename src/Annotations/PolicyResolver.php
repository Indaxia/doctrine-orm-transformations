<?php
namespace ScorpioT1000\OTR\Annotations;

use \Doctrine\Common\Annotations\AnnotationReader;
use \ScorpioT1000\OTR\Annotations\Policy;
use \ScorpioT1000\OTR\Exceptions\PolicyException;

class PolicyResolver {
    public $resolved;
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyFrom(Policy\Interfaces\Policy $policy = null,
                                              $propertyName,
                                              \ReflectionProperty $p,
                                              AnnotationReader $ar) {
        if(! $policy) { $policy = new Policy\From\Auto(); }
        
        // local
        $policies = [];
        if(isset($policy->nested[$propertyName])) { // has property policy
            if($policy instanceof Policy\Interfaces\PolicyFrom) {
                // add parent policy with lowered priority and nothing inside
                $policies[] = $policy->createWithLowerPriority()->inside([]);
            }
            if($policy->nested[$propertyName] instanceof Policy\Interfaces\PolicyFrom) {
                // add current policy
                $policies[] = $policy->nested[$propertyName];
            }
        } else if($policy instanceof Policy\Interfaces\PolicyFrom) {
            // inherit parent policy with nothing inside
            $p = clone $policy;
            $policies[] = $p->inside([]);
        }
        
        // global
        $pa = $ar->getPropertyAnnotation($p);
        foreach($pa as $a) {
            if($a instanceof Policy\Interfaces\PolicyFrom) {
                // add global policies with double lowered priority
                $policies[] = $a->createWithLowerPriority(2.0); 
            }
        }        
        return $this->mergeFrom($policies);
    }
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyTo(Policy\Interfaces\Policy $policy = null,
                                            $propertyName,
                                            \ReflectionProperty $p,
                                            AnnotationReader $ar) {
        if(! $policy) { $policy = new Policy\To\Auto(); }
        
        // local
        $policies = [];
        if(isset($policy->nested[$propertyName])) { // has property policy
            if($policy instanceof Policy\Interfaces\PolicyTo) {
                // add parent policy with lowered priority and nothing inside
                $policies[] = $policy->createWithLowerPriority()->inside([]);
            }
            if($policy->nested[$propertyName] instanceof Policy\Interfaces\PolicyTo) {
                // add current policy
                $policies[] = $policy->nested[$propertyName];
            }
        } else if($policy instanceof Policy\Interfaces\PolicyTo) {
            // inherit parent policy with nothing inside
            $p = clone $policy;
            $policies[] = $p->inside([]);
        }
        
        // global
        $pa = $ar->getPropertyAnnotation($p);
        foreach($pa as $a) {
            if($a instanceof Policy\Interfaces\PolicyTo) {
                // add global policies with double lowered priority
                $policies[] = $a->createWithLowerPriority(2.0); 
            }
        }        
        return $this->mergeTo($policies);
    }
    
    
    /** @return Policy\Interfaces\Policy */
    public function mergeFrom(array $policies) {
        $last = null;
        $deny = null; // [new, unset, update]
        foreach($policies as $p) { // select by priority
            if(!$last || $p->isPriorityGreaterThanOrEqualTo($last)) {
                $last = $p->insideOf($last);
                if($last instanceof Policy\Interfaces\DenyFrom) {
                    if(! $deny) { $deny = [false, false, false]; }
                    if($last instanceof Policy\Interfaces\DenyNewFrom) {
                        $deny[0] = true;
                    }
                    if($last instanceof Policy\Interfaces\DenyUnsetFrom) {
                        $deny[1] = true;
                    }
                    if($last instanceof Policy\Interfaces\DenyUpdateFrom) {
                        $deny[2] = true;
                    }
                } else if($last instanceof Policy\Interfaces\AutoFrom) {
                    $deny = [false, false, false];
                } else if($last instanceof Policy\Interfaces\SkipFrom) {
                    $deny = [true, true, true];
                }
            }
        }
        
        if($deny) { // merge DenyFrom instances
            if($deny[0]) { // new
                if($deny[1]) { // new unset
                    if($deny[2]) { // new unset update
                        $last = (new Policy\From\Skip())->insideOf($last);
                    } else { // new unset
                        $last = (new Policy\From\DenyNewUnset())->insideOf($last);
                    }
                } else if($deny[2] && !$deny[1]) { // new update
                    $last = (new Policy\From\DenyNewUpdate())->insideOf($last);
                } else { // new
                    $last = (new Policy\From\DenyNew())->insideOf($last);
                }
            } else if($deny[1]) { // unset
                if($deny[2]) { // unset update
                    $last = (new Policy\From\DenyUnsetUpdate())->insideOf($last);
                } else { // unset
                    $last = (new Policy\From\DenyUnset())->insideOf($last);
                }
            } else if($deny[2]) { // update
                $last = (new Policy\From\DenyUpdate())->insideOf($last);
            } else {
                $last = (new Policy\From\Auto())->insideOf($last);
            }
        }
        
        return $last ? $last : (new Policy\From\Auto());
    }
    
    
    /** @return Policy\Interfaces\Policy */
    public function mergeTo(array $policies) {
        $last = null;
        foreach($policies as $p) { // select by priority
            if(!$last || $p->isPriorityGreaterThanOrEqualTo($last)) {
                $last = $p->insideOf($last);
            }
        }
        return $last;
    }
}