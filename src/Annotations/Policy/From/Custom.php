<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Uses closure as a proof to determine if the field should be changed or skipped.
 * @ORM\Annotation */
class Custom
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\CustomFrom
{
    public $priority = 0.5;
    
    public $closure = null;
    
    /** Sets closure to prove if the field should be changed or skipped.
     * @param \Closure $c function($newValue, $oldValue, \Doctrine\ORM\EntityManagerInterface $em, $propertyName)
     *      The closure function should return TRUE
     *      to use default (Auto) transformation flow
     *      or FALSE to skip the value.
     * @return Custom */
    public function prove(\Closure $c) {
        if(! $c) {
            throw new \ScorpioT1000\OTR\Exceptions\PolicyException(
                'Closure is not specified for Custom policy');
        }
        $this->closure = $c;
        return $this;
    }

}