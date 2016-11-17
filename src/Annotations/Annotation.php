<?php
namespace Indaxia\OTR\Annotations;

abstract class Annotation implements \Doctrine\ORM\Mapping\Annotation {
    const EPSILON = 0.000001;
    const PRIORITY_MULTIPLIER = 10.0;
    
    /** Sub-policies for fields. Use Annotation::inside() or Annotation::insideOf() to fill the value */
    public $nested = [];
    
    /** Policy priority. It's used in PolicyResolver when merging policies of one level. Higher = More important. */
    public $priority = 0.95;
    
    /** Read access method name for the field. 'get'+(field name in upper-case-first) is used by default.
     * (!) it's cleared by local policy, you have to specify it manually inside it.
     * (!) It's not considered when working with relations, 'get'+referencedColumnName is used instead. */
    public $getter = null;
    
    /** Write access method name for the field. 'set'+(field name in upper-case-first) is used by default.
     * (!) it's cleared by local policy, you have to specify it manually inside it. */
    public $setter = null;
    
    /** Allow policy propagation from parent entity to children field if no local policy specified. */
    public $propagating = true;
    
    
    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
    
    /** Include sub-policy list 
     * @param array
     * @return Annotation */
    public function inside(array $policy) {
        $this->nested = $policy;
        return $this;
    }
    
    public function insideOf(Annotation $a = null) {
        if($a) { $this->nested = $a->nested; }
        return $this;
    }
    
    public function clear() {
        $this->nested = [];
        $this->getter = null;
        $this->setter = null;
        return $this;
    }
    
    /** @return integer priority relative to the other policies in the namespace */
    public function getPriority() { return $this->priority; }
    public function isPriorityGreaterThanOrEqualTo(Annotation $a) {
        return ($this->priority > $a->priority) || (abs($this->priority - $a->priority) < static::EPSILON);
    }

    public function getLowerPriority($times = 1.0) {
        return $this->priority / (pow(self::PRIORITY_MULTIPLIER, $times));
    }
    
}