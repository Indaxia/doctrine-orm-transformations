<?php
namespace Indaxia\OTR\Annotations;

/** Annotation - general Policy skeleton class */
abstract class Annotation implements \Doctrine\ORM\Mapping\Annotation {
    
    /** Priority comparison precision */
    const EPSILON = 0.000001;
    
    /** @see static::getLowerPriority */
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
    
    /** Include sub-policy list from array
     * @param array
     * @return Annotation */
    public function inside(array $policy) {
        $this->nested = $policy;
        return $this;
    }
    
    /** Include sub-policy list from the existing policy
     * @param array
     * @return Annotation */
    public function insideOf(Annotation $a = null) {
        if($a) { $this->nested = $a->nested; }
        return $this;
    }
    
    /** Clear policy's sub-policies and getter/setter */
    public function clear() {
        $this->nested = [];
        $this->getter = null;
        $this->setter = null;
        return $this;
    }
    
    /** @return float priority relative to the other policies in the namespace */
    public function getPriority() { return $this->priority; }
    
    /** @param Annotation $a comparable
     * @return boolean if $this->priority is greater than or equal to $a->priority */
    public function isPriorityGreaterThanOrEqualTo(Annotation $a) {
        return ($this->priority > $a->priority) || (abs($this->priority - $a->priority) < static::EPSILON);
    }

    /** @param float $times divider as a degree of self::PRIORITY_MULTIPLIER
     * @return float priority lowered by $times */
    public function getLowerPriority($times = 1.0) {
        return $this->priority / (pow(static::PRIORITY_MULTIPLIER, $times));
    }
    
}