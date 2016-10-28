<?php
namespace ScorpioT1000\OTR\Annotations\Policy\From;

use \ScorpioT1000\OTR\Annotations\Policy\Interfaces;
use \Doctrine\ORM\Mapping as ORM;

/** ITransformable policy.
 * Uses closure to manually parse the field into Entity.
 * @Annotation */
class Custom
    extends \ScorpioT1000\OTR\Annotations\Annotation
    implements Interfaces\CustomFrom
{    
    public $priority = 0.5;
    
    public $closure = null;
    
    /** Sets closure to prove if the field should be changed or skipped.
     * @param \Closure $c function($value,
     *                             $propertyName,
     *                             \ScorpioT1000\OTR\ITransformable $entity,
     *                             \Doctrine\ORM\EntityManagerInterface $em)
     *      The closure function should return TRUE if the field processed
     *      or FALSE to process it using Auto policy.
     * @return Custom */
    public function parse(\Closure $c) {
        if(! $c) {
            throw new \ScorpioT1000\OTR\Exceptions\PolicyException(
                'Closure is not specified for Custom policy');
        }
        $this->closure = $c;
        return $this;
    }

}