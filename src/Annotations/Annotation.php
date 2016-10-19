<?php
namespace ScorpioT1000\OTR\Annotaions;

class Annotation extends \Doctrine\Common\Annotations\Annotation {
    public $nested = [];
    
    /** Include sub-policy list 
     * @param array
     * @return Annotation */
    public function inside(array $policy) {
        $this->nested = $policy;
        return $this;
    }
}