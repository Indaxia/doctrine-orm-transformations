<?php
namespace Indaxia\OTR\Annotations;

abstract class Annotation implements \Doctrine\ORM\Mapping\Annotation {
    public $nested = [];
    public $priority = 0.0000001;
    const EPSILON = 0.00001;
    const PRIORITY_MULTIPLIER = 10.0;
    
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
    
    
    /** @return integer priority relative to the other policies in the namespace */
    public function getPriority() { return $this->priority; }
    public function isPriorityGreaterThanOrEqualTo(Annotation $a) {
        return ($this->priority > $a->priority) || (abs($this->priority - $a->priority) < static::EPSILON);
    }
    public function createWithLowerPriority($times = 1.0) {
        $new = clone $this;
        $new->priority = static::lowerPriority($this->priority, $times);
        return $new;
    }
    public function createWithIncreasedPriority($times = 1.0) {
        $new = clone $this;
        $new->priority = static::increasePriority($this->priority, $times);
        return $new;
    }
    public static function lowerPriority($p, $times = 1.0) {
        return $p / (pow(self::PRIORITY_MULTIPLIER, $times));
    }
    public static function increasePriority($p, $times = 1.0) {
        return $p * (pow(self::PRIORITY_MULTIPLIER, $times));
    }
    
}