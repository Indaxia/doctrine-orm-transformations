<?php
namespace ScorpioT1000\OTR\Annotations;

abstract class Annotation implements \Doctrine\ORM\Mapping\Annotation {
    public $nested = [];
    public $priority = 0.0001; // 0.0001 - 1.0
    const EPSILON = 0.00001;
    const PRIORITY_MULTIPLIER = 10.0;
    
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
    
    
    /** @return integer priority relative to the other policies in the namespace */
    public function getPriority() { return $this->priority; }
    public function isPriorityGreaterThanOrEqualTo(Annotation $a) {
        return ($this->priority > $a->priority) || (abs($this->priority - $a->priority) < static::EPSILON);
    }
    public function createWithLowerPriority($times = 1.0) {
        $new = clone $this;
        $new->priority /= (self::PRIORITY_MULTIPLIER * $times);
        return $new;
    }
    public function createWithIncreasedPriority($times = 1.0) {
        $new = clone $this;
        $new->priority *= (self::PRIORITY_MULTIPLIER * $times);
        return $new;
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
    
}