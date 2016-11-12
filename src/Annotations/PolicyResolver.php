<?php
namespace Indaxia\OTR\Annotations;

use \Doctrine\Common\Annotations\Reader;
use \Indaxia\OTR\Annotations\Policy;
use \Indaxia\OTR\Exceptions\PolicyException;

class PolicyResolver {
    /** Allows to use serialize/unserialize in entity array field types.
     * @see https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2015-0231 */
    const IGNORE_CVE_2015_0231 = 0x01;
    /** Don't use parent policy when nested policies not specified */
    const NO_POLICY_NESTING = 0x02;
    /** Don't use any of global policies (from Entity annotations) */
    const NO_GLOBAL_POLICIES = 0x04;
    /** Allow setting to null value for non-nullable scalar types */
    const ALLOW_NON_NULLABLE = 0x08;
    /** Replaces empty "simple_array" type with array(null) and vice versa
     * to fix doctrine empty simple_array issue #4673.
     * @see https://github.com/doctrine/doctrine2/issues/4673 */
    const SIMPLE_ARRAY_FIX = 0x10;
    
    public $resolved;
    protected $options;
    
    /** @param integer $options can be merged with | operator.
     * @see constants **/
    public function __construct($options = 0x00) {
        $this->options = $options;
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
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyFrom(Policy\Interfaces\Policy $policy = null,
                                              $propertyName,
                                              \ReflectionProperty $p,
                                              Reader $ar) {
        if(! $policy) {
            $policy = new Policy\From\Auto();
            $policy->priority = \Indaxia\OTR\Annotations\Annotation::lowerPriority($policy->priority, 2.0);
        }
        
        // global
        if(! $this->hasOption(PolicyResolver::NO_GLOBAL_POLICIES)) {
            $pa = $ar->getPropertyAnnotations($p);
            foreach($pa as $a) {
                if($a instanceof Policy\Interfaces\PolicyFrom) {
                    // add global policies with double lowered priority
                    $policies[] = $a->createWithLowerPriority(2.0); 
                }
            }
        }
        
        // local
        $policies = [];
        if(isset($policy->nested[$propertyName])) { // has property policy
            if(($policy instanceof Policy\Interfaces\PolicyFrom)
                  && !($policy instanceof Policy\Interfaces\DenyFrom)
                  && !$this->hasOption(PolicyResolver::NO_POLICY_NESTING)) {
                // add parent policy with lowered priority and nothing inside
                $policies[] = $policy->createWithLowerPriority()->inside([]);
            }
            if($policy->nested[$propertyName] instanceof Policy\Interfaces\PolicyFrom) {
                // add current policy
                $policies[] = $policy->nested[$propertyName];
            }
        } else if(($policy instanceof Policy\Interfaces\PolicyFrom)
                  && !($policy instanceof Policy\Interfaces\DenyFrom)
                  && !$this->hasOption(PolicyResolver::NO_POLICY_NESTING)) {
            // inherit parent policy with nothing inside
            $newp = clone $policy;
            $policies[] = $newp->inside([]);
        }
        return $this->mergeFrom($policies);
    }
    
    /** @return Policy\Interfaces\Policy|null */
    public function resolvePropertyPolicyTo(Policy\Interfaces\Policy $policy = null,
                                            $propertyName,
                                            \ReflectionProperty $p,
                                            Reader $ar) {
        if(! $policy) {
            $policy = new Policy\To\Auto();
            $policy->priority = \Indaxia\OTR\Annotations\Annotation::lowerPriority($policy->priority, 2.0);
        }
        
        // global
        if(! $this->hasOption(PolicyResolver::NO_GLOBAL_POLICIES)) {
            $pa = $ar->getPropertyAnnotations($p);
            foreach($pa as $a) {
                if($a instanceof Policy\Interfaces\PolicyTo) {
                    // add global policies with double lowered priority
                    $policies[] = $a->createWithLowerPriority(2.0); 
                }
            }
        }
        
        // local
        $policies = [];
        if(isset($policy->nested[$propertyName])) { // has property policy
            if(($policy instanceof Policy\Interfaces\PolicyTo)
               && !$this->hasOption(PolicyResolver::NO_POLICY_NESTING)) {
                // add parent policy with lowered priority and nothing inside
                $policies[] = $policy->createWithLowerPriority()->inside([]);
            }
            if($policy->nested[$propertyName] instanceof Policy\Interfaces\PolicyTo) {
                // add current policy
                $policies[] = $policy->nested[$propertyName];
            }
        } else if(($policy instanceof Policy\Interfaces\PolicyTo)
                  && !$this->hasOption(PolicyResolver::NO_POLICY_NESTING)) {
            // inherit parent policy with nothing inside
            $newp = clone $policy;
            $policies[] = $newp->inside([]);
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