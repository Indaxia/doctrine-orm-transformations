<?php
namespace Indaxia\OTR\Annotations;

abstract class Annotation implements \Doctrine\ORM\Mapping\Annotation {
    const EPSILON = 0.000001;
    const PRIORITY_MULTIPLIER = 10.0;
    
    public $nested = [];
    public $priority = 0.95;
    public $getter = null; // cannot be used with relations
    public $setter = null;
    public $propagating = true; // allow policy propagation from parent to children
    
    
    public function __construct(array $data = [])
    {
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